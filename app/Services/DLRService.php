<?php

namespace App\Services;

use App\Entity\Dto\MsgDto;
use App\Entity\Dto\ResponseDto;
use Flytachi\Kernel\Src\Errors\ClientError;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\Service;
use Flytachi\Kernel\Src\Unit\Blink\Blink;

class DLRService extends Service
{
    public function sending(int $sourceAddress, string $message): void
    {
        try {
            if (SmppConfig::$prmDlrOn
                && SmppConfig::$prmDlrUrl != ''
                && SmppConfig::$prmDlrMethod != ''
            ) {
                $blink = match (SmppConfig::$prmDlrMethod) {
                    'GET' => Blink::headers(Blink::ACCEPT_JSON)
                        ->get(SmppConfig::$prmDlrUrl, [
                            'phoneNumber' => $sourceAddress,
                            'input' => $message,
                            'meta-data' => SmppConfig::$prmDlrMetaData
                        ])->send(false),
                    'POST' => Blink::headers(Blink::ACCEPT_JSON, Blink::CONTENT_JSON)
                        ->post(SmppConfig::$prmDlrUrl)
                        ->body([
                            'phoneNumber' => $sourceAddress,
                            'input' => $message,
                            'meta-data' => SmppConfig::$prmDlrMetaData
                        ])
                        ->send(false),
                    default => null
                };

                if (SmppConfig::$prmDlrResponsive && $blink != null) {
                    try {
                        if (!$blink->httpStatus()->isSuccess()) {
                            ClientError::throw(
                                "Dlr response error status code {$blink->status()}",
                                HttpCode::FAILED_DEPENDENCY
                            );
                        }
                        $response = new ResponseDto(...$blink->responseAsJson());
                        $msg = $response->message;
                        $enableInput = $response->enableInput;
                    } catch (\Throwable $e) {
                        $this->logger->warning("response: " .  $e->getMessage());
                        $msg = 'Unknown Error';
                        $enableInput = false;
                    } finally {
                        $this->submitQue(new MsgDto(
                            $sourceAddress,
                            $msg,
                            $enableInput
                        ));
                    }
                }
            }
        } catch (\Throwable $e){
            $this->logger->warning("sending: " .  $e->getMessage());
        }
    }

    public function submitQue(MsgDto $msg): void
    {
        try {
            Store::main()->rPush(SmppConfig::$prmSenderQln, json_encode($msg));
        } catch (\Throwable $e) {
            $this->logger->warning("submit que: " .  $e->getMessage());
        }
    }
}

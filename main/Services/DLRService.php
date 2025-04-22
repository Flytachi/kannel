<?php

namespace Main\Services;

use Flytachi\Kernel\Src\Errors\ClientError;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\Service;
use Flytachi\Kernel\Src\Unit\Blink\Blink;
use Main\Entity\Dto\ResponseDto;
use Main\Entity\Dto\UssdMsgDto;

class DLRService extends Service
{
    public function sending(int $sourceAddress, string $message): void
    {
        try {
            if (SmppConfig::$ussdPrmDlrOn
                && SmppConfig::$ussdPrmDlrUrl != ''
                && SmppConfig::$ussdPrmDlrMethod != ''
            ) {
                $blink = match (SmppConfig::$ussdPrmDlrMethod) {
                    'GET' => Blink::headers(Blink::ACCEPT_JSON)
                        ->get(SmppConfig::$ussdPrmDlrUrl, [
                            'phoneNumber' => $sourceAddress,
                            'input' => $message,
                            'meta-data' => SmppConfig::$ussdPrmDlrMetaData
                        ])->send(false),
                    'POST' => Blink::headers(Blink::ACCEPT_JSON, Blink::CONTENT_JSON)
                        ->post(SmppConfig::$ussdPrmDlrUrl)
                        ->bodyJson([
                            'phoneNumber' => $sourceAddress,
                            'input' => $message,
                            'meta-data' => SmppConfig::$ussdPrmDlrMetaData
                        ])
                        ->send(false),
                    default => null
                };

                if (SmppConfig::$ussdPrmDlrResponsive && $blink != null) {
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
                        $msg = 'Unknown error. Please try again later';
                        $enableInput = false;
                    } finally {
                        $this->submitQue(new UssdMsgDto(
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

    public function submitQue(UssdMsgDto $msg): void
    {
        try {
            SmppConfig::init();
            Store::main()->rPush(SmppConfig::$ussdPrmSenderQln, json_encode($msg));
        } catch (\Throwable $e) {
            $this->logger->warning("submit que: " .  $e->getMessage());
        }
    }
}

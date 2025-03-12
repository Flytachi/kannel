<?php

namespace App\Threads;

use App\Entity\Dto\SmsMsgDto;
use App\Services\SmppConfig;
use App\Services\Store;
use Flytachi\Kernel\Src\Stereotype\Cluster;
use PhpSmpp\Client;
use PhpSmpp\Helper;
use PhpSmpp\Pdu\Part\Address;
use PhpSmpp\Service\Sender;
use PhpSmpp\SMPP;
use PhpSmpp\Transport\Exception\SocketTransportException;

class SmsSender extends Cluster
{
    public ?Sender $service = null;

    public function run(mixed $data = null): void
    {
        SmppConfig::init();
        $this->logger->info('LISTEN ' . SmppConfig::$host . ' ' . SmppConfig::$port);
        $this->prepare(SmppConfig::$smsPrmSenderBalancer);

        $this->service = new Sender(
            [SmppConfig::$host . ':' . SmppConfig::$port],
            SmppConfig::$username,
            SmppConfig::$password,
            Client::BIND_MODE_TRANSCEIVER
        );

        $this->serviceRun();
    }

    private function serviceRun(): void
    {
        try {
            $this->logger->info("(enshure) connection");
            $this->service->enshureConnection();
            $this->logger->info("(enshure) connection success");
            $this->streaming(function () {
                if ($taskData = Store::main()->lPop(SmppConfig::$smsPrmSenderQln)) {
                    try {
                        $msg = new SmsMsgDto(...json_decode($taskData, true));

                        try {
                            $smsId = $this->sendSms($msg->phoneNumber, $msg->message, SmppConfig::$smsPrmSenderFrom);
                            $this->logger->info(
                                '[FROM:' . SmppConfig::$smsPrmSenderFrom . '] '
                                . '[ADDR_SRC:' . $msg->phoneNumber . '] '
                                . '[SMS_ID:' . $smsId .  '] SMS ' . $msg->message
                            );
                        } catch (\Throwable $e) {
                            $this->logger->info(
                                '[FROM:' . SmppConfig::$smsPrmSenderFrom . '] '
                                . '[ADDR_SRC:' . $msg->phoneNumber . '] '
                                . 'SMS ' . $msg->message . ' ('  . $e->getMessage() . ')'
                            );
                        }
                    } catch (\Throwable $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
            });
        } catch (SocketTransportException $ex) {
            $this->logger->error($ex->getMessage());
            $this->serviceRun();
        }
    }

    /**
     * @throws \Throwable
     */
    private function sendSms(int $phone, string $message, string $from): false|string|null
    {
        $this->service->enshureConnection();
        $from = new Address($from, SMPP::TON_UNKNOWN, SMPP::NPI_E164);
        $to = new Address($phone, SMPP::TON_INTERNATIONAL, SMPP::NPI_E164);

        $encodedMessage = $message;
        $dataCoding = SMPP::DATA_CODING_DEFAULT;
        if (Helper::hasUTFChars($message)) {
            $encodedMessage = iconv('UTF-8', 'UCS-2BE', $message);
            $dataCoding = SMPP::DATA_CODING_UCS2;
        }

        $lastError = null;
        $smsId = null;

        for ($i = 0; $i < $this->service->retriesCount; $i++) {
            try {
                $smsId = $this->service->client->sendSMS($from, $to, $encodedMessage, null, $dataCoding);
            } catch (\Throwable $e) {
                $this->service->unbind();
                $this->service->enshureConnection();
                $lastError = $e;
            }
            if ($smsId) {
                break;
            }
            sleep($this->service->delayBetweenAttempts);
        }

        if (empty($smsId)) {
            throw $lastError ?? new \Error("SMPP: no smsc answer");
        }

        return $smsId;
    }

    protected function asInterrupt(): void
    {
        $this->service?->unbind();
        parent::asInterrupt();
    }

    protected function asTermination(): void
    {
        $this->service?->unbind();
        parent::asTermination();
    }
}

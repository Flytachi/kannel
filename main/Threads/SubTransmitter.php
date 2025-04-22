<?php

namespace Main\Threads;

use Flytachi\Kernel\Src\Stereotype\Cluster;
use Main\Entity\Dto\SmsMsgDto;
use Main\Entity\Dto\UssdMsgDto;
use Main\Services\SmppConfig;
use Main\Services\Store;
use PhpSmpp\Client;
use PhpSmpp\Helper;
use PhpSmpp\Pdu\Part\Address;
use PhpSmpp\Pdu\Part\Tag;
use PhpSmpp\Service\Sender;
use PhpSmpp\SMPP;
use PhpSmpp\Transport\Exception\SocketTransportException;

class SubTransmitter extends Cluster
{
    public ?Sender $service = null;

    public function run(mixed $data = null): void
    {
        SmppConfig::init();
        $this->logger->info('LISTEN ' . SmppConfig::$host . ' ' . SmppConfig::$port);
        $this->prepare(SmppConfig::$balancerTransmitter);

        $this->service = new Sender(
            [SmppConfig::$host . ':' . SmppConfig::$port],
            SmppConfig::$username,
            SmppConfig::$password,
            Client::BIND_MODE_TRANSMITTER
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
                if ($taskData = Store::main()->lPop(SmppConfig::$ussdPrmSenderQln)) {
                    try {
                        $msg = new UssdMsgDto(...json_decode($taskData, true));

                        // tags
                        $tags = [];
                        if ($msg->input)
                            $tags[] = new Tag(Tag::USSD_SERVICE_OP, SMPP::USSD_SERVICE_OP_USSR_REQUEST, 1, 'c');
                        else $tags[] = new Tag(Tag::USSD_SERVICE_OP, SMPP::USSD_SERVICE_OP_PSSR_RESPONSE, 1, 'c');
                        if (mb_strlen($msg->message) >= 254)
                            $tags[] = new Tag(Tag::MESSAGE_PAYLOAD, $msg->message, 0,'c');
                        $tags[] = new Tag(Tag::ITS_REPLY_TYPE, 5, 1, 'c');

                        try {
                            $smsId = $this->service->sendUSSD($msg->phoneNumber, $msg->message, SmppConfig::$ussdPrmSenderFrom, $tags);
                            $this->logger->info(
                                '[FROM:' . SmppConfig::$ussdPrmSenderFrom . '] '
                                . '[ADDR_SRC:' . $msg->phoneNumber . '] '
                                . '[MSG_ID:' . $smsId .  '] MSG ' . $msg->message
                            );
                        } catch (\Throwable $e) {
                            $this->logger->info(
                                '[FROM:' . SmppConfig::$ussdPrmSenderFrom . '] '
                                . '[ADDR_SRC:' . $msg->phoneNumber . '] '
                                . 'MSG ' . $msg->message . ' ('  . $e->getMessage() . ')'
                            );
                        }
                    } catch (\Throwable $e) {
                        $this->logger->error($e->getMessage());
                    }
                }

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

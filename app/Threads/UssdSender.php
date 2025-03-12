<?php

namespace App\Threads;

use App\Entity\Dto\UssdMsgDto;
use App\Services\SmppConfig;
use App\Services\Store;
use Flytachi\Kernel\Src\Stereotype\Cluster;
use PhpSmpp\Client;
use PhpSmpp\Helper;
use PhpSmpp\Pdu\Part\Address;
use PhpSmpp\Pdu\Part\Tag;
use PhpSmpp\Service\Sender;
use PhpSmpp\SMPP;
use PhpSmpp\Transport\Exception\SocketTransportException;

class UssdSender extends Cluster
{
    public ?Sender $service = null;

    public function run(mixed $data = null): void
    {
        SmppConfig::init();
        $this->logger->info('LISTEN ' . SmppConfig::$host . ' ' . SmppConfig::$port);
        $this->prepare(SmppConfig::$ussdPrmSenderBalancer);

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
            });
        } catch (SocketTransportException $ex) {
            $this->logger->error($ex->getMessage());
            $this->serviceRun();
        }
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

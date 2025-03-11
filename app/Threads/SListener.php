<?php

namespace App\Threads;

use App\Services\DLRService;
use App\Services\SmppConfig;
use Flytachi\Kernel\Src\Errors\ServerError;
use Flytachi\Kernel\Src\Stereotype\Cluster;
use PhpSmpp\Client;
use PhpSmpp\Service\Listener;
use PhpSmpp\Transport\Exception\SocketTransportException;

class SListener extends Cluster
{
    private ?Listener $service = null;
    public function run(mixed $data = null): void
    {
        SmppConfig::init();
        $this->logger->info('LISTEN ' . SmppConfig::$host . ' ' . SmppConfig::$port);
        $this->prepare(SmppConfig::$prmListenerBalancer);

        $this->service = new Listener(
            [SmppConfig::$host . ':' . SmppConfig::$port],
            SmppConfig::$username,
            SmppConfig::$password,
            Client::BIND_MODE_TRANSCEIVER
        );
        $dlrService = new DLRService;

        $this->serviceRun($dlrService);
    }

    private function serviceRun(DLRService $dlrService): void
    {
        try {
            $this->logger->info("(enshure) connection");
            $this->service->enshureConnection();
            $this->logger->info("(enshure) connection success");
            $this->streaming(function () use ($dlrService) {
                $this->service->listenOnce(function (\PhpSmpp\Pdu\Pdu $pdu) use ($dlrService) {
                    try {
                        if ($pdu instanceof \PhpSmpp\Pdu\Ussd) {
                            $_hex = bin2hex($pdu->message);
                            $this->logger->info(
                                '[ID:' . $pdu->id . '] '
                                . '[SEQ:' . $pdu->sequence . '] '
                                . '[TYPE:' . $pdu->serviceType . '] '
                                . '[ADDR_DEST:' . $pdu->destination->value . '] '
                                . '[ADDR_SRC:' . $pdu->source->value . '] '
                                . '[D_CODE:' . $pdu->dataCoding . '] '
                                . '[MSG_ID:' . $pdu->msgId . '] '
                                . '[HEX:' . $_hex . '] '
                                . ($_hex == '04'
                                    ? 'called'
                                    : 'MSG:' . $pdu->message
                                )
                            );
                            if ($_hex != '04') {
                                $dlrService->sending((int) $pdu->source->value, $pdu->message);
                            }
                        }
                    } catch (\Throwable $e) {
                        $this->logger->error($e->getMessage());
                    }
                });
            });
        } catch (SocketTransportException $ex) {
            $this->logger->error($ex->getMessage());
            $this->serviceRun($dlrService);
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

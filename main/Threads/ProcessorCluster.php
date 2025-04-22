<?php

namespace Main\Threads;

use Flytachi\Kernel\Src\Stereotype\Cluster;
use Flytachi\Kernel\Src\Thread\Signal;
use Flytachi\Kernel\Src\Unit\TimeTool;
use Main\Services\SmppConfig;

class ProcessorCluster extends Cluster
{
    public ?int $receiverPid = null;
    public ?int $transmitterPid = null;

    public function run(mixed $data = null): void
    {
        SmppConfig::init();
        $this->logger->info('START');
        $this->prepare(1);

        $this->setCondition("waiting");
        $this->connective();
        $this->setCondition("active");

        if (SmppConfig::$ussdOn) {
            $this->receiverPid = SubReceiver::dispatch();
        }
        if (SmppConfig::$ussdOn || SmppConfig::$smsOn) {
            $this->transmitterPid = SubTransmitter::dispatch();
        }

        $this->streaming(function () {
            // Subprocess Receiver
            if (SmppConfig::$ussdOn) {
                if ($this->receiverPid == null) $this->receiverPid = SubReceiver::dispatch();
                else {
                    $ussdListenerPid = posix_getpgid($this->receiverPid);
                    if (!$ussdListenerPid) $this->receiverPid = null;
                }
            }
            // Subprocess Sender
            if (SmppConfig::$ussdOn || SmppConfig::$smsOn) {

                if ($this->transmitterPid == null) $this->transmitterPid = SubTransmitter::dispatch();
                else {
                    $ussdSenderPid = posix_getpgid($this->transmitterPid);
                    if (!$ussdSenderPid) $this->transmitterPid = null;
                }
            }
        });
    }

    private function connective(): void
    {
        if (!$this->connectionStatus()) {
            TimeTool::sleepSec(2);
            $this->connective();
        }
    }

    private function connectionStatus(): bool
    {
//        $socket = stream_socket_client("udp://" . SmppConfig::$host . ":" . SmppConfig::$port, $errno, $errstr, 5);
//
//        if ($socket) {
//            $read = [$socket];
//            $write = $except = null;
//
//            if (stream_select($read, $write, $except, 0)) {
//                $status = true;
//            } else {
//                $status = false;
//            }
//            fclose($socket);
//            $this->logger->error("Socket connection error [" . SmppConfig::$host . ":" . SmppConfig::$port . "]");
//            return $status;
//        } else {
//            $this->logger->error("Socket connection error [" . SmppConfig::$host . ":" . SmppConfig::$port . "]: $errstr ($errno)");
//            return false;
//        }
        return true;
    }

    protected function asInterrupt(): void
    {
        if ($this->receiverPid) Signal::interrupt($this->receiverPid);
        if ($this->transmitterPid) Signal::interrupt($this->transmitterPid);
        if ($this->receiverPid) $this->wait($this->receiverPid);
        if ($this->transmitterPid) $this->wait($this->transmitterPid);
        parent::asInterrupt();
    }

    protected function asTermination(): void
    {
        if ($this->receiverPid) Signal::termination($this->receiverPid);
        if ($this->transmitterPid) Signal::termination($this->transmitterPid);
        if ($this->receiverPid) $this->wait($this->receiverPid);
        if ($this->transmitterPid) $this->wait($this->transmitterPid);
        parent::asTermination();
    }
}

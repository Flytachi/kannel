<?php

namespace App\Threads;

use App\Services\SmppConfig;
use Flytachi\Kernel\Src\Stereotype\Cluster;
use Flytachi\Kernel\Src\Thread\Signal;
use Flytachi\Kernel\Src\Unit\TimeTool;

class ProcessorCluster extends Cluster
{
    public ?int $ussdListenerPid = null;
    public ?int $ussdSenderPid = null;
    public ?int $smsSenderPid = null;

    public function run(mixed $data = null): void
    {
        SmppConfig::init();
        $this->logger->info('START');
        $this->prepare(1);

        $this->setCondition("waiting");
        $this->connective();
        $this->setCondition("active");

        if (SmppConfig::$ussdOn) {
            $this->ussdListenerPid = UssdListener::dispatch();
            $this->ussdSenderPid = UssdSender::dispatch();
        }
        if (SmppConfig::$smsOn) {
            $this->smsSenderPid = SmsSender::dispatch();
        }

        $this->streaming(function () {
            // Subprocess Ussd
            if (SmppConfig::$ussdOn) {
                // Subprocess Listener (ussd)
                if ($this->ussdListenerPid == null) $this->ussdListenerPid = UssdListener::dispatch();
                else {
                    $ussdListenerPid = posix_getpgid($this->ussdListenerPid);
                    if (!$ussdListenerPid) $this->ussdListenerPid = null;
                }

                // Subprocess Sender (ussd)
                if ($this->ussdSenderPid == null) $this->ussdSenderPid = UssdSender::dispatch();
                else {
                    $ussdSenderPid = posix_getpgid($this->ussdSenderPid);
                    if (!$ussdSenderPid) $this->ussdSenderPid = null;
                }
            }

            // Subprocess Sms
            if (SmppConfig::$smsOn) {
                // Subprocess Sender (sms)
                if ($this->smsSenderPid == null) $this->smsSenderPid = SmsSender::dispatch();
                else {
                    $smsSenderPid = posix_getpgid($this->smsSenderPid);
                    if (!$smsSenderPid) $this->smsSenderPid = null;
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
        if ($this->ussdListenerPid) Signal::interrupt($this->ussdListenerPid);
        if ($this->ussdSenderPid) Signal::interrupt($this->ussdSenderPid);
        if ($this->smsSenderPid) Signal::interrupt($this->smsSenderPid);
        if ($this->ussdListenerPid) $this->wait($this->ussdListenerPid);
        if ($this->ussdSenderPid) $this->wait($this->ussdSenderPid);
        if ($this->smsSenderPid) $this->wait($this->smsSenderPid);
        parent::asInterrupt();
    }

    protected function asTermination(): void
    {
        if ($this->ussdListenerPid) Signal::termination($this->ussdListenerPid);
        if ($this->ussdSenderPid) Signal::termination($this->ussdSenderPid);
        if ($this->smsSenderPid) Signal::termination($this->smsSenderPid);
        if ($this->ussdListenerPid) $this->wait($this->ussdListenerPid);
        if ($this->ussdSenderPid) $this->wait($this->ussdSenderPid);
        if ($this->smsSenderPid) $this->wait($this->smsSenderPid);
        parent::asTermination();
    }
}

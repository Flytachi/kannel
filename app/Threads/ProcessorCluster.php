<?php

namespace App\Threads;

use App\Services\SmppConfig;
use Flytachi\Kernel\Src\Stereotype\Cluster;
use Flytachi\Kernel\Src\Thread\Signal;
use Flytachi\Kernel\Src\Unit\TimeTool;

class ProcessorCluster extends Cluster
{
    public ?int $listenerPid = null;
    public ?int $senderPid = null;

    public function run(mixed $data = null): void
    {
        SmppConfig::init();
        $this->logger->info('START');
        $this->prepare(1);

        $this->setCondition("waiting");
        $this->connective();
        $this->setCondition("active");

        $this->listenerPid = SListener::dispatch();
        $this->senderPid = SSender::dispatch();

        $this->streaming(function () {
            // Subprocess Listener
            if ($this->listenerPid == null) $this->listenerPid = SListener::dispatch();
            else {
                $listenerPid = posix_getpgid($this->listenerPid);
                if (!$listenerPid) $this->listenerPid = null;
            }

            // Subprocess Sender
            if ($this->senderPid == null) $this->senderPid = SSender::dispatch();
            else {
                $senderPid = posix_getpgid($this->senderPid);
                if (!$senderPid) $this->senderPid = null;
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
        $socket = stream_socket_client("tcp://" . SmppConfig::$host . ":" . SmppConfig::$port, $errno, $errstr, 5);

        if ($socket) {
            $read = [$socket];
            $write = $except = null;

            if (stream_select($read, $write, $except, 0)) {
                $status = true;
            } else {
                $status = false;
            }
            fclose($socket);
            $this->logger->error("Socket connection error [" . SmppConfig::$host . ":" . SmppConfig::$port . "]");
            return $status;
        } else {
            $this->logger->error("Socket connection error [" . SmppConfig::$host . ":" . SmppConfig::$port . "]: $errstr ($errno)");
            return false;
        }
    }

    protected function asInterrupt(): void
    {
        if ($this->listenerPid) Signal::interrupt($this->listenerPid);
        if ($this->senderPid) Signal::interrupt($this->senderPid);
        if ($this->listenerPid) $this->wait($this->listenerPid);
        if ($this->senderPid) $this->wait($this->senderPid);
        parent::asInterrupt();
    }

    protected function asTermination(): void
    {
        if ($this->listenerPid) Signal::termination($this->listenerPid);
        if ($this->senderPid) Signal::termination($this->senderPid);
        if ($this->listenerPid) $this->wait($this->listenerPid);
        if ($this->senderPid) $this->wait($this->senderPid);
        parent::asTermination();
    }
}

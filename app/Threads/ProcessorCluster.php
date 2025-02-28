<?php

namespace App\Threads;

use App\Services\SmppConfig;
use Flytachi\Kernel\Src\Stereotype\Cluster;
use Flytachi\Kernel\Src\Thread\Signal;

class ProcessorCluster extends Cluster
{
    public ?int $listenerPid = null;
    public ?int $senderPid = null;

    public function run(mixed $data = null): void
    {
        SmppConfig::init();
        $this->logger->info('START');
        $this->prepare(1);

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

    protected function asInterrupt(): void
    {
        if ($this->listenerPid) Signal::interrupt($this->listenerPid);
        if ($this->senderPid) Signal::interrupt($this->senderPid);
        $this->wait($this->listenerPid);
        $this->wait($this->senderPid);
        parent::asInterrupt();
    }

    protected function asTermination(): void
    {
        if ($this->listenerPid) Signal::termination($this->listenerPid);
        if ($this->senderPid) Signal::termination($this->senderPid);
        $this->wait($this->listenerPid);
        $this->wait($this->senderPid);
        parent::asTermination();
    }
}

<?php

namespace Main;

use Flytachi\Kernel\Console\Inc\CmdCustom;
use Flytachi\Kernel\Src\Thread\Signal;
use Main\Services\SmppConfig;
use Main\Threads\ProcessorCluster;

class ServiceCmd extends CmdCustom
{
    public function handle(): void
    {
        self::printTitle("Service", 32);
        if (
            count($this->args['arguments']) == 2
        ) {
            $this->resolution();
        } else {
            self::printMessage("Enter argument");
            self::print("Example: extra run script app.service <status|start|stop>");
        }
        self::printTitle("Service", 32);
    }

    private function resolution(): void
    {
        if (array_key_exists(1, $this->args['arguments'])) {
            switch ($this->args['arguments'][1]) {
                case 'status':
                    $this->statusArg();
                    break;
                case 'start':
                    $this->startArg();
                    break;
                case 'stop':
                    $this->stopArg();
                    break;
                default:
                    self::printMessage("Argument '{$this->args['arguments'][1]}' not found");
                    break;
            }
        }
    }

    private function statusArg(): void
    {
        $status = ProcessorCluster::status();
        if ($status != null) {
            self::print("PID: " . $status['pid'], 32);
            self::print("ClassName: " . $status['className'], 32);
            self::print("CONDITION: " . $status['condition'], 32);
            self::print("STARTED_AT: " . $status['startedAt'], 32);
        } else {
            self::printMessage("No active");
        }
    }

    private function startArg(): void
    {
        $status = ProcessorCluster::status();
        if ($status == null) {
            SmppConfig::init();
            $pid = ProcessorCluster::dispatch();
            self::printMessage("Service started [PID:{$pid}]", 32);
        } else {
            self::printMessage("Service is active [PID:{$status['pid']}]");
        }
    }

    private function stopArg(): void
    {
        $status = ProcessorCluster::status();
        if ($status != null) {
            Signal::interrupt($status['pid']);
            self::printMessage("Service stopped [PID:{$status['pid']}]", 32);
        } else {
            self::printMessage("Service is not active");
        }
    }
}

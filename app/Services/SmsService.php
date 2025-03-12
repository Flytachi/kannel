<?php

namespace App\Services;

use App\Entity\Dto\SmsMsgDto;
use Flytachi\Kernel\Src\Stereotype\Service;

class SmsService extends Service
{
    public function submitQue(SmsMsgDto $msg): void
    {
        try {
            SmppConfig::init();
            Store::main()->rPush(SmppConfig::$smsPrmSenderQln, json_encode($msg));
        } catch (\Throwable $e) {
            $this->logger->warning("submit que: " .  $e->getMessage());
        }
    }
}

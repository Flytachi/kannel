<?php

namespace Main\Services;

use Flytachi\Kernel\Src\Stereotype\Service;
use Main\Entity\Dto\SmsMsgDto;

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

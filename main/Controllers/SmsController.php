<?php

namespace Main\Controllers;

use Flytachi\Kernel\Src\Errors\ServerError;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PostMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\RestController;
use Main\Entity\Dto\SmsMsgDto;
use Main\Entity\Request\SmsRequest;
use Main\Services\SmsService;
use Main\Threads\SubTransmitter;

#[RequestMapping('sms')]
class SmsController extends RestController
{
    #[GetMapping]
    public function submitGet(): void
    {
        $request = SmsRequest::params();
        if (SubTransmitter::status() == null) {
            ServerError::throw("Sms service is not working", HttpCode::SERVICE_UNAVAILABLE);
        }
        (new SmsService)->submitQue(new SmsMsgDto(
            $request->phoneNumber,
            $request->message
        ));
    }

    #[PostMapping]
    public function submitPost(): void
    {
        $request = SmsRequest::json();
        if (SubTransmitter::status() == null) {
            ServerError::throw("Sms service is not working", HttpCode::SERVICE_UNAVAILABLE);
        }
        (new SmsService)->submitQue(new SmsMsgDto(
            $request->phoneNumber,
            $request->message
        ));
    }
}

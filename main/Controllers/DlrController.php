<?php

namespace Main\Controllers;

use Flytachi\Kernel\Src\Errors\ServerError;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PostMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\RestController;
use Main\Entity\Dto\UssdMsgDto;
use Main\Entity\Request\SubmitRequest;
use Main\Services\DLRService;
use Main\Threads\SubTransmitter;

#[RequestMapping('dlr')]
class DlrController extends RestController
{
    #[GetMapping]
    public function submitGet(): void
    {
        $request = SubmitRequest::params();
        if (SubTransmitter::status() == null) {
            ServerError::throw("Ussd (sender) service is not working", HttpCode::SERVICE_UNAVAILABLE);
        }
        (new DLRService)->submitQue(new UssdMsgDto(
            $request->phoneNumber,
            $request->message,
            $request->enableInput
        ));
    }

    #[PostMapping]
    public function submitPost(): void
    {
        $request = SubmitRequest::json();
        if (SubTransmitter::status() == null) {
            ServerError::throw("Ussd (sender) service is not working", HttpCode::SERVICE_UNAVAILABLE);
        }
        (new DLRService)->submitQue(new UssdMsgDto(
            $request->phoneNumber,
            $request->message,
            $request->enableInput
        ));
    }
}

<?php

namespace App\Controllers;

use App\Entity\Dto\UssdMsgDto;
use App\Entity\Request\SubmitRequest;
use App\Services\DLRService;
use App\Threads\UssdSender;
use Flytachi\Kernel\Src\Errors\ServerError;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PostMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\RestController;

#[RequestMapping('dlr')]
class DlrController extends RestController
{
    #[GetMapping]
    public function submitGet(): void
    {
        $request = SubmitRequest::params();
        if (UssdSender::status() == null) {
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
        if (UssdSender::status() == null) {
            ServerError::throw("Ussd (sender) service is not working", HttpCode::SERVICE_UNAVAILABLE);
        }
        (new DLRService)->submitQue(new UssdMsgDto(
            $request->phoneNumber,
            $request->message,
            $request->enableInput
        ));
    }
}

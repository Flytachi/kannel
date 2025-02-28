<?php

namespace App\Controllers;

use App\Entity\Dto\MsgDto;
use App\Entity\Request\SubmitRequest;
use App\Services\DLRService;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PostMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Stereotype\RestController;

#[RequestMapping('dlr')]
class DlrController extends RestController
{
    #[GetMapping]
    public function submitGet(): void
    {
        $request = SubmitRequest::params();
        (new DLRService)->submitQue(new MsgDto(
            $request->phoneNumber,
            $request->message,
            $request->enableInput
        ));
    }

    #[PostMapping]
    public function submitPost(): void
    {
        $request = SubmitRequest::json();
        (new DLRService)->submitQue(new MsgDto(
            $request->phoneNumber,
            $request->message,
            $request->enableInput
        ));
    }
}

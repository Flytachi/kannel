<?php

namespace Main\Controllers\WebInterface;

use Flytachi\Kernel\Src\Errors\ClientError;
use Flytachi\Kernel\Src\Factory\Entity\Request;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\DeleteMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PostMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\Response;
use Flytachi\Kernel\Src\Stereotype\RestController;

#[RequestMapping('auth')]
class AuthController extends RestController
{
    #[GetMapping('session/exist')]
    public function sessionExist(): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return new Response([
            'session' => $_SESSION['ACCESS'] ?? false
        ]);
    }

    #[PostMapping('session')]
    public function sessionOpen(): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $haveUser = env('WEB_ADMIN_USER', '');
        $havePass = env('WEB_ADMIN_PASS', '');
        $request = Request::json();

        if (
            $haveUser == '' || $havePass == ''
            || !($request->username == $haveUser && $request->password == $havePass)
        ) {
            ClientError::throw('Uncorrected user or password.', HttpCode::BAD_REQUEST);
        }

        $_SESSION['ACCESS'] = true;
        return new Response('ASK');
    }

    #[DeleteMapping('session')]
    public function sessionClose(): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        return new Response('ASK');
    }
}

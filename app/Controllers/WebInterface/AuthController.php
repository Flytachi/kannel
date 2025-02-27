<?php

namespace App\Controllers\WebInterface;

use Flytachi\Kernel\Src\Factory\Entity\RequestDefault;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\DeleteMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PostMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Http\Error;
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
        $request = RequestDefault::json();

        if (
            $haveUser == '' || $havePass == ''
            || !($request->username == $haveUser && $request->password == $havePass)
        ) {
            Error::throw(HttpCode::BAD_REQUEST, 'Uncorrected user or password.');
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

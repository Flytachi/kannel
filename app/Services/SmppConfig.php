<?php

namespace App\Services;

use Flytachi\Kernel\Src\Http\Error;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\Service;

final class SmppConfig
{
    public static string $host;
    public static int $port;
    public static string $username;
    public static string $password;

    public static int $prmListenerBalancer;
    public static int $prmSenderBalancer;
    public static string $prmSenderQln;
    public static string $prmSenderFrom;
    public static bool $prmDlrOn;
    public static string $prmDlrUrl;
    public static string $prmDlrMethod;
    public static string $prmDlrMetaData;
    public static string $prmDlrResponsive;

    final public static function init(): void
    {
        self::$host = (string) env('SMPP_HOST');
        if (!self::$host) Error::throw(HttpCode::FAILED_DEPENDENCY,
            "env param 'host' not found (SMPP_HOST)");

        self::$port = (int) env('SMPP_PORT');
        if (!self::$port) Error::throw(HttpCode::FAILED_DEPENDENCY,
            "env param 'port' not found (SMPP_PORT)");

        self::$username = (string) env('SMPP_LOGIN');
        if (!self::$username) Error::throw(HttpCode::FAILED_DEPENDENCY,
            "env param 'login' not found (SMPP_LOGIN)");

        self::$password = (string) env('SMPP_PASS', '');
        if (!self::$username) Error::throw(HttpCode::FAILED_DEPENDENCY,
            "env param 'password' not found (SMPP_PASS)");

        // -----
        self::$prmListenerBalancer = (int) env('SMPP_PARAMS_LISTENER_RPS', 10);
        self::$prmSenderBalancer = (int) env('SMPP_PARAMS_SENDER_RPS', 10);
        self::$prmSenderQln = (string) env('SMPP_PARAMS_SENDER_QLN', 'smpp-kannel');
        if(trim(self::$prmSenderQln) == '') self::$prmSenderQln = 'smpp-kannel';
        self::$prmSenderFrom = (string) env('SMPP_PARAMS_SENDER_FROM', '');
        self::$prmDlrOn = (bool) env('SMPP_PARAMS_DLR_ON', false);
        self::$prmDlrUrl = (string) env('SMPP_PARAMS_DLR_URL', '');
        self::$prmDlrMethod = (string) env('SMPP_PARAMS_DLR_METHOD', '');
        self::$prmDlrMetaData = (string) env('SMPP_PARAMS_DLR_META', '');
        self::$prmDlrResponsive = (bool) env('SMPP_PARAMS_DLR_RESPONSIVE', false);
    }
}

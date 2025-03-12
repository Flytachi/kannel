<?php

namespace App\Services;

use Flytachi\Kernel\Src\Errors\ClientError;
use Flytachi\Kernel\Src\Http\HttpCode;

final class SmppConfig
{
    private static bool $initialize = false;
    public static string $host;
    public static int $port;
    public static string $username;
    public static string $password;

    public static bool $ussdOn;
    public static int $ussdPrmListenerBalancer;
    public static int $ussdPrmSenderBalancer;
    public static string $ussdPrmSenderQln;
    public static string $ussdPrmSenderFrom;
    public static bool $ussdPrmDlrOn;
    public static string $ussdPrmDlrUrl;
    public static string $ussdPrmDlrMethod;
    public static string $ussdPrmDlrMetaData;
    public static string $ussdPrmDlrResponsive;

    public static bool $smsOn;
    public static int $smsPrmSenderBalancer;
    public static string $smsPrmSenderQln;
    public static string $smsPrmSenderFrom;

    final public static function init(): void
    {
        if (!self::$initialize) {
            self::$host = (string) env('SMPP_HOST');
            if (!self::$host) ClientError::throw(
                "env param 'host' not found (SMPP_HOST)",
                HttpCode::FAILED_DEPENDENCY,
            );

            self::$port = (int) env('SMPP_PORT');
            if (!self::$port) ClientError::throw(
                "env param 'port' not found (SMPP_PORT)",
                HttpCode::FAILED_DEPENDENCY
            );

            self::$username = (string) env('SMPP_LOGIN');
            if (!self::$username) ClientError::throw(
                "env param 'login' not found (SMPP_LOGIN)",
                HttpCode::FAILED_DEPENDENCY
            );

            self::$password = (string) env('SMPP_PASS', '');
            if (!self::$username) ClientError::throw(
                "env param 'password' not found (SMPP_PASS)",
                HttpCode::FAILED_DEPENDENCY
            );

            // ----- (ussd)
            self::$ussdOn = (bool) env('SMPP_USSD_ON', false);
            self::$ussdPrmListenerBalancer = (int) env('SMPP_USSD_PARAMS_LISTENER_RPS', 100);
            self::$ussdPrmSenderBalancer = (int) env('SMPP_USSD_PARAMS_SENDER_RPS', 100);
            self::$ussdPrmSenderQln = (string) env('SMPP_USSD_PARAMS_SENDER_QLN', 'smpp-kannel-ussd');
            if(trim(self::$ussdPrmSenderQln) == '') self::$ussdPrmSenderQln = 'smpp-kannel-ussd';
            self::$ussdPrmSenderFrom = (string) env('SMPP_USSD_PARAMS_SENDER_FROM', '');
            self::$ussdPrmDlrOn = (bool) env('SMPP_USSD_PARAMS_DLR_ON', false);
            self::$ussdPrmDlrUrl = (string) env('SMPP_USSD_PARAMS_DLR_URL', '');
            self::$ussdPrmDlrMethod = (string) env('SMPP_USSD_PARAMS_DLR_METHOD', '');
            self::$ussdPrmDlrMetaData = (string) env('SMPP_USSD_PARAMS_DLR_META', '');
            self::$ussdPrmDlrResponsive = (bool) env('SMPP_USSD_PARAMS_DLR_RESPONSIVE', false);


            // ---- (sms)
            self::$smsOn = (bool) env('SMPP_SMS_ON', false);
            self::$smsPrmSenderBalancer = (int) env('SMPP_SMS_PARAMS_SENDER_RPS', 100);
            self::$smsPrmSenderQln = (string) env('SMPP_SMS_PARAMS_SENDER_QLN', 'smpp-kannel-sms');
            if(trim(self::$smsPrmSenderQln) == '') self::$smsPrmSenderQln = 'smpp-kannel-sms';
            self::$smsPrmSenderFrom = (string) env('SMPP_SMS_PARAMS_SENDER_FROM', '');
            self::$initialize = true;
        }
    }
}

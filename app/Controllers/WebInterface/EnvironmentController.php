<?php

namespace App\Controllers\WebInterface;

use App\Threads\ProcessorCluster;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\DeleteMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PutMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Stereotype\Response;
use Flytachi\Kernel\Src\Stereotype\RestController;

#[RequestMapping('api/environment')]
#[SessionMiddleware]
class EnvironmentController extends RestController
{
    #[GetMapping]
    public function envList(): Response
    {
        return new Response([
            [ 'key' => 'TIME_ZONE', 'value' => env('TIME_ZONE', '') ],
            [ 'key' => 'DEBUG', 'value' => env('DEBUG', false) ],

            [ 'key' => 'LOGGER_LEVEL_ALLOW', 'value' => env('LOGGER_LEVEL_ALLOW', '') ],
            [ 'key' => 'LOGGER_MAX_FILES', 'value' => env('LOGGER_MAX_FILES', 0) ],
            [ 'key' => 'LOGGER_FILE_DATE_FORMAT', 'value' => env('LOGGER_FILE_DATE_FORMAT', '') ],
            [ 'key' => 'LOGGER_LINE_DATE_FORMAT', 'value' => env('LOGGER_LINE_DATE_FORMAT', '') ],

            [ 'key' => 'REDIS_DBNAME', 'value' => env('REDIS_DBNAME', '') ],
            [ 'key' => 'REDIS_HOST', 'value' => env('REDIS_HOST', '') ],
            [ 'key' => 'REDIS_PASS', 'value' => env('REDIS_PASS', '') ],
            [ 'key' => 'REDIS_PORT', 'value' => env('REDIS_PORT', 6379) ],

            [ 'key' => 'SMPP_HOST', 'value' => env('SMPP_HOST', '') ],
            [ 'key' => 'SMPP_PORT', 'value' => env('SMPP_PORT', '') ],
            [ 'key' => 'SMPP_LOGIN', 'value' => env('SMPP_LOGIN', '') ],
            [ 'key' => 'SMPP_PASS', 'value' => env('SMPP_PASS', '') ],

            [ 'key' => 'SMPP_PARAMS_LISTENER_RPS', 'value' => env('SMPP_PARAMS_LISTENER_RPS', 100) ],
            [ 'key' => 'SMPP_PARAMS_SENDER_RPS', 'value' => env('SMPP_PARAMS_SENDER_RPS', 100) ],
            [ 'key' => 'SMPP_PARAMS_SENDER_QLN', 'value' => env('SMPP_PARAMS_SENDER_QLN', 'smpp-kannel') ],
            [ 'key' => 'SMPP_PARAMS_SENDER_FROM', 'value' => env('SMPP_PARAMS_SENDER_FROM', '') ],
            [ 'key' => 'SMPP_PARAMS_DLR_ON', 'value' => env('SMPP_PARAMS_DLR_ON', false) ],
            [ 'key' => 'SMPP_PARAMS_DLR_URL', 'value' => env('SMPP_PARAMS_DLR_URL', '') ],
            [ 'key' => 'SMPP_PARAMS_DLR_METHOD', 'value' => env('SMPP_PARAMS_DLR_METHOD', '') ],
            [ 'key' => 'SMPP_PARAMS_DLR_META', 'value' => env('SMPP_PARAMS_DLR_META', '') ],
            [ 'key' => 'SMPP_PARAMS_DLR_RESPONSIVE', 'value' => env('SMPP_PARAMS_DLR_RESPONSIVE', false) ],

            [ 'key' => 'WEB_ADMIN_USER', 'value' => env('WEB_ADMIN_USER', '') ],
            [ 'key' => 'WEB_ADMIN_PASS', 'value' => env('WEB_ADMIN_PASS', '') ]
        ]);
    }
}

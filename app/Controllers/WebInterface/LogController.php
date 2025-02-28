<?php

namespace App\Controllers\WebInterface;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Errors\ClientError;
use Flytachi\Kernel\Src\Factory\Entity\RequestDefault;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\Response;
use Flytachi\Kernel\Src\Stereotype\RestController;

#[RequestMapping('api/logs')]
#[SessionMiddleware]
class LogController extends RestController
{
    #[GetMapping('files')]
    public function files(): Response
    {
        $files = glob(Extra::$pathStorageLog . '/*.log');
        foreach ($files as $key => $file) {
            $files[$key] = basename($file, '.log');
        }
        $files = array_reverse($files);
        return new Response($files);
    }

    #[GetMapping]
    public function list(): Response
    {
        $request = RequestDefault::params(false);
        $limit = $request->limit ?? 1000;
        if (!is_numeric($limit)) {
            ClientError::throw('limit must be numeric', HttpCode::BAD_REQUEST);
        }
        $limit = (int) $limit;
        $logFile = Extra::$pathStorageLog . '/' . ($request->filename ?? '') . '.log';

        $logs = [];
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lastLines = array_slice($lines, -$limit);
            foreach ($lastLines as $line) {
                preg_match('/\b(DEBUG|INFO|WARNING|ERROR|CRITICAL|ALERT)\b/', $line, $matches);
                $logLevel = isset($matches[1]) ? strtolower($matches[1]) : "default";

                $logs[] = [
                    'level' => $logLevel,
                    'message' => $line
                ];
            }
        }

        return new Response($logs);
    }
}

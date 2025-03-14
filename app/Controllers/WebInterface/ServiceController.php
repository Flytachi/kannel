<?php

namespace App\Controllers\WebInterface;

use App\Threads\ProcessorCluster;
use App\Threads\SubReceiver;
use App\Threads\SubTransmitter;
use Flytachi\Kernel\Src\Errors\ClientError;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\DeleteMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PutMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Stereotype\Response;
use Flytachi\Kernel\Src\Stereotype\RestController;

#[RequestMapping('api/service')]
#[SessionMiddleware]
class ServiceController extends RestController
{
    #[GetMapping]
    public function status(): Response
    {
        $status = ProcessorCluster::status();
        return new Response([
            'pid' => $status['pid'] ?? null,
            'className' => $status['className'] ?? ProcessorCluster::class,
            'condition' => $status['condition'] ?? 'passive',
            'startedAt' => $status['startedAt'] ?? null
        ]);
    }

    #[PutMapping]
    public function start(): void
    {
        ProcessorCluster::dispatch();
    }

    #[DeleteMapping]
    public function stop(): void
    {
        ProcessorCluster::stop();
    }

    #[GetMapping('subs')]
    public function statusSubs(): Response
    {
        $status = [];
        //
        $stUssdListener = SubReceiver::status();
        $status['SubReceiver'] = [
            'pid' => $stUssdListener['pid'] ?? null,
            'className' => $stUssdListener['className'] ?? SubReceiver::class,
            'condition' => $stUssdListener['condition'] ?? 'passive',
            'startedAt' => $stUssdListener['startedAt'] ?? null
        ];

        $stUssdSender = SubTransmitter::status();
        $status['SubTransmitter'] = [
            'pid' => $stUssdSender['pid'] ?? null,
            'className' => $stUssdSender['className'] ?? SubTransmitter::class,
            'condition' => $stUssdSender['condition'] ?? 'passive',
            'startedAt' => $stUssdSender['startedAt'] ?? null
        ];

        return new Response($status);
    }

    #[PutMapping('subs/{name}')]
    public function startSubs(string $name): void
    {
        switch ($name) {
            case 'SubReceiver':
                SubReceiver::dispatch();
                break;
            case 'SubTransmitter':
                SubTransmitter::dispatch();
                break;
            default:
                ClientError::throw("Service {$name} not found", HttpCode::NOT_FOUND);
        }
    }

    #[DeleteMapping('subs/{name}')]
    public function stopSubs(string $name): void
    {
        switch ($name) {
            case 'SubReceiver':
                SubReceiver::stop();
                break;
            case 'SubTransmitter':
                SubTransmitter::stop();
                break;
            default:
                ClientError::throw("Service {$name} not found", HttpCode::NOT_FOUND);
        }
    }
}

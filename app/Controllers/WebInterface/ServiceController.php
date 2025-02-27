<?php

namespace App\Controllers\WebInterface;

use App\Threads\ProcessorCluster;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\DeleteMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\PutMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
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
}

<?php

namespace App\Controllers\WebInterface;

use Flytachi\Kernel\Src\Factory\Mapping\Annotation\GetMapping;
use Flytachi\Kernel\Src\Stereotype\Controller;
use Flytachi\Kernel\Src\Stereotype\View;

class MainController extends Controller
{
    #[GetMapping]
    public function main(): View
    {
        return View::render('template', 'main');
    }
}

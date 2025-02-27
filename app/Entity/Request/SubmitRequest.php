<?php

namespace App\Entity\Request;

use Flytachi\Kernel\Src\Stereotype\Request;

class SubmitRequest extends Request
{
    public function __construct(
        public int $phoneNumber,
        public string $message,
        public bool $enableInput = false,
    ) {
    }
}

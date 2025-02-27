<?php

namespace App\Entity\Dto;

class ResponseDto
{
    public function __construct(
        public string $message,
        public bool $enableInput
    ) {
    }
}

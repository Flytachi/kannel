<?php

namespace App\Entity\Dto;

class MsgDto
{
    public function __construct(
        public int $phoneNumber,
        public string $message,
        public bool $input
    ) {
    }
}

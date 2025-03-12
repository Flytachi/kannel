<?php

namespace App\Entity\Dto;

class SmsMsgDto
{
    public function __construct(
        public int $phoneNumber,
        public string $message
    ) {
    }
}

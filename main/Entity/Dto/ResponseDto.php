<?php

namespace Main\Entity\Dto;

class ResponseDto
{
    public function __construct(
        public string $message,
        public bool $enableInput
    ) {
    }
}

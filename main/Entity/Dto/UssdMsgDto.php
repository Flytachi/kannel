<?php

namespace Main\Entity\Dto;

class UssdMsgDto
{
    public function __construct(
        public int $phoneNumber,
        public string $message,
        public bool $input
    ) {
    }
}

<?php

namespace Main\Entity\Request;

use Flytachi\Kernel\Src\Errors\ClientError;
use Flytachi\Kernel\Src\Stereotype\RequestObject;

class SubmitRequest extends RequestObject
{
    public function __construct(
        public int|string $phoneNumber,
        public string $message,
        public bool|string $enableInput = false,
    ) {
        if (is_string($this->phoneNumber)) {
            if (!is_numeric($this->phoneNumber)) {
                ClientError::throw("Phone number must be a numeric value");
            }
            $this->phoneNumber = (int) $this->phoneNumber;
        }
        if (is_string($this->enableInput)) {
            $this->enableInput = (bool) $this->enableInput;
        }
    }
}

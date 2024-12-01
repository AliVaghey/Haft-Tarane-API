<?php

namespace App\Traits;

trait Payable
{
    abstract public function paymentFailed(...$params);
    abstract public function paid(...$params);
}

<?php

use App\Helpers\SMS\SMS;

function sms(bool $isflash = false)
{
    return new SMS($isflash);
}

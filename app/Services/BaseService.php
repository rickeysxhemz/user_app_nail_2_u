<?php

namespace App\Services;

use App\Http\Traits\GlobalErrorHandlingTrait;
use App\Http\Traits\AppCommonTrait;

class BaseService
{
    use GlobalErrorHandlingTrait, AppCommonTrait;
}
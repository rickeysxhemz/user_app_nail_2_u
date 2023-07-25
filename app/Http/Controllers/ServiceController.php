<?php

namespace App\Http\Controllers;

use App\Http\Requests\Services\AddServicesRequest;
use App\Http\Requests\Services\EditServicesRequest;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\ServicesService;

class ServiceController extends Controller
{
    public function __construct(ServicesService $AuthService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->services_service = $AuthService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function all()
    {
        return $services_all = $this->services_service->all();

        if (!$services_all)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Services did not displayed!", $services_all));

        return ($this->global_api_response->success(1, "Services displayed successfully!", $services_all['record']));

    }
}

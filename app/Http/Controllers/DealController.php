<?php

namespace App\Http\Controllers;

use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\DealService;

class DealController extends Controller
{
    public function __construct(DealService $deals_service, GlobalApiResponse $GlobalApiResponse)
    {
        $this->deals_service = $deals_service;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function all()
    {
        $get_services = $this->deals_service->all();

        if (!$get_services)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Deals did not fetched!", $get_services));

        return ($this->global_api_response->success(count($get_services), "All deals fetched successfully!", $get_services['record']));
    }

    public function getDealArtist($id)
    {
        $get_services = $this->deals_service->getDealArtist($id);

        if (!$get_services)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Deals did not fetched!", $get_services));

        return ($this->global_api_response->success(count($get_services), "All deals fetched successfully!", $get_services['record']));
    }
    
    public function getDealService($id)
    {
        $get_services = $this->deals_service->getDealService($id);

        if (!$get_services)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Deal Service did not fetched!", $get_services));

        return ($this->global_api_response->success(count($get_services), "Deal service fetched successfully!", $get_services['record']));
    }
}
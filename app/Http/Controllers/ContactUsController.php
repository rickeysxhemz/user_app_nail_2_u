<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactUsRequests\ContactUsRequest;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\ContactUsService;

class ContactUsController extends Controller
{
    public function __construct(ContactUsService $ContactUsService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->contactus_service = $ContactUsService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function contactUs(ContactUsRequest $request)
    {
        $contact_us = $this->contactus_service->contactUs($request);

        if (!$contact_us)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Message did not sent!", $contact_us));

        return ($this->global_api_response->success(1, "Message sent successfully!", $contact_us));
    }
}

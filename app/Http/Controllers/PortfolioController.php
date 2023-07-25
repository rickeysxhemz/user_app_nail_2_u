<?php

namespace App\Http\Controllers;

use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Libs\Response\GlobalApiResponse;
use App\Services\PortfolioService;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function __construct(PortfolioService $PortfolioService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->portfolio_service = $PortfolioService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function getDetails()
    {
        $portfolio_details = $this->portfolio_service->getDetails();

        if (!$portfolio_details)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Portfolio details did not fetched!", $portfolio_details));

        return ($this->global_api_response->success(1, "Portfolio details fetched successfully!", $portfolio_details));
    }

    public function getImages()
    {
        $portfolio_images = $this->portfolio_service->getImages();

        if (!$portfolio_images)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "User portfolio images did not fetched!", $portfolio_images));

        return ($this->global_api_response->success(1, "User portfolio images fetched successfully!", $portfolio_images));
    }

    public function uploadImage(Request $request)
    {
        $upload_image = $this->portfolio_service->uploadImage($request);

        if ($upload_image === intval(GlobalApiResponseCodeBook::FILE_NOT_EXISTS['outcomeCode']))
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::FILE_NOT_EXISTS, "Portfolio image not found in request!", $upload_image));

        if (!$upload_image)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Portfolio image did not uploaded!", $upload_image));

        return ($this->global_api_response->success(1, "Portfolio imaget uploaded successfully!", $upload_image));
    }

    public function deleteImage(Request $request)
    {
        $delete_image = $this->portfolio_service->deleteImage($request);

        if (!$delete_image)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Portfolio image did not deleted!", $delete_image));

        return ($this->global_api_response->success(1, "Portfolio imaget deleted successfully!", $delete_image));
    }
}

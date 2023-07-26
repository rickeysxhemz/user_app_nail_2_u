<?php

namespace App\Http\Controllers;

use App\Http\Requests\Favourite\CreateFavouriteRequest;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\FavouriteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavouriteController extends Controller
{
    public function __construct(FavouriteService $FavouriteService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->favourite_service = $FavouriteService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function all()
    {

        $all = $this->favourite_service->all();

        if ($all === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "favourite not found!", []));

        if (!$all)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "favourite failed!", $all));

        return ($this->global_api_response->success(count($all), "favourite fetch successfully!", $all));
    }

    public function create(CreateFavouriteRequest $request)
    {

        $create_favourite = $this->favourite_service->create($request);

        if ($create_favourite['outcomeCode'] === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "favourite found!", $create_favourite['record']));

        if (!$create_favourite['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "favourite failed!", $create_favourite['record']));

        return ($this->global_api_response->success(count($create_favourite), "favourite created successfully!", $create_favourite['record']));
    }
    
    public function deleteFavourite(CreateFavouriteRequest $request)
    {

        $delete_favourite = $this->favourite_service->deleteFavourite($request);

        if ($delete_favourite['outcomeCode'] === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "favourite not found!", $delete_favourite['record']));

        if (!$delete_favourite['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "favourite failed!", $delete_favourite['record']));

        return ($this->global_api_response->success(count($delete_favourite), "favourite deleted successfully!", $delete_favourite['record']));
    }
}

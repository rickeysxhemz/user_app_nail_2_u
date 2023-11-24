<?php

namespace App\Http\Controllers;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\CardService;
use App\Http\Requests\CardRequests\AddRequest;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function __construct(CardService $CardService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->card_service = $CardService;
        $this->global_api_response = $GlobalApiResponse;
    }

    function create(AddRequest $request) {
        $create_card = $this->card_service->create($request);
        
        if (!$create_card)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Card failed!", $create_card['record']));

        return ($this->global_api_response->success(count($create_card), "Card created successfully!", $create_card['record']));
    }

    public function all()
    {
        $cards = $this->card_service->all();

        if (!$cards['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Cards details did not fetched!", $cards['record']));

        return ($this->global_api_response->success(count($cards), "Cards details fetched successfully!", $cards['record']));
    }
}

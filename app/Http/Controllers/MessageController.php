<?php

namespace App\Http\Controllers;

use App\Http\Requests\Messages\CreateMessageRequest;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\MessageService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(MessageService $MessageService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->message_service = $MessageService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function all(Request $request)
    {
        $messages = $this->message_service->all($request);

        if (!$messages['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Message did not fetched!", $messages['record']));

        return ($this->global_api_response->success(count($messages), "Message fetched successfully!", $messages['record']));
    }
    public function create(CreateMessageRequest $request)
    {

        $create_message = $this->message_service->create($request);

        if (!$create_message['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Message failed!", $create_message['record']));

        return ($this->global_api_response->success(count($create_message), "Message created successfully!", $create_message['record']));
    }
}
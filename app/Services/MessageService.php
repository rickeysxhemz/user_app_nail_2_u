<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use App\Helper\Helper;
use Illuminate\Support\Facades\Auth;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\CommonTrait;

class MessageService extends BaseService
{
    use CommonTrait;
    public function all($request)
    {
        try {
            
            $messages = Message::where(function ($query) use ($request) {
                $query->where('receiver_id', $request->receiver_id)->where('sender_id', Auth::id());
            })->orWhere(function ($query) use ($request){
                $query->where('receiver_id', Auth::id())->where('sender_id', $request->receiver_id);
            })->get();
            
            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $messages);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("MessagesService: All", $error);
            return Helper::returnRecord(false, []);
        }
    }

    public function create($request)
    {  
        try {
                DB::beginTransaction();
                $message = new Message();
                $message->receiver_id = $request->receiver_id;
                $message->sender_id = Auth::id();
                $message->message = $request->message;
                $message->save();

                $user_name = User::find(Auth::id());
                $title = 'new message';
                $body = $user_name->username.' send a message';

                $data = [
                        'status' => 'chat', 
                        'sender' =>  Auth::id(), 
                        'receiver' => $request->receiver_id, 
                        'message' => $request->message
                    ];

                $this->pusher($request->receiver_id, $title, $body, $data);

                DB::commit();

                return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_CREATED['outcomeCode'], ['message_id' => $message->id]);

        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("MessageService: Create", $error);
            return Helper::returnRecord(false, []);
        }
    }
}
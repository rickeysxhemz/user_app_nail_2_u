<?php

namespace App\Services;

use Exception;
use App\Helper\Helper;
use Illuminate\Support\Facades\Auth;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Illuminate\Support\Facades\DB;
use App\Models\Card;

class CardService extends BaseService
{
    public function create($request)
    {
        try {
            DB::beginTransaction();

            $card = new Card();
            $card->user_id = Auth::id();
            $card->card_name = $request->card_name;
            $card->card_number = $request->card_number;
            $card->exp_date = $request->exp_date;
            $card->cvv = $request->cvv;
            $card->save();

            DB::commit();

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_CREATED['outcomeCode'], ['card' => $card]);
        }
        catch (Exception $e) {
            DB::rollBack();
        
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("CardService: Create", $error);
            return false;
        }
    }

    public function all()
    {
        try {
            $cards = Card::where('user_id', Auth::id())->get();
            

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $cards);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("CradService: all", $error);
            return Helper::returnRecord(false, []);
        }
    } 
}
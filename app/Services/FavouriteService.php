<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use App\Helper\Helper;
use Illuminate\Support\Facades\Auth;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Illuminate\Support\Facades\DB;

class FavouriteService extends BaseService
{
    public function create($request)
    {
        try {
            $favourite_artist = DB::table('favourite_artist')->where('artist_id', $request->artist_id)->where('user_id', Auth::id())->first();

            if (!$favourite_artist) {
                DB::beginTransaction();
                
                DB::table('favourite_artist')->insert([
                    [
                        'artist_id' => $request->artist_id,
                        'user_id' => Auth::id()
                    ]
                 ]);

                DB::commit();

                return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_CREATED['outcomeCode'], []);
            }
             
            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'], 'artist and user exist');
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("FavouriteService: send", $error);
            return Helper::returnRecord(false, []);
        }
    }
    
    public function deleteFavourite($request)
    {
        try {
            $favourite_artist = DB::table('favourite_artist')->where('artist_id', $request->artist_id)->where('user_id', Auth::id())->first();

            if ($favourite_artist) {
                
                DB::table('favourite_artist')->where('artist_id', $request->artist_id)->where('user_id', Auth::id())->delete();

                return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_CREATED['outcomeCode'], []);
            }
             
            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'], 'artist and user not exist');
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("FavouriteService: delete", $error);
            return Helper::returnRecord(false, []);
        }
    }
}

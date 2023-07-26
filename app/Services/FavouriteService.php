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
    public function all()
    {
        try {
            $artist_data = [];
            $artists = User::with([
                'reviews',
                'jobs' => function ($q) {
                    $q->where("status", "done");
                }    
                // },
                // 'services'
            ])
                ->whereHas("roles", function ($q) {
                    $q->where("name", "artist");
                })
                ->whereNotNull('phone_verified_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('favourite_artist')
                          ->whereRaw('users.id = favourite_artist.artist_id');
                })
                ->orderby('id', 'desc')
                ->get(['id', 'username', 'image_url', 'cv_url', 'cover_image']);
           
            if ($artists) {
                foreach ($artists as $artist) {
                    $data['id'] = $artist->id;
                    $data['username'] = $artist->username;
                    $data['image_url'] = env('COMMON_PATH').$artist->image_url;
                    $data['cover_image'] =  env('COMMON_PATH').$artist->cover_image;
                    $data['ratings'] = round($artist->reviews->avg('rating'), 1);
                    $data['jobs_done'] = count($artist->jobs);
                    $status = 0;
                    $favourite_status = DB::table('favourite_artist')->where('artist_id', $artist->id)->where('user_id', Auth::id())->first();
                    if($favourite_status){
                        $status = 1;
                    }
                    $data['favourite'] = $status;
                    $data['expert'] = '';
                    $data['service_price'] = DB::table('artist_services')->where('artist_id', $artist->id)->sum('price');

                    array_push($artist_data, $data);
                }
                return $artist_data;
            }
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("FavouriteService: all", $error);
            return Helper::returnRecord(false, []);
        }
    }

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

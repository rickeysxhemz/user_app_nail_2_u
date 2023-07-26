<?php

namespace App\Services;

use App\Models\User;
use App\Models\Rating;
use App\Helper\Helper;
use Illuminate\Support\Facades\Auth;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Traits\CommonTrait;

class ReviewService extends BaseService
{
    use CommonTrait;
    
    public function send($request)
    {
        try {
                DB::beginTransaction();
                $rating = new Rating;
                $rating->artist_id = $request->artist_id;
                $rating->client_id = Auth::id();
                $rating->rating = $request->rating;
                $rating->review = $request->review;
                $rating->save();

                $user_name = User::find(Auth::id());
                $artist = 'artist';
                $title = 'Good news you have get review';
                $body = $user_name->username.' send '. $request->rating . ' star';
                $booking_created = '10';
                $this->notifications($request->artist_id, $title, $body, $booking_created,  $artist);
                DB::commit();
                return $rating;

        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("User:ReviewService: send", $error);
            return false;
        }
    }
    
    public function getReview($id)
    {
        try {

            $user_detail = User::with([
                'ArtistService'
            ])
            ->where("id", $id)
            ->get(['id', 'username', 'image_url', 'cv_url', 'cover_image']);
                
            $reviews = [];
            $rates = Rating::where('artist_id', $id)->pluck('rating')->toArray();
            if (count($rates) != 0) {
                $overall_rating = array_sum($rates) / count($rates);
                $ratings = Rating::where('artist_id', $id)->get();
                if ($ratings) {
                    foreach ($ratings as $rating) {

                        $user = User::find($rating['client_id']);
                        $temp['username'] = $user->username;
                        $temp['rating'] = $rating['rating'];
                        $temp['review'] = $rating['review'];
                        $temp['created_at'] = $rating['created_at'];
                        array_push($reviews, $temp);
                    }
                    
                    return  [
                        'overall_rating' => round($overall_rating,1),
                        'artist_name' => $user_detail[0]->username,
                        'cover_image' =>  env('COMMON_PATH').$user_detail[0]->cover_image,
                        'services' => $user_detail[0]->ArtistService,
                        'ratings' => $reviews
                    ];
                }
            }
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];

        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("User:ReviewService: getReview", $error);
            return false;
        }
    }
}    

<?php

namespace App\Services;

use Exception;
use App\Helper\Helper;
use App\Models\Booking;
use App\Models\Portfolio;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Libs\Response\GlobalApiResponseCodeBook;

class PortfolioService extends BaseService
{
    public function getDetails()
    {
        try {
            $rates = [];
            $visits = [];
            $user = User::find(Auth::id());
            $ratings = Rating::where('artist_id', Auth::id())->get(['rating']);

            foreach ($ratings as $rating) {
                array_push($rates, $rating['rating']);
            }

            $bookings = Booking::where('artist_id', Auth::id())->get('status');
            foreach ($bookings as $booking) {
                array_push($visits, $booking['status']);
            }

            $data = [
                'username' => $user->username,
                'experience' => $user->experience,
                'rating' => array_sum($rates) / count(array_filter($rates)),
                'Jobs done' => count($visits)
            ];

            return $data;
        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("PortfolioService: getDetails", $error);
            return false;
        }
    }

    public function getImages()
    {
        try {
            $pics = [];
            $images = Portfolio::where('artist_id', Auth::id())->get();
            if ($images) {
                foreach ($images as $image) {
                    $temp['image_url'] = $image['image_url'];
                    $temp['absolute_image_url'] = $image['absolute_image_url'];
                    array_push($pics, $temp);
                }
                return $pics;
            }
            return intval(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']);
        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("PortfolioService: getImages", $error);
            return false;
        }
    }

    public function uploadImage($request)
    {
        try {
            if (!$request->hasFile('image_url')) {
                return GlobalApiResponseCodeBook::FILE_NOT_EXISTS['outcomeCode'];
            }

            $filenameWithExt = $request->file('image_url')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('image_url')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $path = $request->file('image_url')->move('storage\artist\portfolio', $fileNameToStore);

            $portfolio = new Portfolio;
            $portfolio->artist_id = Auth::id();
            $portfolio->image_url = $path;
            $portfolio->save();

            return $portfolio;
        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("PortfolioService: uploadImage", $error);
            return false;
        }
    }

    public function deleteImage($request)
    {
        try {
            $portfolio = Portfolio::where('id', $request->image_id)->where('artist_id', Auth::id())->delete();
            return $portfolio;
        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("PortfolioService: deleteImage", $error);
            return false;
        }
    }
}

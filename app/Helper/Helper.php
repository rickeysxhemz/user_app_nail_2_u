<?php

namespace App\Helper;

use App\Models\ErrorLog;
use Carbon\Carbon;

class Helper
{
    public static function storeCvUrl($request)
    {
        if (!$request->hasFile('cv_url')) {
            return false;
        }
        $file = $request->File('cv_url');
        $file_name = $file->hashName();
        $request->cv_url->move(public_path('storage/artistCvs'), $file_name);
        $destination = 'storage/artistCvs/' . $file_name;
        return $destination;
    }

    public static function storeImageUrl($request, $user)
    {
        if (!$request->hasFile('image_url')) {
            return false;
        }
        if ($user->image_url != 'storage/profileImages/default-profile-image.png') {
            unlink(base_path() . '/public/' . $user->image_url);
        }
        $file = $request->File('image_url');
        $file_name = $file->hashName();
        $request->image_url->move(public_path('storage/profileImages'), $file_name);
        $destination = 'storage/profileImages/' . $file_name;
        return $destination;
    }

    public static function errorLogs($function_name, $error)
    {
        $error_log = new ErrorLog;
        $error_log->function_name = $function_name;
        $error_log->exception = $error;
        $error_log->save();
    }

    public static function returnRecord($outCome = null, $record = null)
    {
        return ['outcomeCode' => intval($outCome), 'record' => $record];
    }

    public static function getDays($time)
    {
        $to = Carbon::createFromFormat('Y-m-d H:s:i', $time);
        $from = Carbon::createFromFormat('Y-m-d H:s:i', now());

        $diff_in_days = $to->diffInDays($from);
        if ($diff_in_days == 0) {
            return 'Today';
        } else {
            return $diff_in_days . ' days ago';
        }
    }

    public static function calculateArtistRating($rating)
    {
        if (count($rating) != 0) {
            $rating = array_sum($rating) / count($rating);
            return round($rating, 2);
        }
        return 0;
    }
}

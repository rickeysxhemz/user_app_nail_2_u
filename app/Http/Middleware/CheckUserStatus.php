<?php

namespace App\Http\Middleware;

use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Libs\Response\GlobalApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->checkUserStatus())
            return response([
                '_metadata' => [
                    "outcome" => "COMPLETE_YOUR_PROFILE",
                    "outcomeCode" => 15,
                    "httpResponseCode" => 200,
                    "numOfRecords" => 1,
                    "message" => "Complete your profile!"
                ],
                'records' => Auth::user()->only('id', 'username', 'email', 'phone_no', 'address', 'experience', 'cv_url', 'image_url', 'total_balance', 'absolute_cv_url', 'absolute_image_url'),
                'errors' => [],
            ]);

        return $next($request);
    }

    private function checkUserStatus()
    {
        $flag = false;

        if (Auth::user()->password === null or Auth::user()->password === '')
            return true;

        return $flag;
    }
}

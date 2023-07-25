<?php

namespace App\Services;

use App\Helper\Helper;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SettingService extends BaseService
{
    public function update($request)
    {
        try {
            DB::beginTransaction();
            $setting_update = Setting::where('user_id', Auth::id())->first();
            $request->has('private_account') ? ($setting_update->private_account = $request->private_account) : null;
            $request->has('secure_payment') ? ($setting_update->secure_payment = $request->secure_payment) : null;
            $request->has('sync_contact_no') ? ($setting_update->sync_contact_no = $request->sync_contact_no) : null;
            $request->has('app_notification') ? ($setting_update->app_notification = $request->app_notification) : null;
            $request->has('language') ? ($setting_update->language = $request->language) : null;
            $setting_update->save();
            DB::commit();
            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_UPDATED['outcomeCode'], $setting_update->toArray());
        } catch (\Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("SettingService: add", $error);
            return false;
        }
    }

    public function getSetting()
    {
        try {
            $get_setting = Setting::where('user_id', Auth::id())->first();
             
            return $get_setting;

        } catch (\Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("SettingService: getSetting", $error);
            return false;
        }
    }

    public function resetPassword($request)
    {
        try {
            DB::beginTransaction();

            $user = User::where('id', Auth::id())->first();
            
            if ($user && Hash::check($request->password, $user->password)) {
                return intval(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode']);
            }
            if ($user) {
    
                $user->password = Hash::make($request->password);
                $user->save();

                $response = [
                    'message' => 'Password has been resetted!',
                ];
                DB::commit();
                return $response;
            }
            return intval(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']);
        } catch (\Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("SettingService: resetPassword", $error);
            return false;
        }
    }
}

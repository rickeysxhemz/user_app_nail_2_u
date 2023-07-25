<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Http\Requests\AuthRequests\ResetPasswordRequest;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\SettingService;

class SettingController extends Controller
{
    public function __construct(SettingService $AuthService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->services_service = $AuthService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function update(UpdateSettingsRequest $request)
    {
        $update_settings = $this->services_service->update($request);

        if (!$update_settings)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Setting did not updated!", $update_settings));

        return ($this->global_api_response->success(1, "Setting updated successfully!", $update_settings['record']));
    }

    public function getSetting()
    {
        $getSetting = $this->services_service->getSetting();

        if (!$getSetting)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Setting did not updated!", $getSetting));

        return ($this->global_api_response->success(1, "Setting updated successfully!", $getSetting));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $reset_password = $this->services_service->resetPassword($request);

        if (!$reset_password)
            return $this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Password didn't reset!", $reset_password);
        
        if ($reset_password == GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'])
            return $this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS, "This is your old password", []);

        return $this->global_api_response->success(1, "Password has been reset successfully!", $reset_password);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequests\ForgotPasswordRequest;
use App\Http\Requests\AuthRequests\VerifyCodeRequest;
use App\Http\Requests\AuthRequests\VerifyPhoneRequest;
use App\Http\Requests\AuthRequests\ResetPasswordRequest;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Http\Requests\AuthRequests\RegisterRequest;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Laravel\Socialite\Facades\Socialite;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(AuthService $AuthService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->auth_service = $AuthService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function register(RegisterRequest $request)
    {
        $register = $this->auth_service->register($request);
        
        if ($register['outcomeCode'] === GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS, "Record Already Exist!", $register['record']));
        if (!$register)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "User did not registered!", $register));

        return ($this->global_api_response->success(1, "Artist registered successfully!", $register));
    }

    public function login(LoginRequest $request)
    {
        $login = $this->auth_service->login($request);

        if (!$login)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Login not successful!", []));

        if ($login['outcomeCode'] === GlobalApiResponseCodeBook::INVALID_CREDENTIALS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INVALID_CREDENTIALS, "Your email or password is invalid!", []));

        if ($login['outcomeCode'] === GlobalApiResponseCodeBook::EMAIL_NOT_VERIFIED['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::EMAIL_NOT_VERIFIED, "Your email is not verified!", $login['record']));
        
        return ($this->global_api_response->success(1, "Login successfully!", $login['record']));
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $forgot_password = $this->auth_service->forgotPassword($request);

        if (!$forgot_password)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Email for sending resetting password did not sent!", $forgot_password));

        if ($forgot_password['outcomeCode'] == GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']) {
            return $this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "invalid email!", []);
        }
        return ($this->global_api_response->success(1, "successfully!", $forgot_password['record']));
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $verify_code = $this->auth_service->verifyCode($request);

        if (!$verify_code)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "otp code not verified", $verify_code));

        return ($this->global_api_response->success(1, "code verify successfully!", $verify_code));
    }

    public function verifyPhone(VerifyPhoneRequest $request)
    {
        $verify_phone = $this->auth_service->verifyPhone($request);

        if (!$verify_phone)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "phone did not exist", $verify_phone));

        return ($this->global_api_response->success(1, $verify_phone, 0));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $reset_password = $this->auth_service->resetPassword($request);
        
        if ($reset_password == GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return $this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "Record not found for resetting password!", []);
        
        if ($reset_password == GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'])
            return $this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS, "This is your old password", []);

        if (!$reset_password)
            return $this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Password didn't reset!", $reset_password);

        return $this->global_api_response->success(1, "Password has been reset successfully!", $reset_password);
    }

    public function logout()
    {
        $logout = $this->auth_service->logout();

        if ($logout === GlobalApiResponseCodeBook::NOT_AUTHORIZED['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::NOT_AUTHORIZED, "User is not authorized to logout!", $logout));

        if (!$logout)
            return (new GlobalApiResponse())->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Logout not successful!", $logout);

        return (new GlobalApiResponse())->success(1, "User logout successfully!", $logout);
    }

    public function verifyEmail(string $token, string $email)
    {
        $verify_email = $this->auth_service->verifyEmail($token, $email);

        if ($verify_email == GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']) {
            return $this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "Record not found for email verification!", []);
        }

        if (!$verify_email) {
            return $this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Email didn't verified!", $verify_email);
        }

        return $this->global_api_response->success(1, "Email verified successfully!", $verify_email);
    }
    
    public function resendOtpCode($id)
    {
        $resend_otp_code = $this->auth_service->resendOtpCode($id);

        if ($resend_otp_code == GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']) {
            return $this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "Record not found for otp verification!", []);
        }

        if (!$resend_otp_code) {
            return $this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "otp didn't verified!", $resend_otp_code);
        }

        return $this->global_api_response->success(1, "otp send successfully!", $resend_otp_code);
    }

    public function redirectToProvider($provider)
    {
        $user = Socialite::driver($provider)->redirect();
        return $user;
    }

    public function handleProviderCallback($provider)
    {
        return $this->auth_service->handleProviderCallback($provider);
    }
}

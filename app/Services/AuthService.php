<?php

namespace App\Services;

use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Models\SocialIdentity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use App\Helper\Helper;
use App\Jobs\SendEmailVerificationMail;
use App\Jobs\SendPasswordResetMail;
use App\Jobs\PasswordResetSuccessfull;
use App\Models\EmailVerify;
use App\Models\Setting;
use App\Models\User;
use App\Models\OTP;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Exception;
use Twilio\Rest\Client;

class AuthService extends BaseService
{
    public function register($request)
    {
        try {
            DB::beginTransaction();
            
            $userexist = User::where('email', $request->email)->first();
            // dd($userexist);
            if($userexist &&  $userexist->phone_verified_at == null){
               
                $phoneexist = User::where('phone_no', $request->phone_no)->first();
                
                if($phoneexist &&  $phoneexist->phone_verified_at == null){
                     
                    $user = User::find($phoneexist->id);
                    $user->username = $request->username;
                    // $user->email = $request->email;
                    $user->password = Hash::make($request->password);
                    $user->phone_no = $request->phone_no;
                    $user->zipcode = '97836';
                    $user->image_url = 'storage/profileImages/default-profile-image.png';
                    $user->cv_url = null;
                    $user->save();
                    
                    $otp = new OTP();
                    $otp->user_id = $user->id;
                    $otp->otp_value = random_int(100000, 999999);
                    // $otp->otp_value = '123456';
                    $otp->save();
                    
                    $account_sid = 'AC60d20bdd51da17c92e5dd29c9f22e521';
                    $auth_token = 'bb3720d64d89358fe6915c168f5474d4';
                    $twilio_number = '+13158478569';
                    
                    // $receiverNumber = $request->phone_number;
                    // $message = 'this is your code';
                    // $client = new Client($account_sid, $auth_token);
                    // $client->messages->create($receiverNumber, [
                    //     'from' => $twilio_number]);
                    
                    $receiverNumber = $request->phone_no;
                    $message = 'This message from Nails2u here is your six digit otp  ' . $otp->otp_value;
                    $client = new Client($account_sid, $auth_token);
                    $client->messages->create($receiverNumber, [
                        'from' => $twilio_number, 
                        'body' => $message]);
                    
                    DB::commit();
                    return $user;
                }
    
                $user = User::find($userexist->id);
                $user->username = $request->username;
                // $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->phone_no = $request->phone_no;
                $user->zipcode = '97836';
                $user->image_url = 'storage/profileImages/default-profile-image.png';
                $user->cv_url = null;
                $user->save();

                $otp = new OTP();
                $otp->user_id = $user->id;
                $otp->otp_value = random_int(100000, 999999);
                // $otp->otp_value = '123456';
                $otp->save();
                
                $account_sid = 'AC60d20bdd51da17c92e5dd29c9f22e521';
                $auth_token = 'bb3720d64d89358fe6915c168f5474d4';
                $twilio_number = '+13158478569';
                
                // $receiverNumber = $request->phone_number;
                // $message = 'this is your code';
                // $client = new Client($account_sid, $auth_token);
                // $client->messages->create($receiverNumber, [
                //     'from' => $twilio_number]);
                
                $receiverNumber = $request->phone_no;
                $message = 'This message from Nails2u here is your six digit otp   ' . $otp->otp_value;
                $client = new Client($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number, 
                    'body' => $message]);

                DB::commit();
                return $user;
            }
            
            if($userexist &&  $userexist->phone_verified_at !== null){
                return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'], ['The email has already been taken.']);
            }
            $phoneexist = User::where('phone_no', $request->phone_no)->first();
            if($phoneexist &&  $phoneexist->phone_verified_at !== null){
                return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'], ['The Phone has already been taken.']);
            }
            
            $user = new User();
            $user->username = $request->username;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone_no = $request->phone_no;
            $user->zipcode = '97836';
            $user->image_url = 'storage/profileImages/default-profile-image.png';
            $user->cv_url = null;
            $user->save();

            $setting = new Setting();
            $setting->user_id = $user->id;
            $setting->private_account = 0;
            $setting->secure_payment = 1;
            $setting->sync_contact_no = 0;
            $setting->app_notification = 1;
            $setting->save();

            $user_role = Role::findByName('user');
            $user_role->users()->attach($user->id);

            // $verify_email_token = Str::random(140);
            // $email_verify = new EmailVerify;
            // $email_verify->email = $request->email;
            // $email_verify->token = $verify_email_token;
            // $email_verify->save();

            // $mail_data = [
            //     'email' => $request->email,
            //     'token' => $verify_email_token
            // ];

            // SendEmailVerificationMail::dispatch($mail_data);

            $otp = new OTP();
            $otp->user_id = $user->id;
            $otp->otp_value = random_int(100000, 999999);
            // $otp->otp_value = '123456';
            $otp->save();

            $account_sid = 'AC60d20bdd51da17c92e5dd29c9f22e521';
            $auth_token = 'bb3720d64d89358fe6915c168f5474d4';
            $twilio_number = '+13158478569';
            
            $receiverNumber = $request->phone_no;
            $message = 'This message from Nails2u here is your six digit otp  ' . $otp->otp_value;
            $client = new Client($account_sid, $auth_token);
            $client->messages->create($receiverNumber, [
                'from' => $twilio_number, 
                'body' => $message]);

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            // dd($e);
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: register", $error);
            return false;
        }
    }

    public function login($request)
    {
        try {
            $credentials = $request->only('email', 'password');

            $user = User::whereHas('roles', function ($q) {
                $q->where('name', 'user');
            })
                ->where('email', '=', $credentials['email'])
                ->with('setting', 'FavouriteArtist')
                ->first();
            if(isset($user->phone_verified_at) && $user->phone_verified_at !== null){
                if (
                    Hash::check($credentials['password'], isset($user->password) ? $user->password : null)
                    &&
                    $token = $this->guard()->attempt($credentials)
                ) {
    
                    $roles = Auth::user()->roles->pluck('name');
                    $data = Auth::user()->toArray();
                    unset($data['roles']);
    
                    $data = [
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => $this->guard()->factory()->getTTL() * 60,
                        'user' => Auth::user()->only('id', 'username', 'email', 'phone_no', 'address', 'experience', 'cv_url', 'image_url', 'total_balance', 'absolute_cv_url', 'absolute_image_url'),
                        'roles' => $roles,
                        'settings' => Auth::user()->setting->only('user_id', 'private_account', 'secure_payment', 'sync_contact_no', 'app_notification', 'language')
                    ];
                    return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], $data);
                }
                return Helper::returnRecord(GlobalApiResponseCodeBook::INVALID_CREDENTIALS['outcomeCode'], []);
            }
            return Helper::returnRecord(GlobalApiResponseCodeBook::INVALID_CREDENTIALS['outcomeCode'], []);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: login", $error);
            return false;
        }
    }
    public function socialLogin($request)
    {
        try {
            $user = User::where('email',$request->email)
            ->where('social_login_token',$request->token)
            ->first();
            if (!$user) {
                $user = new User();
                $user->username = $request->name;
                $user->email = $request->email;
                $user->password = Hash::make(Str::random(8));
                $user->social_login_token= $request->token;
                $user->zipcode= '97836';
                $user->cv_url= null;
                $user->save();
            }
            $token = $this->guard()->login($user);
            $roles = Auth::user()->roles->pluck('name');
            $data = Auth::user()->toArray();
            unset($data['roles']);
            $data = [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->guard()->factory()->getTTL() * 60,
                'user' => Auth::user()->only('id', 'username', 'email'),
                'roles' => $roles,
                // 'settings' => Auth::user()->setting->only('user_id', 'private_account', 'secure_payment', 'sync_contact_no', 'app_notification', 'language')
            ];
            return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], $data);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: socialLogin", $error);
            return false;
        }
    }

    public function forgotPassword($request)
    {
        try {
            DB::beginTransaction();
            if($request->has('email') && isset($request->email))
            {
                $password_reset_token = Str::random(140);
                $password_reset = new PasswordReset();
                $password_reset->email = $request->email;
                $password_reset->token = $password_reset_token;
                $password_reset->save();

                $user = User::whereHas('roles', function ($q) {
                                $q->where('name', 'user');
                            })
                            ->where('email', $request->email)
                            ->first();
                if($user) {
                    $otp = new OTP();
                    $otp->user_id = $user->id;
                    $otp->otp_value = random_int(100000, 999999);
                    $otp->save();
    
                    $mail_data = [
                        "token" => $otp->otp_value,
                        "email" => $request->email
                    ];
                    SendPasswordResetMail::dispatch($mail_data);
                    $response = [
                        "message" => "last 4 digits",
                        "digit" => substr($user->phone_no,-4)
                    ];
                    DB::commit();
                    return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], $response);
                } else {
                    
                    return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'], ['invalid email!']);
                    // $response = [
                    //     "message" => "invalid email!"
                    // ];
                    // $artist_exist = User::whereHas('roles', function ($q) {
                    //                     $q->where('name', 'artist');
                    //                 })
                    //                 ->where('email', $request->email)
                    //                 ->first();
                    // if($artist_exist) {
                    //     $response = [
                    //         "message" => "This email exist as artist"
                    //     ];
                    // } else {
                    //     $response = [
                    //         "message" => "invalid email!"
                    //     ];
                    // }
                }

                
            }
            else
            {
                $user = User::whereHas('roles', function ($q) {
                                $q->where('name', 'user');
                            })
                            ->where('phone_no', $request->phone_number)
                            ->first();
                
                if($user){
                    
                    $otp = new OTP();
                    $otp->user_id = $user->id;
                    $otp->otp_value = random_int(100000, 999999);
                    // $otp->otp_value = '123456';
                    $otp->save();
        
                    $account_sid = 'AC60d20bdd51da17c92e5dd29c9f22e521';
                    $auth_token = 'bb3720d64d89358fe6915c168f5474d4';
                    $twilio_number = '+13158478569';
                    
                    $receiverNumber = $request->phone_number;
                    $message = 'This message from Nails2u here is your six digit otp  ' . $otp->otp_value;
                    // dd($message);
                    $client = new Client($account_sid, $auth_token);
                    $client->messages->create($receiverNumber, [
                        'from' => $twilio_number, 
                        'body' => $message]);
    
                    $response = [
                        "message" => "six digit code send your number!",
                        "phone_number" => $request->phone_number
                    ];
                    DB::commit();
                    return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], $response);
                } else {
                    return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'], ['invalid number!']);
                    // $response = [
                    //     "message" => "invalid number!"
                    // ];
                    // $artist_exist = User::whereHas('roles', function ($q) {
                    //                     $q->where('name', 'artist');
                    //                 })
                    //                 ->where('phone_no', $request->phone_number)
                    //                 ->first();
                    // if($artist_exist) {
                    //     $response = [
                    //         "message" => "This phone number exist as artist"
                    //     ];
                    // } else {
                    //     $response = [
                    //         "message" => "invalid number!"
                    //     ];
                    // }    
                }

                
            }
            // return $response;
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: forgotPassword", $error);
            return false;
        }
    }

    public function verifyCode($request)
    {
        try {
            if($request->has('email') && isset($request->email))
            {
                $user = User::where('email', $request->email)->first();
            }
            else 
            {
                $user = User::where('phone_no', $request->phone_number)->first();
            }
            
            $otp = OTP::where('user_id', $user->id)->where('otp_value', $request->code)->first();
            if($otp && $request->has('register_otp') && isset($request->register_otp)){
                $user->phone_verified_at = now();
                $user->save();
                OTP::where('user_id', $user->id)->latest()->delete();
            }
            return $otp;
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: forgotPassword", $error);
            return false;
        }
    }

    public function verifyPhone($request)
    {
        try {

            $error = [];

            $user = User::where('phone_no', $request->phone_no)->first();
            
            if($user && $user->phone_verified_at !== null){
                $model_has_roles = DB::table('model_has_roles')
                ->where('model_id', $user->id)->first();
                
                if($model_has_roles && $model_has_roles->role_id == '3') {
                    return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'], ['The phone number has already been taken as artist']);
                } else {
                    return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'], ['The phone number has already been taken']);
                }
            } else {
                return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], '');
            }

        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: verifyPhone", $error);
            return false;
        }
    }

    public function emailExist($request)
    {
        try {

            $user = User::where('email', $request->email)->first();
            
            if($user && $user->phone_verified_at !== null){
                $model_has_roles = DB::table('model_has_roles')
                ->where('model_id', $user->id)->first();
                
                if($model_has_roles && $model_has_roles->role_id == '3') {
                    return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'], ['The email has already been taken as artist']);
                } else {
                    if($request->has('type') && isset($request->type)){
                        return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], '');
                    }
                    return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode'], ['The email has already been taken']);
                }
            } else {
                return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], '');
            }

        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: emailExist", $error);
            return false;
        }
    }

    public function resetPassword($request)
    {
        try {
            DB::beginTransaction();
            if($request->has('email') && isset($request->email))
            {
                $user = User::where('email', $request->email)->first();
                
                if ($user && Hash::check($request->password, $user->password)) {
                    return intval(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode']);
                }
            }
            else
            {
                $user = User::where('phone_no', $request->phone_number)->first();
                
                if ($user && Hash::check($request->password, $user->password)) {
                    return intval(GlobalApiResponseCodeBook::RECORD_ALREADY_EXISTS['outcomeCode']);
                }
            }
            $record = OTP::where('user_id', $user->id)
                ->where('otp_value', $request->code)->latest()->first();
            if ($record) {
                // $user = User::where('email', $email)->first();
                $user->password = Hash::make($request->password);
                $user->save();

                OTP::where('user_id', $user->id)->latest()->delete();
                
                if($request->has('email') && isset($request->email))
                {
                    $mail_data = [
                        "email" => $request->email
                    ];
                    PasswordResetSuccessfull::dispatch($mail_data);
                }
                

                $response = [
                    'message' => 'Password has been resetted!',
                ];
                DB::commit();
                return $response;
            }
            return intval(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']);
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: resetPassword", $error);
            return false;
        }
    }

    public function logout()
    {
        try {
            Auth::logout();
            return true;
        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: logout", $error);
            return false;
        }
    }

    public function verifyEmail($token, $email)
    {
        try {
            DB::beginTransaction();
            $record = EmailVerify::where('token', $token)
                ->where('email', $email)->latest()->first();
            if ($record) {
                $user = User::where('email', $email)->first();
                $user->email_verified_at = now();
                $user->save();

                EmailVerify::where('email', $email)->delete();

                $response = [
                    'message' => 'Email has been verified!',
                ];
                DB::commit();
                return $response;
            }
            return intval(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']);
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: verifyEmail", $error);
            return false;
        }
    }
    
    
    public function resendOtpCode($id)
    {
        try {
            DB::beginTransaction();
            $otp = OTP::where('user_id', $id)->first();
            if ($otp) {
                $otp->otp_value = random_int(100000, 999999);
                $otp->save();

                $user = User::find($id);

                $account_sid = 'AC60d20bdd51da17c92e5dd29c9f22e521';
                $auth_token = 'bb3720d64d89358fe6915c168f5474d4';
                $twilio_number = '+13158478569';
                
                $receiverNumber = $user->phone_no;
                $message = 'this is your password reset verification code' . $otp->otp_value;
                $client = new Client($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number, 
                    'body' => $message]);

                $response = [
                    "message" => "six digit code send your number!",
                    "phone_number" => $user->phone_no
                ];
                $response = [
                    'message' => 'Email has been verified!',
                ];
                DB::commit();
                return $response;
            }
            return intval(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']);
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: resendOtpCode", $error);
            return false;
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    public function handleProviderCallback($provider)
    {
        try {
            $providerUser = Socialite::driver($provider)->user();
            return $user = $this->findOrCreateUser($providerUser, $provider);

//            $data = Auth::user()->toArray();
//            unset($data['roles']);
//
//            $data = [
//                'access_token' => $token,
//                'token_type' => 'bearer',
//                'expires_in' => $this->guard()->factory()->getTTL() * 60,
//                'user' => Auth::user()->only('id', 'username', 'email', 'phone_no', 'address', 'experience', 'cv_url', 'image_url', 'total_balance', 'absolute_cv_url', 'absolute_image_url'),
//                'roles' => $roles,
//                'settings' => Auth::user()->setting->only('user_id', 'private_account', 'secure_payment', 'sync_contact_no', 'app_notification', 'language')
//            ];
//
//            return $data;

            //return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], $data);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("AuthService: handleProviderCallback", $error);
            return false;
        }
    }

    public function findOrCreateUser($providerUser, $provider)
    {
        $account = SocialIdentity::whereProviderName($provider)
            ->whereProviderId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {
            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {

                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'password' => '$2y$10$zzp91bknlK3h3PPh3/xanuZFoE81aIsbn0THkGqZRm2RzCV8f082C',
                    'image_url' => $providerUser->avatar,
                    'user_verified_at' => Carbon::now(),
                ]);

                $admin_role = Role::findByName('user');
                $admin_role->users()->attach($user->id);

                $setting = new Setting();
                $setting->user_id = $user->id;
                $setting->private_account = 0;
                $setting->secure_payment = 1;
                $setting->sync_contact_no = 0;
                $setting->app_notification = 1;
                $setting->save();
            }

            $user->identities()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
            ]);

            return $user;
        }
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}

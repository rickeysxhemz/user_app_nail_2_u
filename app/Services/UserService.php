<?php

namespace App\Services;

use App\Events\JobPostEvent;
use App\Helper\Helper;
use App\Models\User;
use App\Models\UserPostedService;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Http\Traits\CommonTrait;

class UserService extends BaseService
{
    use CommonTrait;
    
    public function getProfileDetails()
    {
        try {
            $user = User::find(Auth::id());
            $data = [
                'username' => $user->username,
                'image_url' => $user->image_url,
                'phone_no' => $user->phone_no,
                'email' => $user->email,
                'address' => $user->address,
                'absolute_image_url' => $user->absolute_image_url
            ];
            return $data;
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("UserService: getProfileDetails", $error);
            return false;
        }
    }

    public function editProfile($request)
    {
        try {
            DB::begintransaction();
            $user = User::find(Auth::id());
            $request->has('username') ? ($user->username = $request->username) : null;
            $request->has('phone_no') ? ($user->phone_no = $request->phone_no) : null;
            $request->has('email') ? ($user->email = $request->email) : null;
            $request->has('password') ? ($user->password = Hash::make($request->password)) : null;
            $request->has('address') ? ($user->address = $request->address) : null;
            
            if (isset($request->image_url)) {
                $path = Helper::storeImageUrl($request, $user);
                $user->image_url = $path;
            }
            $user->save();
            DB::commit();
            return $user;
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("UserService: editProfile", $error);
            return false;
        }
    }

    public function registerAsArtist($request)
    {
        try {
            DB::beginTransaction();
            $artist = new User();
            $artist->username = $request->username;
            $artist->email = $request->email;
            $artist->password = Hash::make($request->password);
            $artist->phone_no = $request->phone_no;
            $artist->address = $request->address;
            $artist->experience = $request->experience;
            $artist->cv_url = Helper::storeCvUrl($request);
            $artist->image_url = 'storage/profileImages/default-profile-image.png';
            $artist->save();
            $artist->assignRole('artist');
            DB::commit();
            return $artist;
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("UserService: registerAsArtist", $error);
            return false;
        }
    }
    
    public function postYourService($request)
    {
        try {

            $services = DB::table('services')
                ->whereIn('id', $request->services_ids)
                ->pluck('id')->toArray();
             
            $services_ids_not_found = [];
            foreach (array_diff($request->services_ids, $services) as $value)
                $services_ids_not_found[] = $value;
            
            if (empty($services_ids_not_found) || count($services_ids_not_found) <= 0) {
                DB::beginTransaction();
                $user_posted_service = new UserPostedService;
                $user_posted_service->time = $request->time;
                $user_posted_service->date = $request->date;
                $user_posted_service->user_id = Auth::id();
                $user_posted_service->service_id = $request->service_id;
                $user_posted_service->price = $request->price;
                $user_posted_service->location = $request->location;
                $user_posted_service->additional_info = $request->additional_info;
                $user_posted_service->save();
                $user_posted_service->PostService()->attach($services);
                $message = [
                    'time' => $request->time,
                    'service' => $request->service,
                    'price' => $request->price,
                    'location' => $request->location,
                    'additional_info' => $request->additional_info
                ];
                event(new JobPostEvent($message));
                DB::commit();
                return $user_posted_service;
            }

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'], $services_ids_not_found);
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("User:UserService: postYourService", $error);
            return false;
        }
    }
    
    public function getPostYourService()
    {
        try {
            $user_posted_service = UserPostedService::where('user_id', Auth::id())->whereIn('status', ['active', 'deactive', 'accepted'])->get();
            return $user_posted_service;
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("UserService: getPostYourService", $error);
            return false;
        }
    }
    
    public function getAddresses()
    {
        try {
            $user = User::find(Auth::id());
            if ($user->address) {
                return unserialize($user->address);
            } else {
                return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
            }
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("Artist:UserService: getAddresses", $error);
            return false;
        }
    }
    
    public function saveAddress($request)
    {
        try {
            DB::beginTransaction();
            $addresses = [];
            $user = User::find(Auth::id());
            if ($user->address) {
                $addresses = unserialize($user->address);
                array_push($addresses, $request->address);
                $user->address = serialize($addresses);
                $user->save();
                DB::commit();
                return $user->address;
            } else {
                array_push($addresses, $request->address);
                $user->address = serialize($addresses);
                $user->save();
                DB::commit();
                return $user->address;
            }
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("Artist:UserService: saveAddress", $error);
            return false;
        }
    }
    
    public function jobStart($id)
    {
        try {
            $booking = Booking::find($id);
            // dd($booking);
            if ($booking) {
                $booking->status = 'in-process';
                $booking->save();
                
                $artist_name = User::find($booking->artist_id);
                $user = 'user';
                $title = 'Good news your artist start your job';
                $body = '$'.$booking->total_price.' job start for '. $artist_name->username;
                $booking_created = '2';
                $this->notifications($booking->client_id, $title, $body, $booking_created,  $user);

                $user_name = User::find($booking->client_id);
                $artist = 'artist';
                $title = 'Good news your job in progress';
                $body = $user_name->username.' allow you to start your job of $'. $booking->total_price;
                $booking_created = '2';
                $this->notifications($booking->artist_id, $title, $body, $booking_created,  $artist);
                
                return GlobalApiResponseCodeBook::RECORD_UPDATED['outcomeCode'];
            } else {
                return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
            }
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("Artist:UserService: getAddresses", $error);
            return false;
        }
    }
    
    public function jobEnd($id)
    {
        try {
            $booking = Booking::find($id);
            // dd($booking);
            if ($booking) {
                $booking->status = 'done';
                $booking->save();

                $transaction = Transaction::where('booking_id', $id)->first();
                
                if($transaction){
                    DB::beginTransaction();
                    $payment_exist = Payment::where('booking_id', $id)->first();
                    if(!$payment_exist){
                        $payment = new Payment();
                        $payment->artist_id = $transaction->receiver_id;
                        $payment->client_id = $transaction->sender_id;
                        $payment->booking_id = $transaction->booking_id;
                        $payment->transaction_id = $transaction->id;
                        $payment->amount = $booking->total_price;
                        $payment->status = 'pending';
                        $payment->save();
                    }

                    DB::commit();
                }
                $artist_name = User::find($booking->artist_id);
                $user = 'user';
                $title = 'Good news for you';
                $body = '$'.$booking->total_price.' your job completed by '. $artist_name->username;
                $booking_created = '3';
                $this->notifications($booking->client_id, $title, $body, $booking_created,  $user);

                $user_name = User::find($booking->client_id);
                $artist = 'artist';
                $title = 'Good news for you';
                $body = $user_name->username.' mark your job as compeleted you have earn $'. $booking->total_price;
                $booking_created = '3';
                $this->notifications($booking->artist_id, $title, $body, $booking_created,  $artist);
                
                return GlobalApiResponseCodeBook::RECORD_UPDATED['outcomeCode'];
            } else {
                return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
            }
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("User:UserService: jobEnd", $error);
            return false;
        }
    }
    
    public function delete()
    {
        try {
            DB::beginTransaction();
            $user = User::where('id', Auth::id())
                ->whereHas("roles", function ($q) {
                    $q->where("name", "user");
                })->first();
            // $user->roles()->detach();
            $user->delete();
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("UserService: delete", $error);
            return Helper::returnRecord(false, []);
        }
    }
}

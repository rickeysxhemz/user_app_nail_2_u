<?php

namespace App\Services;

use Exception;
use App\Helper\Helper;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\SchedulerBooking;
use App\Models\User;
use App\Models\UserPostedService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\CommonTrait;

class PaymentService extends BaseService
{
    use CommonTrait;
    
    public function getDetails()
    {
        try {
            $bookings = Booking::select('id','artist_id', 'client_id', 'total_price', 'status')
                ->with('Artist:id,username,cv_url,image_url',
                    'Transaction'    
                )
                ->where('client_id', Auth::id())
                ->whereIn('status', ['in-process', 'done'])
                ->orderByDesc('id')
                ->get();

            if ($bookings) {
                return $bookings;
            }
            return intval(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']);
        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("PaymentService: getDetails", $error);
            return false;
        }
    }

    public function getTotalEarning()
    {
        try {
            $data = [
                "total_earning" => Auth::user()->total_balance,
                "pending" => Auth::user()->transections()->with('Booking:id,service_id','Booking.service:id,name')->where('transaction_status',0)->orderBy('created_at', 'desc')->get(['id','booking_id','amount','created_at']),
                "completed" => Auth::user()->transections()->where('transaction_status',1)->orderBy('created_at', 'desc')->get(),
            ];

            return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], $data);

        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("PaymentService: getTotalEarning", $error);
            return false;
        }
    }
    
    public function sendPayments($request)
    {
        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.clover.com/pakms/apikey',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Authorization: Bearer 0c6ef57c-383c-a768-f60e-dca7255ec230'
            ),
            ));

            $response = json_decode(curl_exec($curl));
            
            curl_close($curl);


            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://token.clover.com/v1/tokens',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "card":{
                    "number":"'.$request->number.'",
                    "exp_month":"'.$request->exp_month.'",
                    "exp_year":"'.$request->exp_year.'",
                    "cvv":"'.$request->cvv.'",
                    "brand":"DISCOVER"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'apikey:'.$response->apiAccessKey,
                'Content-Type: application/json'
            ),
            ));

            $response_tokenize_card = json_decode(curl_exec($curl));
            if(isset($response_tokenize_card->message) && $response_tokenize_card->message){
                return Helper::returnRecord(GlobalApiResponseCodeBook::INVALID_FORM_INPUTS['outcomeCode'],  $response_tokenize_card->error->message);
            }
            
            curl_close($curl);
           
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://scl.clover.com/v1/charges',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "amount":'.$request->amount * 100 .',
                "currency":"usd",
                "source": "'.$response_tokenize_card->id.'"
            }',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer 0c6ef57c-383c-a768-f60e-dca7255ec230'
            ),
            ));

            $response_make_payment = json_decode(curl_exec($curl));
            
            if(isset($response_make_payment->message) && $response_make_payment->message){
                return Helper::returnRecord(GlobalApiResponseCodeBook::INVALID_FORM_INPUTS['outcomeCode'],  $response_make_payment->error->message);
            }
        
            curl_close($curl);

            if (isset($response_make_payment->captured) && $response_make_payment->captured ==  true) {
            
                DB::begintransaction();
                if(isset($request->booking_id) && $request->booking_id !== ''){
                    
                    $booking = Booking::find($request->booking_id);
                    $booking->status = 'new';
                    $booking->total_price = $request->amount;
                    $booking->save();
                    
                    $scheduler_booking = SchedulerBooking::where('booking_id', $request->booking_id)->first();
                    $scheduler_booking->status = 'book';
                    $scheduler_booking->save();

                    $transaction = new Transaction();
                    $transaction->sender_id = $booking->client_id;
                    $transaction->receiver_id = $booking->artist_id;
                    $transaction->payment_method_id = 3;
                    $transaction->booking_id = $booking->id;
                    $transaction->transaction_status = 1;
                    $transaction->save();
                    
                    
                    $artist_name = User::find($booking->artist_id);
                    $user = 'user';
                    $title = 'Your booking created successfully';
                    $body = '$'.$request->amount.' booking created for '. $artist_name->username;
                    $booking_created = '1';
                    $this->notifications($booking->client_id, $title, $body, $booking_created,  $user);

                    $user_name = User::find($booking->client_id);
                    $artist = 'artist';
                    $title = 'Good news you have get new booking';
                    $body = $user_name->username.' created new booking of $'. $request->amount;
                    $booking_created = '1';
                    $this->notifications($booking->artist_id, $title, $body, $booking_created,  $artist);

                    DB::commit();
                } else {
                    $user_post_services = UserPostedService::find($request->job_post_id);
                    $user_post_services->status = 'active';
                    $user_post_services->save();

                    $transaction = new Transaction();
                    $transaction->sender_id = Auth::id();
                    $transaction->payment_method_id = 3;
                    $transaction->transaction_status = 1;
                    $transaction->user_posted_service_id  = $request->job_post_id;
                    $transaction->save();
                    DB::commit();
                }
                $response_make_payment = '';
                return Helper::returnRecord(GlobalApiResponseCodeBook::SUCCESS['outcomeCode'], $response_make_payment);
            }

        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("PaymentService: getTotalEarning", $error);
            return false;
        }
    }
}

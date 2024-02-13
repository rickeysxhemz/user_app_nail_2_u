<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\SchedulerBooking;
use App\Models\BookingLocation;
use App\Models\Scheduler;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use App\Helper\Helper;
use Illuminate\Support\Facades\Auth;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Http\Traits\CommonTrait;

class BookingService extends BaseService
{
    use CommonTrait;
    public function all($request)
    {
        try {
            $booking = [];
            $bookings['current'] = Booking::with(['BookingLocation','BookingService', 'SchedulerBooking'])
                                ->where('client_id', Auth::id())
                                ->whereDate('created_at', Carbon::today())
                                ->where('status', 'new')
                                ->get();
            $bookings['previous'] = Booking::with(['BookingService', 'SchedulerBooking'])
                                    ->where('client_id', Auth::id())
                                    ->where('status', 'done')
                                    ->get();
            $bookings['cancel'] = Booking::with(['BookingService', 'SchedulerBooking'])
                                    ->where('client_id', Auth::id())
                                    ->where('status', 'cancel')
                                    ->get();
            $bookings['favourites'] = Auth::user()->FavouriteArtist;

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $bookings);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getJobHistory", $error);
            return Helper::returnRecord(false, []);
        }
    }
    
    public function bookingDetail($request)
    {
        try {
            $data = [];

            $client = User::with([
                                    'reviews',
                                    'jobs' => function ($q) {
                                        $q->where("status", "done");
                                    }
                                ])
                                // ->whereHas("roles", function ($q) {
                                //     $q->where("name", "artist");
                                // })
                                ->where('id', $request->client_id)
                                ->first(['id', 'username', 'image_url', 'cv_url', 'cover_image', 'address']);
                                        
            $bookings = Booking::with(['BookingLocation','BookingService', 'SchedulerBooking'])
                                ->where('id', $request->booking_id)
                                ->first();
                                
            if(strtotime($bookings->ended_at) > time()){
                $time_left =  strtotime($bookings->ended_at) -  time();
            } else {
                $time_left = 0;
            }                         

            // $profile = explode("https://user.nail2u.net",$client->image_url);
            $data['id'] = $client->id ? $client->id : '';
            $data['username'] = $client->username ? $client->username : '';
            $data['image_url'] =   env('COMMON_PATH').$client->image_url; 
            $data['cover_image'] =  env('COMMON_PATH').$client->cover_image ?   env('COMMON_PATH').$client->cover_image : '';
            $data['ratings'] = round($client->reviews->avg('rating'), 2);
            $data['service'] = $bookings->BookingService ? $bookings->BookingService : '';
            $data['location'] = $client->address ? $client->address : '';
            $data['booking_price'] = $bookings->total_price ? $bookings->total_price : 0;   
            $data['time_left'] = $time_left;                 
            

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $data);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getJobHistory", $error);
            return Helper::returnRecord(false, []);
        }
    }

    public function getAvailableArtistTime($request)
    {
        try {
            if($request->has('artist_id')){
                $all_times = [];
                $available = DB::table('schedulers')
                // ->whereNotExists(function ($query) use ($request) {
                //     $query->select(DB::raw(1))
                //         ->from('scheduler_bookings')
                //         ->where('scheduler_bookings.user_id', '=', $request->artist_id)
                //         ->where('scheduler_bookings.date', $request->date)
                //         ->where('scheduler_bookings.status', 'book')
                //         ->whereRaw('schedulers.id = scheduler_bookings.scheduler_id');
                // })
                ->select('id', 'time')
                ->get();

                foreach ($available as $time) {
                    $data['id'] = $time->id;
                    $data['time'] = $time->time;
                    $scheduler_bookings = SchedulerBooking::where('user_id', $request->artist_id)
                                                            ->where('date', $request->date)
                                                            ->where('scheduler_id', $time->id)
                                                            ->first();
                    if($scheduler_bookings){
                        $data['status'] = 'booked';
                    } else {
                        $data['status'] = 'unbooked';
                    }                                        
                    array_push($all_times, $data);
                };
                $available_time = $all_times;

            } else {
                $available_time = DB::table('schedulers')
                ->select('id', 'time')
                ->get();
            }

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $available_time);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getAvailableArtistTime", $error);
            return Helper::returnRecord(false, []);
        }
    }

    public function create($request)
    {
        // dd($request->services_ids);
        // $services = DB::table('services')->where('artist_id', $request->artist_id)
        try {

            $services = DB::table('services')
                ->whereIn('id', $request->services_ids)
                ->pluck('id')->toArray();

            $artist_services = DB::table('artist_services')
                ->whereIn('service_id', $request->services_ids)
                ->where('artist_id', $request->artist_id)
                ->pluck('price')->toArray();
    
            $services_ids_not_found = [];
            foreach (array_diff($request->services_ids, $services) as $value)
                $services_ids_not_found[] = $value;

            if (empty($services_ids_not_found) || count($services_ids_not_found) <= 0) {
                DB::beginTransaction();
                
                $scheduler_time = Scheduler::where('id', $request->schedule_time_id)->first();
                $date_array = explode("-", $request->date);
                $end_date_converted = $date_array[2].'-'.$date_array[0].'-'.$date_array[1];
                $scheduler_time = explode(" ", $scheduler_time->time);
                $end_date = $end_date_converted . ' ' . $scheduler_time[0];
                
                $booking = new Booking();
                $booking->artist_id = $request->artist_id;
                // $booking->status = 'new';
                $booking->client_id = Auth::id();
                $booking->ended_at = $end_date;
                $booking->started_at = $request->schedule_time_id;
                $booking->save();
                $booking->BookingService()->attach($services);

                $scheduler_booking = new SchedulerBooking();
                $scheduler_booking->scheduler_id = $request->schedule_time_id;
                $scheduler_booking->user_id = $request->artist_id;
                $scheduler_booking->booking_id = $booking->id;
                $scheduler_booking->date = $request->date;
                $scheduler_booking->save();
                
                
                $booking_location = new BookingLocation();
                $booking_location->booking_id = $booking->id;
                $booking_location->user_longitude = $request->longitude;
                $booking_location->user_latitude = $request->latitude;
                $booking_location->status = 'standby';
                $booking_location->save();

                if($artist_services){
                    foreach ($artist_services as $key => $artist_service) {
                        $update_services = DB::table('booking_services')
                                                ->where('booking_id', $booking->id)
                                                ->update(['price' => $artist_service]);
                    }
                }
                DB::commit();

                return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_CREATED['outcomeCode'], ['booking_id' => $booking->id]);
            }
             
            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'], $services_ids_not_found);
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getJobHistory", $error);
            return Helper::returnRecord(false, []);
        }
    }
    public function cancelBooking($id)
    {
        try {
            DB::beginTransaction();
            $booking = Booking::where('id', $id)->first();
            $booking->status = 'cancel';
            $booking->save();

            $scheduler_booking = SchedulerBooking::where('booking_id', $id)->first();
            $scheduler_booking->status = 'cancel';
            $scheduler_booking->save();

            
            $booking_location = BookingLocation::where('booking_id', $id)->first();
            $booking_location->status = 'standby';
            $booking_location->save();

            $transaction=Transaction::where('booking_id', $id)->first();
            $transaction->transaction_status = 2;
            $transaction->save();
            DB::commit();

            $artist="artist";
            $bookingCancel="Booking Cancel !";
            $body="Booking Cancelled Successfully.At Nails2U, your convenience , our top priority";
            $booking_cancel="1";
            $this->notifications($booking->artist_id, $bookingCancel, $body, $booking_cancel, $artist);

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_CREATED['outcomeCode'], ['booking_id' => $booking->id]);
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getJobHistory", $error);
            return Helper::returnRecord(false, []);
        }
    }
    public function getAvailableSlots($request)
    {
        try {
            $available_slots=[];
            $booking_id=$request->booking_id;
             $available_time =Scheduler::whereNotIn('id', function($query) use ($request) {
                $query->select('scheduler_id')
                    ->from('scheduler_bookings')
                    ->where('user_id', $request->artist_id)
                    ->where('date', $request->date)
                    ->whereIn('status', ['book']);
            })->get();
            $available_slots['booking_id']=$booking_id;
            $available_slots['available_time']=$available_time;

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $available_slots);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getAvailableArtistTime", $error);
            return Helper::returnRecord(false, []);
        }
    }
    public function updateSchedular($request)
    {
        try {
            $scheduler_booking = SchedulerBooking::where('booking_id',$request->booking_id)->first();
            $scheduler_booking->scheduler_id = $request->schedule_time_id;
            $scheduler_booking->date = $request->date;
            $scheduler_booking->save();

            $booking = Booking::where('id', $request->booking_id)->first();
            $booking->started_at = $request->schedule_time_id;

            $scheduler_time = Scheduler::where('id', $request->schedule_time_id)->first();
            $date_array = explode("-", $request->date);
            $end_date_converted = $date_array[2].'-'.$date_array[0].'-'.$date_array[1];
            $scheduler_time = explode(" ", $scheduler_time->time);
            $end_date = $end_date_converted . ' ' . $scheduler_time[0];
            $booking->ended_at = $end_date;
            $booking->save();

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $scheduler_booking);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getAvailableArtistTime", $error);
            return Helper::returnRecord(false, []);
        }
    }
}

<?php

namespace App\Services;

use Exception;
use App\Helper\Helper;
use App\Models\User;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserPostedService;

class DashboardService extends BaseService
{
    public function getAllArtists()
    { 
        try {
            $artist_data = [];
            $artists = User::with([
                'reviews',
                'jobs' => function ($q) {
                    $q->where("status", "done");
                }    
                // },
                // 'services'
            ])
                ->whereHas("roles", function ($q) {
                    $q->where("name", "artist");
                })
                ->whereNotNull('phone_verified_at')
                ->orderby('id', 'desc')
                ->get(['id', 'username', 'image_url', 'cv_url', 'cover_image']);
                
            if ($artists) {
                foreach ($artists as $artist) {
                    $data['id'] = $artist->id;
                    $data['username'] = $artist->username;
                    $data['image_url'] = 'https://artist.nail2u.net/'.$artist->image_url;
                    $data['cover_image'] = 'https://artist.nail2u.net/'.$artist->cover_image;
                    $data['ratings'] = round($artist->reviews->avg('rating'), 1);
                    $data['jobs_done'] = count($artist->jobs);
                    $status = 0;
                    $favourite_status = DB::table('favourite_artist')->where('artist_id', $artist->id)->where('user_id', Auth::id())->first();
                    if($favourite_status){
                        $status = 1;
                    }
                    $data['favourite'] = $status;
                    $data['expert'] = '';
                    $data['service_price'] = DB::table('artist_services')->where('artist_id', $artist->id)->sum('price');
                    // $artist_services = $artist->services->pluck('id')->toArray();

                    // if (count($artist_services) > 0) {
                    //     for ($i = 0; $i < count($artist->services->toArray()); $i++) {
                    //         $verify = [];
                    //         $count = DB::table('booking_services')->where('service_id', $artist_services[$i])->count();
                    //         $verify[$artist_services[$i]] = $count;
                    //         $maxs = array_keys($verify, max($verify));
                    //     }

                    //     $service = Service::find($maxs[0]);
                    //     $data['expert'] = $service->name;
                    //     $data['service_price'] = $service->price;
                    // }
                    array_push($artist_data, $data);
                }
                return $artist_data;
            }
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("DashboardService: getAllArtists", $error);
            return false;
        }
    }
    
    public function getArtist($id)
    {
        try {
            $data = [];
            $artist_data = [];
            $suggested_artists = User::with([
                'reviews',
                'ArtistService',
                'portfolio',
                'jobs' => function ($q) {
                    $q->where("status", "done");
                }    
                // },
                // 'services'
            ])
                ->whereHas("roles", function ($q) {
                    $q->where("name", "artist");
                })
                ->where('id', $id)
                ->orderby('id', 'desc')
                ->get(['id', 'username', 'image_url', 'cv_url', 'cover_image', 'created_at']);
                // return $suggested_artists;
            if ($suggested_artists) {
                foreach ($suggested_artists as $artist) {
                    $profile = explode("https://user.nail2u.net",$artist->absolute_image_url);
                    $data['id'] = $artist->id;
                    $data['username'] = $artist->username;
                    $data['image_url'] =  'https://artist.nail2u.net'.$profile[1];
                    $data['cover_image'] = 'https://artist.nail2u.net/'.$artist->cover_image;
                    $data['ratings'] = round($artist->reviews->avg('rating'), 1);
                    $data['jobs_done'] = count($artist->jobs);
                    $data['since_join'] = date("Y", strtotime($artist->created_at));
                    $data['service'] = $artist->ArtistService;
                    $data['expert'] = '';
                    $data['service_price'] = DB::table('artist_services')->where('artist_id', $artist->id)->sum('price');
                    $data['portfolio'] = $artist->portfolio;
                   
                    array_push($artist_data, $data);

                    $artist_data = collect($artist_data)->sortBy('jobs_done')->reverse()->toArray();
                    $artist_data = array_slice($artist_data, 0, 15);
                }
                return $artist_data;
            }
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("DashboardService: getArtist", $error);
            return false;
        }
    }

    public function getSuggestedArtists()
    {
        try {
            $data = [];
            $artist_data = [];
            $suggested_artists = User::with([
                'reviews',
                'jobs' => function ($q) {
                    $q->where("status", "done");
                }    
                // },
                // 'services'
            ])
                ->whereHas("roles", function ($q) {
                    $q->where("name", "artist");
                })
                ->whereNotNull('phone_verified_at')
                ->orderby('id', 'desc')
                ->get(['id', 'username', 'image_url', 'cv_url', 'cover_image']);
            if ($suggested_artists) {
                foreach ($suggested_artists as $artist) {
                    $data['id'] = $artist->id;
                    $data['username'] = $artist->username;
                    $data['image_url'] = $artist->absolute_image_url;
                    $data['cover_image'] = $artist->cover_image;
                    $data['ratings'] = round($artist->reviews->avg('rating'), 1);
                    $data['jobs_done'] = count($artist->jobs);
                    $status = 0;
                    $favourite_status = DB::table('favourite_artist')->where('artist_id', $artist->id)->where('user_id', Auth::id())->first();
                    if($favourite_status){
                        $status = 1;
                    }
                    $data['favourite'] = $status;
                    $data['expert'] = '';
                    $data['service_price'] = DB::table('artist_services')->where('artist_id', $artist->id)->sum('price');
                    // $artist_services = $artist->services->pluck('id')->toArray();

                    // if (count($artist_services) > 0) {
                    //     for ($i = 0; $i < count($artist->services->toArray()); $i++) {
                    //         $verify = [];
                    //         $count = DB::table('booking_services')->where('service_id', $artist_services[$i])->count();
                    //         $verify[$artist_services[$i]] = $count;
                    //         $maxs = array_keys($verify, max($verify));
                    //     }

                    //     $service = Service::find($maxs[0]);
                    //     $data['expert'] = $service->name;
                    //     $data['service_price'] = $service->price;
                    // }
                    array_push($artist_data, $data);

                    $artist_data = collect($artist_data)->sortBy('jobs_done')->reverse()->toArray();
                    $artist_data = array_slice($artist_data, 0, 15);
                }
                return $artist_data;
            }
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("DashboardService: getSuggestedArtists", $error);
            return false;
        }
    }

    public function getNewArtists()
    {
        try {
            $artist_data = [];
            $new_artists = User::with([
                'reviews',
                'jobs' => function ($q) {
                    $q->where("status", "done");
                }
            ])
                ->whereHas("roles", function ($q) {
                    $q->where("name", "artist");
                })
                ->whereNotNull('phone_verified_at')
                ->limit(15)
                ->latest()
                ->get(['id', 'username', 'image_url', 'cv_url']);

            if ($new_artists) {
                foreach ($new_artists as $artist) {
                    $data['id'] = $artist->id;
                    $data['username'] = $artist->username;
                    $data['image_url'] = $artist->absolute_image_url;
                    $data['ratings'] = round($artist->reviews->avg('rating'), 1);
                    $data['jobs_done'] = count($artist->jobs);
                    $status = 0;
                    $favourite_status = DB::table('favourite_artist')->where('artist_id', $artist->id)->where('user_id', Auth::id())->first();
                    if($favourite_status){
                        $status = 1;
                    }
                    $data['favourite'] = $status;
                    $data['expert'] = '';
                    $data['service_price'] = '';
                    // $artist_services = $artist->services->pluck('id')->toArray();
                    
                    // if (count($artist_services) > 0) {
                    //     for ($i = 0; $i < count($artist->services->toArray()); $i++) {
                    //         $verify = [];
                    //         $count = DB::table('booking_services')->where('service_id', $artist_services[$i])->count();
                    //         $verify[$artist_services[$i]] = $count;
                    //         $maxs = array_keys($verify, max($verify));
                    //     }

                    //     $service = Service::find($maxs[0]);
                    //     $data['expert'] = $service->name;
                    //     $data['service_price'] = $service->price;
                    // }
                    array_push($artist_data, $data);
                }
                return $artist_data;
            }
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("DashboardService: getNewArtists", $error);
            return false;
        }
    }

    public function getArtistPortfolio($request)
    {
        // try {
            $artist_portfolio = User::with([
                "portfolio:id,artist_id,title,image_url",
                "ArtistService:id,name",
                "reviews:id,artist_id,rating",
                "jobs" => function ($q) {
                    $q->where("status", "done");
                }
            ])
                ->whereHas("roles", function ($q) {
                    $q->where("name", "artist");
                })
                ->where('id', $request->artist_id)
                ->first(['id', 'username', 'cv_url', 'image_url', 'created_at as working_since']);

            if ($artist_portfolio) {

                $data = [
                    'rating' => round($artist_portfolio->reviews->avg('rating'), 1),
                    'jobs_done' => count($artist_portfolio->jobs),
                    'data' => $artist_portfolio
                ];

                return $data;
            }
        //     return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        // } catch (Exception $e) {
        //     $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
        //     Helper::errorLogs("DashboardService: getArtistPortfolio", $error);
        //     return false;
        // }
    }

    public function getArtistReviews($request)
    {
        try {
            $reviews = [];
            $record = User::with([
                'reviews'
                // 'services:id,artist_id,name'
            ])
                ->where('id', $request->artist_id)->first();

            foreach ($record->reviews as $review) {
                $client = User::where('id', $review->client_id)->first();
                $data['client_name'] = $client->username;
                $data['date'] = $review['created_at'];
                $data['review'] = $review->review;
                $data['rating'] = $review->rating;
                array_push($reviews, $data);
            };

            $response = [
                'username' => $record->username,
                'image_url' => $record->absolute_image_url,
                'rating' => round($record->reviews->avg('rating'), 1),
                'reviews' => $reviews,
                // 'services' =>  $record->services
                'services' =>  ''
            ];
            return $response;
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("DashboardService: getArtistReviews", $error);
            return false;
        }
    }
    
    public function trackBooking($request)
    {
        try {
            $booking = Booking::select('id', 'artist_id', 'client_id', 'started_at', 'total_price', 'status')
                ->with([
                    'BookingService:id,name',
                    'Artist:id,username,image_url,cv_url,cover_image',
                    'Client:id,address,cv_url,image_url',
                    'Schedule:id,time',
                    'BookingLocation'
                ])
                ->where('id', $request->booking_id)
                ->first();
            if ($booking) {
                return $booking;
            }
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("DashboardService: trackBooking", $error);
            return false;
        }
    }
    
    public function getTrackBooking()
    {
        
        try {
            $booking = Booking::select('id', 'artist_id', 'client_id', 'started_at', 'total_price', 'created_at', 'status')
                ->with([
                    'BookingService:id,name',
                    'Artist:id,username,image_url,cv_url,cover_image',
                    'Client:id,address,cv_url,image_url',
                    'Schedule:id,time',
                    'BookingLocation'
                ])
                ->where('client_id', Auth::id())
                ->where('status', 'new')
                ->first();
            
            $job_posts = UserPostedService::select('id','user_id', 'date', 'time', 'price', 'location', 'created_at')
                        ->with([
                            'Client:id,username,address,cv_url,image_url',
                            'PostService:id,name'
                        ])
                        ->where('user_id', Auth::id())
                        ->first();
            if(isset($booking->created_at) ){
                if(isset($job_posts->created_at)) {
                    if ($booking->created_at > $job_posts->created_at) {
                        return $booking;
                    } else {
                        return $job_posts;
                    }
                } else {
                    return $booking;
                }
                
            } elseif(isset($job_posts->created_at)){
                return $job_posts;
            }
        
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("DashboardService: trackBooking", $error);
            return false;
        }
    }
    
    public function deviceToken($request)
    {
        try {
            DB::beginTransaction();
            $update_token = User::where('id', Auth::id())->first();
            if ($update_token) {
                $update_token->device_token = $request->device_token;
                $update_token->save();
                DB::commit();
                return GlobalApiResponseCodeBook::RECORD_UPDATED['outcomeCode'];
            } 
            return GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'];
        } catch (Exception $e) {
            DB::rollBack();
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("Artist:UserService: devicetoken", $error);
            return false;
        }
    }
}

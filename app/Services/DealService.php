<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Deal;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use App\Helper\Helper;
use Illuminate\Support\Facades\Auth;
use App\Libs\Response\GlobalApiResponseCodeBook;
use Illuminate\Support\Facades\DB;

class DealService extends BaseService
{
    public function all()
    {
        try {
            // $deals = Deal::with('Services', 'Artist:id,username,cv_url,image_url')->get()->toArray();
            
            $deals = Deal::with('DealServices')
                    ->where('status', 'active')
                    ->get()->toArray();

                
            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $deals);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getJobHistory", $error);
            return Helper::returnRecord(false, []);
        }
    }
    public function getDealArtist($id)
    {
        try{
            $artist_data = [];

            $deal_services = Deal::with('DealServices')
                    ->where('status', 'active')
                    ->where('id', $id)
                    ->get()->toArray();
            $services = $deal_services[0]['deal_services'];
            
            $total_amount = 0;
            if (count($services) > 0) {
                foreach ($services as $key => $service) {
                    $total_amount = $total_amount + $service['amount'];
                }
            }
         
            $discount_price = $total_amount - ($total_amount * ($deal_services[0]['discount_percentage'] / 100));
            
            $artists = User::whereExists(function ($query) use ($id) {
                $query->select(DB::raw(1))
                    ->from('artist_deals')
                    ->where('artist_deals.deal_id', '=', $id)
                    ->whereRaw('users.id = artist_deals.user_id');
            })
            ->select('id','username', 'image_url', 'cv_url', 'cover_image')
            ->with(['reviews'])
            ->get();

            if ($artists) {
                foreach ($artists as $artist) {
                    $data['id'] = $artist->id;
                    $data['username'] = $artist->username;
                    $data['image_url'] = $artist->absolute_image_url;
                    $data['cover_image'] = 'https://artist.nail2u.net/'.$artist->cover_image;
                    $data['ratings'] = round($artist->reviews->avg('rating'), 1);
                    $data['reviews'] = $artist->reviews;
                    
                    array_push($artist_data, $data);
                }
            }    
            $data = [
                'total_price' => $total_amount,
                'discount_price' => (int)$discount_price,
                'deal' => $deal_services,
                'artists' => $artist_data
            ];


            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $data);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("BookingService: getJobHistory", $error);
            return Helper::returnRecord(false, []);
        }
    }
    
    public function getDealService($id)
    {
        try{
            $deal_service_data = [];

            $deal_services = Deal::with('DealServices')
            ->where('status', 'active')
            ->where('id', $id)
            ->get()->toArray();

            if($deal_services)
            {
                $services = $deal_services[0]['deal_services'];
                
                $total_price = 0;
                foreach ($services as $key => $service) {
                    $total_price = $total_price  + $service['amount']; 
                }
                $discount_price = $total_price - ($total_price * ($deal_services[0]['discount_percentage'] / 100));

                
                $data['total_price'] = $total_price;
                $data['discount_price'] = (int)$discount_price;
                $data['services'] = $deal_services[0]['deal_services'];
            
                array_push($deal_service_data, $data);
            }
            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORDS_FOUND['outcomeCode'], $deal_service_data);
        } catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("DealService: getDealServices", $error);
            return Helper::returnRecord(false, []);
        }  
    }
}
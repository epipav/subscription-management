<?php
namespace App\Http\Controllers;
use App\Models\Subscription;
use App\Models\Device;
use Illuminate\Support\Facades\Http;

use Carbon\Carbon;


class SubscriptionUpdateController
{
    public static function sayMo(){
        $now = new Carbon;
        $subsToUpdate = Subscription::join('devices', 'devices.id', '=', 'subscriptions.deviceId')
                           ->select('devices.uid','devices.appId','devices.os','subscriptions.*')        
                           ->where('status','True')
                           ->where('expire_date', '<=', $now)
                           ->orWhere(function ($query) {
                            $query->where('status', '=', null)
                                  ->where('expire_date', '=', null);
                            })
                           ->get();


        foreach ($subsToUpdate as $sub){

        
            $url = 'http://subscription-management-mock-api:8200/api/mock/' . strtolower($sub->os); 

            $thirdPartyResponse = Http::post($url, [
                'receipt' => $sub->receipt
            ]);

            if(!$thirdPartyResponse->clientError()){
                //rate limited, add to db anyways for further processing from worker.
                //$responseObj = ['receipt'=>$sub->receipt, 'status'=> $thirdPartyResponse['status']];

                $expireDateNormalized = null;
                //handle timezone difference. We are storing dates in UTC~(GMT), so add 6 hours.
                if( !is_null($thirdPartyResponse['expire-date'])){
                    $expireDateNormalized = date("Y-m-d H:i:s", (strtotime($thirdPartyResponse['expire-date']) - (6*60*60)));
                }
    
    
                $match = ['deviceId' => $sub->deviceId, 'appId' => $sub->appId];
                    
                $sub->status = $thirdPartyResponse['status'] ? "True" :"False";
                $sub->expire_date = $expireDateNormalized;
                $sub->save();

            }



            
            
            

        }   
        return "[" . count($subsToUpdate) . "] subscriptions are updated.";
    }
}
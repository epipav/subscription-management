<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
use App\Models\Device;
use App\Models\Subscription;
use \Firebase\JWT\JWT;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'uid' => 'required',
            'appId' => 'required',
            'language' => 'required',
            'os' => 'required'

        ]);
        
        if ($validator->fails()) {
            return response(['message' => $validator->getMessageBag()->first()]);
        }
       
        $requestArray = $request->all();

        try{
            $device = Device::firstOrCreate(['uid'=>$requestArray['uid'],
                                            'appId'=>$requestArray['appId'],
                                            'language'=>$requestArray['language'],
                                            'os'=>$requestArray['os']]);

            //1 day expire time.
            $expiryTimestamp = time() + (24*60*60);
            $device["exp"] = $expiryTimestamp;
            $jwt = JWT::encode($device, env('JWT_SECRET'));
            //$jwt_decoded = JWT::decode($jwt, env('JWT_SECRET'), array('HS256'));

        }
        catch (\Illuminate\Database\QueryException $e) {

            if ($e->getCode() !== 01000) {
                return response(['message' => 'OS field can only be Google or iOS.'],400);  
            }
            return response(['message' => 'Malformed request, please check all fields.'],400);

        }

        return array("client_token"=>$jwt);
    }

    public function show_event(Request $request){
        $requestArray = $request->all();        
        Log::channel('stderr')->notice("Event recieved: ", ['body' => $requestArray]);
        return response(['message'=>'Got Event!'],200);


    }

    public function purchase(Request $request)
    {
        //
        //$devices = DB::table('devices')->get();
        //$request_decoded = json_decode($request->all());

        $requestArray = $request->all();
       
        $url = 'http://subscription-management-mock-api:8200/api/mock/' . strtolower($request['device']->os); 

        $thirdPartyResponse = Http::post($url, [
            'receipt' => $requestArray['receipt']
        ]);
        if($thirdPartyResponse->clientError()){
            //rate limited, add to db anyways for further processing from worker.
            $match = ['deviceId' => $requestArray['device']->id, 'appId' => $requestArray['device']->appId];
            $subscription = Subscription::updateOrCreate($match,['receipt' => $requestArray['receipt'],
                                                         'status' => null,
                                                         'expire_date' => null                                                    
                                                         ]);
            return response(['receipt' => $requestArray['receipt'],'message'=>$thirdPartyResponse['message']],$thirdPartyResponse->getStatusCode());
        }

        $responseObj = ['receipt'=>$requestArray['receipt'], 'status'=> $thirdPartyResponse['status']];

        $expireDateNormalized = null;
        //handle timezone difference. We are storing dates in UTC~(GMT), so add 6 hours.
        if( !is_null($thirdPartyResponse['expire-date'])){
            $expireDateNormalized = date("Y-m-d H:i:s", (strtotime($thirdPartyResponse['expire-date']) - (6*60*60)));
            $responseObj['expire-date'] = $expireDateNormalized;
        }


        $match = ['deviceId' => $requestArray['device']->id, 'appId' => $requestArray['device']->appId];
        $subscription = Subscription::updateOrCreate($match,['receipt' => $requestArray['receipt'],
                                                         'status' => $thirdPartyResponse['status'] ? "True" :"False",
                                                         'expire_date' => $expireDateNormalized,                                                    
                                                         ]);
        
        
       

        //var_dump($response);

        return $responseObj;
    }

    public function check_subscription(Request $request)
    {
        //
        //$devices = DB::table('devices')->get();
        //$request_decoded = json_decode($request->all());

        $requestArray = $request->all();

        $conditions = ['deviceId' => $requestArray['device']->id, 'appId' => $requestArray['device']->appId];
        $subs = Subscription::where($conditions)->get();

        if($subs !== null && count($subs) > 0 ){
            return response($subs[0],200);
        }

        return response(["message"=>"Subscription for given device doesn't exist."],404);
    }


}

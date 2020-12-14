<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;

class MockApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $requestArray = $request->all();
        $status = true;
        

        if( substr($requestArray["receipt"], -2) % 6 === 0){
            return response(['message' => "Rate limited."], 429);
        }
        else if ( substr($requestArray["receipt"], -1) % 2 === 0){
            $status = false;
            return response(['status' => $status, 'expire-date'=>null], 200);
        }
        //get utc-6 date, and add random 1-12 hours for mocking real expiry
        $expiryTimestamp = time() + (6*60*60);
        $expiryTimestamp += (rand(1,12) * (60*60));

        return ['status' => $status, 'expire-date'=> date("Y-m-d H:i:s",$expiryTimestamp)];

    }


}

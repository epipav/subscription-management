<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use \Firebase\JWT\JWT;


class SubscriptionManagementTest extends TestCase
{
    
    use RefreshDatabase;
   
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_api_register()
    {
        $device = Device::factory()->make();

        //var_dump($device->id);
        $response = $this->call('POST','http://subscription-management:8000/api/register', ['uid'=> $device->uid,
                                                         'appId' => $device->appId,
                                                         'language' => $device->language,
                                                         'os' => $device->os
                                                         ]);
        
        //check if db is populated
        $this->assertDatabaseHas('devices', ['uid'=> $device->uid,
        'appId' => $device->appId,
        'language' => $device->language,
        'os' => $device->os
        ]);

        $responseArray = json_decode($response->getContent());

        $jwt_decoded = JWT::decode($responseArray->client_token, env('JWT_SECRET'), array('HS256'));

        $this->assertEquals($jwt_decoded->uid, $device->uid);
        $this->assertEquals($jwt_decoded->appId, $device->appId);
        $this->assertEquals($jwt_decoded->language, $device->language);
        $this->assertEquals($jwt_decoded->os, $device->os);
             
    }


    public function test_api_purchase_success(){

        $device = Device::factory()->make();

        //register first
        $responseRegister = $this->call('POST','http://subscription-management:8000/api/register', ['uid'=> $device->uid,
                                                         'appId' => $device->appId,
                                                         'language' => $device->language,
                                                         'os' => $device->os
                                                         ]);


        //make purchase, odd number receipt for success
        $receipt = "101";
        $responseRegisterArray = json_decode($responseRegister->getContent()); 

        $responsePurchase = $this->call('POST','http://subscription-management:8000/api/purchase', [
        'client-token'=> $responseRegisterArray->client_token,
        'receipt' => $receipt,
        ]);
        
    
        $purchaseContent = json_decode($responsePurchase->getContent());
        $this->assertEquals($responsePurchase->status(),200);
        $this->assertEquals($purchaseContent->receipt, $receipt);
        $this->assertTrue($purchaseContent->status);
        $responsePurchase->assertJsonStructure(['status','receipt','expire-date']);


        //decode jwt to check db fields.
        $jwt_decoded = JWT::decode($responseRegisterArray->client_token, env('JWT_SECRET'), array('HS256'));
        //var_dump($purchaseContent);
        //exit;
        //check db
        $this->assertDatabaseHas('subscriptions', [
        'deviceId'=> $jwt_decoded->id,
        'appId' => $jwt_decoded->appId,
        'receipt' => $receipt,
        'status' => "True",
        'expire_date' => $purchaseContent->{'expire-date'}
        ]);
    }

    public function test_api_purchase_failure(){
        $device = Device::factory()->make();

        //register first
        $responseRegister = $this->call('POST','http://subscription-management:8000/api/register', ['uid'=> $device->uid,
                                                         'appId' => $device->appId,
                                                         'language' => $device->language,
                                                         'os' => $device->os
                                                         ]);


        //make purchase, even number, but last 2 digits shouldn't be divisible by 6.
        $receipt = "102";
        $responseRegisterArray = json_decode($responseRegister->getContent()); 

        $responsePurchase = $this->call('POST','http://subscription-management:8000/api/purchase', [
        'client-token'=> $responseRegisterArray->client_token,
        'receipt' => $receipt,
        ]);
        
        $purchaseContent = json_decode($responsePurchase->getContent());
        $this->assertEquals($responsePurchase->status(),200);
        $this->assertEquals($purchaseContent->receipt, $receipt);
        $this->assertFalse($purchaseContent->status);
        $responsePurchase->assertJsonStructure(['status','receipt']);

        $jwt_decoded = JWT::decode($responseRegisterArray->client_token, env('JWT_SECRET'), array('HS256'));

        $this->assertDatabaseHas('subscriptions', [
            'deviceId'=> $jwt_decoded->id,
            'appId' => $jwt_decoded->appId,
            'receipt' => $receipt,
            'status' => "False"
            ]);
    }

    public function test_api_purchase_rate_limited(){
        $device = Device::factory()->make();

        //register first
        $responseRegister = $this->call('POST','http://subscription-management:8000/api/register', ['uid'=> $device->uid,
                                                         'appId' => $device->appId,
                                                         'language' => $device->language,
                                                         'os' => $device->os
                                                         ]);


        //If last 2 digits are divisible by 6, it should throw rate limit.
        $receipt = "100";
        $responseRegisterArray = json_decode($responseRegister->getContent()); 

        $responsePurchase = $this->call('POST','http://subscription-management:8000/api/purchase', [
        'client-token'=> $responseRegisterArray->client_token,
        'receipt' => $receipt,
        ]);
        
        $purchaseContent = json_decode($responsePurchase->getContent());
        $this->assertEquals($responsePurchase->status(),429);
        $this->assertEquals($purchaseContent->message, "Rate limited.");
        $responsePurchase->assertJsonStructure(['receipt','message']);

        $jwt_decoded = JWT::decode($responseRegisterArray->client_token, env('JWT_SECRET'), array('HS256'));

        $this->assertDatabaseHas('subscriptions', [
            'deviceId'=> $jwt_decoded->id,
            'appId' => $jwt_decoded->appId,
            'receipt' => $receipt,
            'status' => null,
            'expire_date' => null
            ]);
    }

    public function test_api_check_subs_exist(){
        $device = Device::factory()->make();

        //register first
        $responseRegister = $this->call('POST','http://subscription-management:8000/api/register', ['uid'=> $device->uid,
                                                         'appId' => $device->appId,
                                                         'language' => $device->language,
                                                         'os' => $device->os
                                                         ]);


        //If last 2 digits are divisible by 6, it should throw rate limit.
        $receipt = "101";
        $responseRegisterArray = json_decode($responseRegister->getContent()); 

        $responsePurchase = $this->call('POST','http://subscription-management:8000/api/purchase', [
        'client-token'=> $responseRegisterArray->client_token,
        'receipt' => $receipt,
        ]);
        
        $responseCheckSubs = $this->call('POST','http://subscription-management:8000/api/check_subscription', [
            'client-token'=> $responseRegisterArray->client_token
        ]);
        
        
        $checkSubsContent = json_decode($responseCheckSubs->getContent());
        $jwt_decoded = JWT::decode($responseRegisterArray->client_token, env('JWT_SECRET'), array('HS256'));
        

        $this->assertEquals($responseCheckSubs->status(),200);
        $this->assertEquals($checkSubsContent->deviceId, $jwt_decoded->id);
        $this->assertEquals($checkSubsContent->appId, $jwt_decoded->appId);
        $this->assertEquals($checkSubsContent->receipt, $receipt);
        $this->assertEquals($checkSubsContent->status, "True");

    }

    public function test_api_check_subs_not_exist(){
        $device = Device::factory()->make();

        //register first
        $responseRegister = $this->call('POST','http://subscription-management:8000/api/register', ['uid'=> $device->uid,
                                                         'appId' => $device->appId,
                                                         'language' => $device->language,
                                                         'os' => $device->os
                                                         ]);


        //If last 2 digits are divisible by 6, it should throw rate limit.
        $receipt = "101";
        $responseRegisterArray = json_decode($responseRegister->getContent()); 
        
        $responseCheckSubs = $this->call('POST','http://subscription-management:8000/api/check_subscription', [
            'client-token'=> $responseRegisterArray->client_token
        ]);
        
        
        $checkSubsContent = json_decode($responseCheckSubs->getContent());
        $jwt_decoded = JWT::decode($responseRegisterArray->client_token, env('JWT_SECRET'), array('HS256'));
        

        $this->assertEquals($responseCheckSubs->status(),404);
        $this->assertEquals($checkSubsContent->message, "Subscription for given device doesn't exist.");

    }


}

<?php

namespace App\Observers;

use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use App\Models\Webhook;
use Illuminate\Support\Facades\Http;




class SubscriptionObserver
{
    /**
     * Handle the Subscription "created" event.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return void
     */
    public function created(Subscription $subscription)
    {
        $this->sendWebhook($subscription->appId, $subscription->deviceId, 'Started');
    }

    /**
     * Handle the Subscription "updated" event.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return void
     */
    public function updated(Subscription $subscription)
    {
        if($subscription->status == "False"){
            $this->sendWebhook($subscription->appId, $subscription->deviceId, 'Cancelled');
        }
        else{
            $this->sendWebhook($subscription->appId, $subscription->deviceId, 'Renewed');
        }

    }

    private function sendWebhook($appId, $deviceId, $event){

        $webhook = Webhook::all()->first();
        Log::channel('stderr')->info("webhook: " . $webhook->url);
        $webhook = Http::post($webhook->url, [
            'deviceId' => $deviceId,
            'appId' => $appId,
            'event' => $event
        ]);
    }

    /**
     * Handle the Subscription "deleted" event.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return void
     */
    public function deleted(Subscription $subscription)
    {
    }

}

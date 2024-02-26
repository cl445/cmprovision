<?php

namespace App\Listeners;

use App\Events\CmProvisioningComplete;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CmProvisioningCompleteListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CmProvisioningComplete $event)
    {
        $serviceUrls = Config::get('services.external_services');

        foreach($serviceUrls as $serviceName => $url) {
            // Verify if URL is set and valid
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                Log::warning("The URL for $serviceName is not set or not valid.");
                continue;
            }

            // Call each external service with POST request
            try {
                $response = Http::post($url, $event->cm->toArray());

                // Log the response from each service
                Log::info("$serviceName response: " . $response->body());

            } catch (\Exception $e) {
                Log::error("$serviceName failed: " . $e->getMessage());
            }
        }
    }
}

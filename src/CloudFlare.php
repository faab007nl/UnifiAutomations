<?php

namespace Fabian\CloudflareUnifiIpImport;

use Fabian\CloudflareUnifiIpImport\models\CloudflareIpResponse;

class CloudFlare
{

    public function fetchIps(): CloudflareIpResponse
    {
        // Initialize cURL session
        $ch = curl_init();

        // Set the URL
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/ips");

        // Set the HTTP request method to GET
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        // Set the headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        // Return the response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the cURL request and get the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $response = 'cURL error: ' . curl_error($ch);
        }

        // Close the cURL session
        curl_close($ch);

        return new CloudflareIpResponse(
            true,
            json_decode($response)->result->ipv4_cidrs,
            json_decode($response)->result->ipv6_cidrs
        );
    }

}
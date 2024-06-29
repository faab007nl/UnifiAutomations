<?php

namespace Fabian\CloudflareUnifiIpImport;

use Exception;

class PiHole
{
    private string $piHoleUrl;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        // Verify env vars
        if (!isset($_ENV['PIHOLE_HOST'])) {
            throw new Exception('Please set the PI_HOLE_URL environment variable.');
        }

        $this->piHoleUrl = trim($_ENV['PIHOLE_HOST']);
    }

    /**
     * @throws Exception
     */
    public function isOnline(): bool
    {
        // Initialize cURL session
        $ch = curl_init($this->piHoleUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Set timeout to 5 seconds
        curl_setopt($ch, CURLOPT_HEADER, true); // Include the header in the output
        curl_setopt($ch, CURLOPT_NOBODY, true); // We don't need the body content

        // Execute cURL request
        curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            $httpCode = 500;
        }else{
            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        }

        return $httpCode === 200;
    }

}
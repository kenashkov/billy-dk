<?php

declare(strict_types=1);

namespace Kenashkov\BillyDk;

class BillyDk
{
    private string $api_token;

    public function __construct(string $api_token) {
        $this->api_token = $api_token;
    }

    public function update_product()

    public function request($method, $url, $body = null): object
    {
//        try {
            $curl = curl_init("https://api.billysbilling.com/v2" . $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

            // Set headers
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "X-Access-Token: " . $this->api_token,
                "Content-Type: application/json"
            ));

            if ($body) {
                // Set body
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
            }

            // Execute request
            $res = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $body = json_decode($res);

            if ($status >= 400) {
                //throw new \Exception("$method: $url failed with $status - $res");
                throw new \RuntimeException(sprintf('%1$s: request %2$s : %2$s failed with status %4$s.', __CLASS__, ));
            }

            return $body;
//        } catch (Exception $Exception) {
//            print_r($Exception);
//            throw $Exception;
//        }
    }
}
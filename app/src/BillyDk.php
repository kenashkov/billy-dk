<?php

declare(strict_types=1);

namespace Kenashkov\BillyDk;

class BillyDk
{
    private string $api_token;

    public const API_URL = 'https://api.billysbilling.com/v2';

    protected string $api_url;

    /**
     * BillyDk constructor.
     * @param string $api_token
     * @param string $api_url Allows the default API url to be overriden (for example if there is staging server)
     */
    public function __construct(string $api_token, string $api_url = self::API_URL) {
        $this->api_token = $api_token;
        $this->api_url = $api_url;
    }

    public function update_product(ProductInterface $Product): void
    {

    }

    public function request(string $method, string $url, string $body = null): object
    {
//        try {
            $curl = curl_init(self::API_URL . $url);
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
                $message = sprintf(
                    '%1$s: request %2$s : %2$s failed with status %4$s.',
                    __CLASS__,
                    $method,
                    $url,
                    $status,
                );
                throw new \RuntimeException($message);
            }

            return $body;
//        } catch (Exception $Exception) {
//            print_r($Exception);
//            throw $Exception;
//        }
    }
}
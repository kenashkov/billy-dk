<?php

declare(strict_types=1);

namespace Kenashkov\BillyDk;

use Kenashkov\BillyDk\Exceptions\BillyDkException;
use Kenashkov\BillyDk\Interfaces\ProductInterface;

/**
 * Class BillyDk
 * @package Kenashkov\BillyDk
 *
 * Api to billy.dk
 * @link https://www.billy.dk/api/
 *
 */
class BillyDk
{

    public const API_URL = 'https://api.billysbilling.com/v2';

    /**
     * The supported HTTP methods by the billy API
     */
    public const VALID_METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
    ];

    /**
     * The supported paths
     */
    public const VALID_PATHS = [
        '/products',
        '/contacts',
    ];

    /**
     * Must be provided to the constructor.
     * @var string
     */
    private string $api_token;

    /**
     * May differ from the default API_URL
     * @var string
     */
    private string $api_url;

    /**
     * BillyDk constructor.
     * @param string $api_token
     * @param string $api_url Allows the default API url to be overriden (for example if there is staging server)
     */
    public function __construct(string $api_token, string $api_url = self::API_URL) {
        if (!$api_token) {
            throw new \InvalidArgumentException(sprintf('No api_token is provided.'));
        }
        if (!$api_url) {
            throw new \InvalidArgumentException(sprintf('No api_url is provided.'));
        }
        if (!filter_var($api_url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('The provided api_url %1$s is not valid.', $api_url));
        }
        $this->api_token = $api_token;
        $this->api_url = $api_url;
    }

    public function get_api_url(): string
    {
        return $this->api_url;
    }

    public function get_api_token(): string
    {
        return $this->api_token;
    }

    public function update_product(ProductInterface $Product): void
    {

    }

    public function delete_product(ProductInterface $Product): void
    {

    }

    /**
     * Validates the HTTP method forthe api request.
     * @param string $method
     * @throws \InvalidArgumentException
     */
    public static function validate_api_method(string $method): void
    {
        if (!$method) {
            throw new \InvalidArgumentException(sprintf('No method is provided.'));
        }
        if (!in_array($method, self::VALID_METHODS)) {
            $message = sprintf(
                'The provided method %1$s is not valid. The valid methods are %2$s.',
                $method,
                implode(', ', self::VALID_METHODS)
            );
            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * Validates the path part of the API URL.
     * The paths must:
     * - contain leading /
     * - not contain any other /
     * - be [a-z0-9]/i
     * - the path without the optional query must be found in self::VALID_PATHS
     * @param string $path The path may also contain query arguments
     * @throws \InvalidArgumentException
     */
    public static function validate_api_path(string $path): void
    {
        if (!$path) {
            throw new \InvalidArgumentException(sprintf('No path is provided.'));
        }
        if ($path[0] !== '/') {
            $message = sprintf('The provided path %1$s does not start with "/".', $path);
            throw new \InvalidArgumentException($message);
        }
        //no path of the API contains / besides the leading one - it is /bankPayments not /bank/payments
        //there is no - or _ either... all are camelCase
        //if (preg_match('/\(/[a-z0-9\-_]*)/i', $path, $matches)) {
        //the regex below allows for paths like /some/path/to
        //as none of the paths of the API have sub paths having / in the path besides the leading one is dissalowed
        if (strrpos($path, '/')) { // 0 is allowed (the leading /) so no !== FALSE here...
            throw new \InvalidArgumentException(sprintf('The provided path %1$s contains a / besides the leading one.', $path));
        }
        if (preg_match('/(\/[a-z0-9]*)/i', $path, $matches)) {
            $base_path = $matches[0];
            if (!in_array($base_path, self::VALID_PATHS)) {
                $message = sprintf(
                    'The provided path %1$s contains a base path %2$s which is not supported. The supported paths are %3$s.',
                    $path,
                    $base_path,
                    implode(', ',self::VALID_PATHS)
                );
                throw new \InvalidArgumentException($message);
            }
        } else {
            throw new \InvalidArgumentException(sprintf('The provided path %1$s does not appear to be valid.'));
        }
    }

    /**
     * @param string $method
     * @param string $path
     * @param string|null $body
     * @return object
     * @throws BillyDkException
     */
    public function request(string $method, string $path, string $body = null): object
    {
        self::validate_api_method($method);
        self::validate_api_path($path);

        $curl = curl_init(self::API_URL . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // Set headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "X-Access-Token: " . $this->api_token,
            "Content-Type: application/json"
        ]);

        if ($body) {
            // Set body
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }

        // Execute request
        $res = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $body = json_decode($res);

        if ($http_code >= 400) {
            $message = sprintf(
                '%1$s: request %2$s : %2$s failed with status %4$s.',
                __CLASS__,
                $method,
                $url,
                $status,
            );
            throw new BillyDkException($message);
        }

        return $body;

    }
}
<?php

declare(strict_types=1);

namespace Kenashkov\BillyDk;

use Kenashkov\BillyDk\Exceptions\BillyDkException;
use Kenashkov\BillyDk\Exceptions\BillyDkNotFoundException;
use Kenashkov\ErpApi\Interfaces\ErpExceptionInterface;
use Kenashkov\ErpApi\Interfaces\ErpNotFoundExceptionInterface;
use Kenashkov\ErpApi\Interfaces\ErpInterface;
use Kenashkov\ErpApi\Interfaces\ProductInterface;

/**
 * Class BillyDk
 * @package Kenashkov\BillyDk
 *
 * Api to billy.dk
 * @link https://www.billy.dk/api/
 *
 */
class BillyDk implements ErpInterface
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
     * A page may have a maximum of X elements
     */
    public const MAX_PAGE_SIZE = 1000;

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

    /**
     * Get the API url in use (the one provided to the constructor)
     * @return string
     */
    public function get_api_url(): string
    {
        return $this->api_url;
    }

    /**
     * Get the API token in use (the one provided to the constructor)
     * @return string
     */
    public function get_api_token(): string
    {
        return $this->api_token;
    }


    /**
     * Returns a product from the ERP based on ERP compatible product from the local app
     * @param ProductInterface $Product
     * @param \stdClass|null $Response The complete response (can be used for debug)
     * @return ProductInterface
     * @throws \ReflectionException
     */
    public function get_product(ProductInterface $Product, ?\stdClass &$Response = NULL): ProductInterface
    {
        $product_erp_id = $Product->get_erp_id();
        return $this->get_product_by_id($product_erp_id, $Response);
    }

    /**
     * @param string $product_erp_id
     * @param \stdClass|null $Response The complete response (can be used for debug)
     * @return ProductInterface
     * @throws \ReflectionException
     */
    public function get_product_by_id(string $product_erp_id, ?\stdClass &$Response = NULL): ProductInterface
    {
        if (!$product_erp_id) {
            throw new \InvalidArgumentException(sprintf('No product_erp_id provided.'));
        }
        $Response = $this->request('GET', '/products/'. $product_erp_id);
        if (isset($Response->errorCode) && $Response->errorCode === 'RECORD_NOT_FOUND') {
            throw new BillyDkNotFoundException($Response->errorMessage);
        }
        return self::get_product_from_object($Response->product);
    }

    /**
     * Used for both updategin existing products and creating new ones.
     * @param ProductInterface $Product
     * @param \stdClass|null $Response The complete response (can be used for debug)
     * @return string The ERP ID ofthe created/updated object (this is needed for the newly created records)
     */
    public function update_product(ProductInterface $Product, ?\stdClass &$Response = NULL): string
    {
        $product_erp_id = $Product->get_erp_id();
        $method = $product_erp_id ? 'PUT' : 'POST';
        $path = $product_erp_id ? '/products/'.$product_erp_id : '/products';

        $Response = $this->request($method, $path, ['product' => $Product->get_erp_product_formatted_array() ] );
        $ret = $Response->products[0]->id;
        return $ret;
    }

    /**
     * Delete a product
     * @param ProductInterface $Product
     * @param \stdClass|null $Response The complete response (can be used for debug)
     * @throws BillyDkException
     */
    public function delete_product(ProductInterface $Product, ?\stdClass &$Response = NULL): void
    {
        $product_billy_id = $Product->get_erp_id();
        $method = 'DELETE';
        $path = '/products/'.$product_billy_id;
        $Response = $this->request($method, $path );
    }

    /**
     *
     * @param int $page
     * @param int $page_size
     * @return ProductInterface[] (in fact BillyDk\ProductInterface[] is returned which is a covariant)
     * @throws BillyDkException
     */
    public function get_products(int $page = 1, int $page_size = 1000): array
    {
        if ($page < 1) {
            throw new \InvalidArgumentException(sprintf('The page must be a positive number.'));
        }
        if ($page_size < 1) {
            throw new \InvalidArgumentException(sprintf('The page size must be a positive number.'));
        }
        if ($page_size > self::MAX_PAGE_SIZE) {
            throw new \InvalidArgumentException(sprintf('A page may have a maximum %1$s elements.', self::MAX_PAGE_SIZE));
        }

        $Response = $this->request('GET', "/products?page={$page}&pageSize={$page_size}");

        $ret = [];
        foreach ($Response->products as $Product) {
            $ret[] = self::get_product_from_object($Product);
        }
        return $ret;
    }

    /**
     * Validates the HTTP method forthe api request.
     * @param string $method
     * @throws \InvalidArgumentException
     */
    protected static function validate_api_method(string $method): void
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
     * A helper method for converting an object from the decoded json response to ProductInterface
     * @param \stdClass $Object
     * @return ProductInterface
     * @throws \ReflectionException
     */
    protected static function get_product_from_object(\stdClass $Object): ProductInterface
    {
        //optimization for Swoole - this will persist between the requests
        static $params = [];
        if (!$params) {
            $params = (new \ReflectionMethod(Product::class, '__construct'))->getParameters();
        }
//            $ret[] = new Product(
//                $Product->id,
//                $Product->organizationId,
//                $Product->name,
//                $Product->description,
//                $Product->accountId,
//                $Product->productNo,
//                $Product->suppliersProductNo,
//                $Product->salesTaxRulesetId,
//                $Product->isArchived,
//            );
        //a more flexible way
        $constr_args = [];
        foreach ($params as $RParam ) {
            $constr_args[] = $Object->{$RParam->getName()};
        }
        return new Product(...$constr_args);
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
    protected static function validate_api_path(string $path): void
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
        //for PUT & DELETE there is another /
//        if (strrpos($path, '/')) { // 0 is allowed (the leading /) so no !== FALSE here...
//            throw new \InvalidArgumentException(sprintf('The provided path %1$s contains a / besides the leading one.', $path));
//        }
        //TODO - instead a validation for ID afterthe second / can be added (based on the ID length)
        if (preg_match('/(\/[a-z0-9]+)/i', $path, $matches)) {
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
            throw new \InvalidArgumentException(sprintf('The provided path %1$s does not appear to be valid.', $path));
        }
    }

    /**
     * Executes a request against the API.
     * The exceptions are left to bubble out and can be caught in the Models (by catching ErpExceptionInterface)
     * @param string $method
     * @param string $path
     * @param string|null $body
     * @return object
     * @throws ErpExceptionInterface
     */
    protected function request(string $method, string $path, ?array $body = null): \stdClass
    {
        self::validate_api_method($method);
        self::validate_api_path($path);
        //TODO a validation for single / in the path can be added for GET method

        $url = self::API_URL . $path;
        $curl = curl_init($url);
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
        if ($res === false) {
            $message = sprintf('%1$s to %2$s failed with error: %3$s.', $method, $url, curl_error($curl));
        }
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $body = json_decode($res);

        //lets not catch any errors here and let them bubble to the methods of the class - they will handle these
//        if ($http_code >= 400) {
//            $message = sprintf(
//                '%1$s: request %2$s : %2$s failed with HTTP code %4$s. Error: %5$s',
//                __CLASS__,
//                $method,
//                $path,
//                $http_code,
//                $body->errorMessage
//            );
//            throw new BillyDkException($message);
//        }


        return $body;

    }
}
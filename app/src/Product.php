<?php

declare(strict_types=1);

namespace Kenashkov\BillyDk;

use Kenashkov\BillyDk\Interfaces\ProductInterface;
use Kenashkov\BillyDk\Traits\ProductTrait;

/**
 * Class Product
 * @package Kenashkov\BillyDk
 *
 * Follows the naming convention of the Billy API
 */
class Product implements ProductInterface
{

    use ProductTrait;

    private string $id;
    private string $organizationId;
    private string $name;
    private string $description;
    private string $accountId;
    private string $productNo;
    private string $suppliersProductNo;
    private string $salesTaxRulesetId;
    private bool $isArchived;

    public function __construct(
        string $id,
        string $organizationId,
        string $name,
        string $description,
        string $accountId,
        string $productNo,
        string $suppliersProductNo,
        string $salesTaxRulesetId,
        bool $isArchived
    )
    {
        //a speed up for Swoole (this will persist between the requests)
        //also good for normal PHP context as there will be multiple instances of this class created in one request
        static $properties = [];
        if (!$properties) {
            $properties = (new \ReflectionClass($this))->getProperties();
        }
        //foreach ( (new \ReflectionClass($this))->getProperties() as $RProperty) {
        foreach ($properties as $RProperty) {
            $this->{$RProperty->getName()} = ${$RProperty->getName()};
        }
//        //standard way
//        $this->id = $id;
//        $this->organizationId = $organizationId;
//        $this->name = $name;
//        $this->description = $description;
//        $this->accountId = $accountId;
//        $this->productNo = $productNo;
//        $this->suppliersProductNo = $suppliersProductNo;
//        $this->salesTaxRulesetId = $salesTaxRulesetId;
//        $this->isArchived = $isArchived;
    }

    /**
     * @implements ProductInterface
     * @return string
     */
    public function get_erp_id(): string
    {
        return $this->id;
    }


    public function get_erp_organization(): string
    {
        return $this->organizationId;
    }

    public function get_erp_name(): string
    {
        return $this->name;
    }

    public function get_erp_description(): string
    {
        return $this->description;
    }

    public function get_erp_account(): string
    {
        return $this->accountId;
    }

    public function get_erp_product_number(): string
    {
        return $this->productNo;
    }

    public function get_erp_suppliers_product_number(): string
    {
        return $this->suppliersProductNo;
    }

    public function get_erp_sales_tax_ruleset(): string
    {
        return $this->salesTaxRulesetId;
    }

    public function get_erp_is_archived(): bool
    {
        return (bool) $this->isArchived;
    }
}
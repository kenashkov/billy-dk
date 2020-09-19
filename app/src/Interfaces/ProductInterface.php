<?php

declare(strict_types=1);

namespace Kenashkov\BillyDk\Interfaces;

use Kenashkov\BillyDk\Traits\ProductTrait;

/**
 * Interface ProductInterface
 * @package Kenashkov\BillyDk
 *
 * Represents a product that can be added to Billy.dk
 *
 * @example data
 * {
 * "id": "McuTyKwkQDq86ICyGT4NXA",
 * "organizationId": "cwNMzNn1TOWhrYwyb6jdfA",
 * "name": "product 21",
 * "description": "43432",
 * "accountId": "4qAjMzZRRoO7sOAjzkorjw",
 * "inventoryAccountId": null,
 * "productNo": "432432",
 * "suppliersProductNo": "4324",
 * "salesTaxRulesetId": "K5A89XDhQJeiyC9HtTX6Hw",
 * "isArchived": false,
 * "isInInventory": false,
 * "imageId": null,
 * "imageUrl": null
 * },
 *
 * An Interface and a helper Trait (@see ProductTrait) is provided instead of an Abstract Class so that the implementing
 * class can be from another hierarchy (and not forced to extend an abstract class from this hierarchy).
 *
 * Individual methods for each property as provided instead of a single method retreiving all the properties for a more
 * formal validation and descriptive purpose.
 */
interface ProductInterface
{

    /**
     * A mapping between methods (returning the data) and the names of the properties in the Billy.dk API
     */
    public const PROPERTY_METHOD_MAP = [
        'get_billy_id'                          => 'id',
        'get_billy_organization'                => 'organizationId',
        'get_billy_name'                        => 'name',
        'get_billy_description'                 => 'description',
        'get_billy_account'                     => 'accountId',
        'get_billy_product_number'              => 'productNo',
        'get_billy_suppliers_product_number'    => 'suppliersProductNo',
        'get_billy_sales_tax_ruleset'           => 'salesTaxRulesetId',
        'get_billy_is_archived'                 => 'isArchived',
    ];

    /**
     * There is a billy_ prefix to the methods as otherwise they may be too generic (for example get_id())
     * and interfere with another class hierarchy when the interface is implemented
     * @return string
     */
    public function get_billy_id(): string;

    /**
     * @return string
     */
    public function get_billy_organization(): string;

    public function get_billy_name(): string;

    public function get_billy_description(): string;

    public function get_billy_account(): string;

    public function get_billy_product_number(): string;

    public function get_billy_suppliers_product_number(): string;

    public function get_billy_sales_tax_ruleset(): string;

    public function get_billy_is_archived(): bool;

    //public function get_prices(): array;

    /**
     * Returns an associative array as expected by the Billy API
     * @see self::PROPERTY_METHOD_MAP
     * @return array
     */
    public function get_billy_product_formatted_array(): array;

}
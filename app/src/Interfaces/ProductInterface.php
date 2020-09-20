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
 * @example data for single product
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


interface ProductInterface extends \Kenashkov\ErpApi\Interfaces\ProductInterface
{

    /**
     * A mapping between the ErpInterface methods (returning the data) and the names of the properties in the Billy.dk API
     */
    public const ERP_METHOD_PROPERTY_MAP = [
        'get_erp_id'                          => 'id',
        'get_erp_organization'                => 'organizationId',
        'get_erp_name'                        => 'name',
        'get_erp_description'                 => 'description',
        'get_erp_account'                     => 'accountId',
        'get_erp_product_number'              => 'productNo',
        'get_erp_suppliers_product_number'    => 'suppliersProductNo',
        'get_erp_sales_tax_ruleset'           => 'salesTaxRulesetId',
        'get_erp_is_archived'                 => 'isArchived',
    ];

}
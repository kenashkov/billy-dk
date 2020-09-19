<?php

declare(strict_types=1);

namespace Kenashkov\BillyDk\Traits;

use Kenashkov\BillyDk\Interfaces\ProductInterface;

/**
 * Trait ProductTrait
 * To be used in conjunction with ProductInterface
 */
trait ProductTrait
{

    /**
     *
     * Has a check is it used on a class implementing the @see ProductInterface because it uses
     * @see ProductInterface::PROPERTY_METHOD_MAP
     *
     * @return array
     */
    public function get_billy_product_formatted_array(): array
    {
        if (!$this instanceof ProductInterface) {
            $message = sprintf(
                'The class %1$s is not a %2$s. The trait %3$s must be used only on classes implementing %4$s.',
                get_class($this),
                ProductInterface::class,
                __TRAIT__,
                ProductInterface::class,
            );
            throw new \RuntimeException($message);
        }
        $ret = [];
        foreach (self::PROPERTY_METHOD_MAP as $method => $property) {
            $ret[$property] = $this->{$method}();
        }
        return $ret;
    }
}
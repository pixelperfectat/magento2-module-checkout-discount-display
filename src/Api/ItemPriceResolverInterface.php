<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Api;

use Magento\Quote\Model\Quote\Item\AbstractItem;

interface ItemPriceResolverInterface
{
    /**
     * Get the regular catalog price for the item's product
     *
     * @param AbstractItem $item
     * @return float
     */
    public function getRegularPrice(AbstractItem $item): float;

    /**
     * Check whether the item has any discount (regular price differs from calculation price)
     *
     * @param AbstractItem $item
     * @return bool
     */
    public function hasDiscount(AbstractItem $item): bool;
}

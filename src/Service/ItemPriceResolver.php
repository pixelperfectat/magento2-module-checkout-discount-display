<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Service;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use PixelPerfect\CheckoutDiscountDisplay\Api\ItemPriceResolverInterface;

class ItemPriceResolver implements ItemPriceResolverInterface
{
    private const PRICE_EPSILON = 0.001;

    /**
     * @inheritDoc
     */
    public function getRegularPrice(AbstractItem $item): float
    {
        return (float) $item->getProduct()->getPrice();
    }

    /**
     * @inheritDoc
     */
    public function hasDiscount(AbstractItem $item): bool
    {
        $product = $item->getProduct();
        $regularPrice = (float) $product->getPrice();
        $finalPrice = (float) $product->getFinalPrice();

        return ($regularPrice - $finalPrice) > self::PRICE_EPSILON;
    }
}

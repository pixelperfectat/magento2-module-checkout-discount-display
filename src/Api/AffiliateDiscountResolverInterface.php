<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Api;

use Magento\Quote\Model\Quote\Item\AbstractItem;

interface AffiliateDiscountResolverInterface
{
    /**
     * Get the total affiliate discount amount applied to an item
     *
     * @param AbstractItem $item
     * @return float
     */
    public function getAffiliateDiscountForItem(AbstractItem $item): float;

    /**
     * Get the resolved affiliate rule IDs for the current session
     *
     * @return string[]
     */
    public function getAffiliateRuleIds(): array;
}

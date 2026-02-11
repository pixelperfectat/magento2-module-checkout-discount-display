<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Api;

use Magento\Quote\Model\Quote\Item\AbstractItem;

interface ItemMessageServiceInterface
{
    /**
     * Build and attach per-item discount messages
     *
     * @param AbstractItem $item
     * @return void
     */
    public function addMessagesForItem(AbstractItem $item): void;
}

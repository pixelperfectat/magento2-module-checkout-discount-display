<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\ViewModel;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\StoreManagerInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ConfigInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ItemPriceResolverInterface;

class CartItemPriceData implements ArgumentInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly ItemPriceResolverInterface $priceResolver,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * Check if cart page messages are enabled for the current store
     *
     * @return bool
     */
    public function isCartMessagesEnabled(): bool
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        return $this->config->isCartMessagesEnabled($storeId);
    }

    /**
     * Get the formatted regular catalog price
     *
     * @param AbstractItem $item
     * @return string
     */
    public function getRegularPrice(AbstractItem $item): string
    {
        $price = $this->priceResolver->getRegularPrice($item);
        return $this->priceCurrency->format($price, false);
    }

    /**
     * Check if the item has a discount (regular price differs from calculation price)
     *
     * @param AbstractItem $item
     * @return bool
     */
    public function hasDiscount(AbstractItem $item): bool
    {
        return $this->priceResolver->hasDiscount($item);
    }
}

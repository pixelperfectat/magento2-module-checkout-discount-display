<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Plugin;

use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ConfigInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ItemPriceResolverInterface;

class AddPriceDataToSectionData
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly ItemPriceResolverInterface $priceResolver,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * Add regular price, discount flag and discount messages to section data
     *
     * @param AbstractItem $subject
     * @param array<string, mixed> $result
     * @param Item $item
     * @return array<string, mixed>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItemData(AbstractItem $subject, array $result, Item $item): array
    {
        $storeId = (int) $this->storeManager->getStore()->getId();

        if ($this->config->isStrikethroughEnabled($storeId)) {
            $regularPrice = $this->priceResolver->getRegularPrice($item);

            $result['regular_price'] = $regularPrice;
            $result['regular_price_formatted'] = $this->priceCurrency->format($regularPrice, false);
            $result['has_discount'] = $this->priceResolver->hasDiscount($item);
        }

        if ($this->config->isMessagesEnabled($storeId)) {
            $messages = $item->getMessage(false);
            $result['discount_messages'] = is_array($messages) ? array_values($messages) : [];
        }

        return $result;
    }
}

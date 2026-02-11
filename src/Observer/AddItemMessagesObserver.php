<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ConfigInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ItemMessageServiceInterface;

class AddItemMessagesObserver implements ObserverInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly ItemMessageServiceInterface $itemMessageService,
        private readonly StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * Add per-item discount messages after totals collection
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        if (!$this->config->isMessagesEnabled($storeId)) {
            return;
        }

        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            $this->itemMessageService->addMessagesForItem($item);
        }
    }
}

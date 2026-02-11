<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PixelPerfect\CheckoutDiscountDisplay\Api\ConfigInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ItemMessageServiceInterface;
use PixelPerfect\CheckoutDiscountDisplay\Observer\AddItemMessagesObserver;

class AddItemMessagesObserverTest extends TestCase
{
    private ConfigInterface&MockObject $config;
    private ItemMessageServiceInterface&MockObject $itemMessageService;
    private StoreManagerInterface&MockObject $storeManager;
    private AddItemMessagesObserver $observer;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->itemMessageService = $this->createMock(ItemMessageServiceInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(5);
        $this->storeManager->method('getStore')->willReturn($store);

        $this->observer = new AddItemMessagesObserver(
            $this->config,
            $this->itemMessageService,
            $this->storeManager,
        );
    }

    public function testSkipsWhenMessagesDisabled(): void
    {
        $this->config->method('isMessagesEnabled')->with(5)->willReturn(false);
        $this->itemMessageService->expects($this->never())->method('addMessagesForItem');

        $this->observer->execute($this->createObserverMock([]));
    }

    public function testProcessesEachVisibleItem(): void
    {
        $this->config->method('isMessagesEnabled')->with(5)->willReturn(true);

        $item1 = $this->createMock(AbstractItem::class);
        $item2 = $this->createMock(AbstractItem::class);

        $this->itemMessageService->expects($this->exactly(2))
            ->method('addMessagesForItem');

        $this->observer->execute($this->createObserverMock([$item1, $item2]));
    }

    public function testHandlesEmptyCart(): void
    {
        $this->config->method('isMessagesEnabled')->with(5)->willReturn(true);
        $this->itemMessageService->expects($this->never())->method('addMessagesForItem');

        $this->observer->execute($this->createObserverMock([]));
    }

    /**
     * @param AbstractItem[] $items
     */
    private function createObserverMock(array $items): Observer
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getAllVisibleItems')->willReturn($items);

        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuote'])
            ->getMock();
        $event->method('getQuote')->willReturn($quote);

        $observer = $this->createMock(Observer::class);
        $observer->method('getEvent')->willReturn($event);

        return $observer;
    }
}

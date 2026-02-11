<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Test\Unit\Plugin;

use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PixelPerfect\CheckoutDiscountDisplay\Api\ConfigInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ItemPriceResolverInterface;
use PixelPerfect\CheckoutDiscountDisplay\Plugin\AddPriceDataToSectionData;

class AddPriceDataToSectionDataTest extends TestCase
{
    private ConfigInterface&MockObject $config;
    private ItemPriceResolverInterface&MockObject $priceResolver;
    private PriceCurrencyInterface&MockObject $priceCurrency;
    private StoreManagerInterface&MockObject $storeManager;
    private AddPriceDataToSectionData $plugin;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->priceResolver = $this->createMock(ItemPriceResolverInterface::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(5);
        $this->storeManager->method('getStore')->willReturn($store);

        $this->plugin = new AddPriceDataToSectionData(
            $this->config,
            $this->priceResolver,
            $this->priceCurrency,
            $this->storeManager,
        );
    }

    public function testReturnsUnmodifiedResultWhenDisabled(): void
    {
        $this->config->method('isStrikethroughEnabled')->with(5)->willReturn(false);

        $subject = $this->createMock(AbstractItem::class);
        $item = $this->createMock(Item::class);
        $result = ['product_price' => '€80.00'];

        $output = $this->plugin->afterGetItemData($subject, $result, $item);

        $this->assertArrayNotHasKey('regular_price', $output);
    }

    public function testAddsRegularPriceDataWhenEnabled(): void
    {
        $this->config->method('isStrikethroughEnabled')->with(5)->willReturn(true);
        $this->priceResolver->method('getRegularPrice')->willReturn(100.00);
        $this->priceResolver->method('hasDiscount')->willReturn(true);
        $this->priceCurrency->method('format')->with(100.00, false)->willReturn('€100.00');

        $subject = $this->createMock(AbstractItem::class);
        $item = $this->createMock(Item::class);
        $result = ['product_price' => '€80.00'];

        $output = $this->plugin->afterGetItemData($subject, $result, $item);

        $this->assertSame(100.00, $output['regular_price']);
        $this->assertSame('€100.00', $output['regular_price_formatted']);
        $this->assertTrue($output['has_discount']);
    }

    public function testHasDiscountFalseWhenNoDifference(): void
    {
        $this->config->method('isStrikethroughEnabled')->with(5)->willReturn(true);
        $this->priceResolver->method('getRegularPrice')->willReturn(100.00);
        $this->priceResolver->method('hasDiscount')->willReturn(false);
        $this->priceCurrency->method('format')->willReturn('€100.00');

        $subject = $this->createMock(AbstractItem::class);
        $item = $this->createMock(Item::class);
        $result = ['product_price' => '€100.00'];

        $output = $this->plugin->afterGetItemData($subject, $result, $item);

        $this->assertFalse($output['has_discount']);
    }
}

<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Test\Unit\Service;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PixelPerfect\CheckoutDiscountDisplay\Service\ItemPriceResolver;

class ItemPriceResolverTest extends TestCase
{
    private ItemPriceResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ItemPriceResolver();
    }

    public function testGetRegularPriceReturnsProductPrice(): void
    {
        $item = $this->createItemWithPrices(regularPrice: 100.00, calculationPrice: 80.00);

        $this->assertSame(100.00, $this->resolver->getRegularPrice($item));
    }

    public function testHasDiscountReturnsTrueWhenPricesDiffer(): void
    {
        $item = $this->createItemWithPrices(regularPrice: 100.00, calculationPrice: 80.00);

        $this->assertTrue($this->resolver->hasDiscount($item));
    }

    public function testHasDiscountReturnsFalseWhenPricesMatch(): void
    {
        $item = $this->createItemWithPrices(regularPrice: 100.00, calculationPrice: 100.00);

        $this->assertFalse($this->resolver->hasDiscount($item));
    }

    public function testHasDiscountHandlesFloatingPointComparison(): void
    {
        $item = $this->createItemWithPrices(regularPrice: 29.99, calculationPrice: 29.99);

        $this->assertFalse($this->resolver->hasDiscount($item));
    }

    private function createItemWithPrices(float $regularPrice, float $calculationPrice): AbstractItem&MockObject
    {
        $product = $this->createMock(Product::class);
        $product->method('getPrice')->willReturn($regularPrice);

        $item = $this->createMock(AbstractItem::class);
        $item->method('getProduct')->willReturn($product);
        $item->method('getCalculationPrice')->willReturn($calculationPrice);

        return $item;
    }
}

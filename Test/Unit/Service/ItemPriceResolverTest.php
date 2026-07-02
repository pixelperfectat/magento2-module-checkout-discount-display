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
        $item = $this->createItemWithPrices(regularPrice: 100.00, finalPrice: 80.00);

        $this->assertSame(100.00, $this->resolver->getRegularPrice($item));
    }

    public function testHasDiscountReturnsTrueWhenPricesDiffer(): void
    {
        $item = $this->createItemWithPrices(regularPrice: 100.00, finalPrice: 80.00);

        $this->assertTrue($this->resolver->hasDiscount($item));
    }

    public function testHasDiscountReturnsFalseWhenPricesMatch(): void
    {
        $item = $this->createItemWithPrices(regularPrice: 100.00, finalPrice: 100.00);

        $this->assertFalse($this->resolver->hasDiscount($item));
    }

    public function testHasDiscountHandlesFloatingPointComparison(): void
    {
        $item = $this->createItemWithPrices(regularPrice: 29.99, finalPrice: 29.99);

        $this->assertFalse($this->resolver->hasDiscount($item));
    }

    private function createItemWithPrices(float $regularPrice, float $finalPrice): AbstractItem&MockObject
    {
        $product = $this->createMock(Product::class);
        $product->method('getPrice')->willReturn($regularPrice);
        $product->method('getFinalPrice')->willReturn($finalPrice);

        $item = $this->createMock(AbstractItem::class);
        $item->method('getProduct')->willReturn($product);

        return $item;
    }
}

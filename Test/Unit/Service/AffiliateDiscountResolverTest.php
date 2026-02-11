<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Test\Unit\Service;

use Amasty\Affiliate\Model\Rule\AffiliateQuoteResolver;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Api\Data\DiscountDataInterface;
use Magento\SalesRule\Api\Data\RuleDiscountInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PixelPerfect\CheckoutDiscountDisplay\Service\AffiliateDiscountResolver;

class AffiliateDiscountResolverTest extends TestCase
{
    private AffiliateQuoteResolver&MockObject $affiliateQuoteResolver;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->affiliateQuoteResolver = $this->createMock(AffiliateQuoteResolver::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testReturnsZeroWhenAffiliateResolverIsNull(): void
    {
        $resolver = new AffiliateDiscountResolver(null, $this->logger);
        $item = $this->createMock(AbstractItem::class);

        $this->assertSame(0.0, $resolver->getAffiliateDiscountForItem($item));
    }

    public function testReturnsZeroWhenNoAffiliateRuleIds(): void
    {
        $this->affiliateQuoteResolver->method('resolveRuleIds')->willReturn([]);
        $resolver = new AffiliateDiscountResolver($this->affiliateQuoteResolver, $this->logger);
        $item = $this->createMock(AbstractItem::class);

        $this->assertSame(0.0, $resolver->getAffiliateDiscountForItem($item));
    }

    public function testReturnsZeroWhenItemHasNoDiscounts(): void
    {
        $this->affiliateQuoteResolver->method('resolveRuleIds')->willReturn(['10']);
        $resolver = new AffiliateDiscountResolver($this->affiliateQuoteResolver, $this->logger);

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDiscounts'])
            ->getMockForAbstractClass();
        $extensionAttributes->method('getDiscounts')->willReturn(null);

        $item = $this->createMock(AbstractItem::class);
        $item->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->assertSame(0.0, $resolver->getAffiliateDiscountForItem($item));
    }

    public function testSumsAffiliateDiscountsOnly(): void
    {
        $this->affiliateQuoteResolver->method('resolveRuleIds')->willReturn(['10', '20']);
        $resolver = new AffiliateDiscountResolver($this->affiliateQuoteResolver, $this->logger);

        $affiliateDiscount = $this->createDiscount('10', 5.00);
        $couponDiscount = $this->createDiscount('99', 3.00);
        $anotherAffiliateDiscount = $this->createDiscount('20', 2.50);

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDiscounts'])
            ->getMockForAbstractClass();
        $extensionAttributes->method('getDiscounts')
            ->willReturn([$affiliateDiscount, $couponDiscount, $anotherAffiliateDiscount]);

        $item = $this->createMock(AbstractItem::class);
        $item->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->assertSame(7.50, $resolver->getAffiliateDiscountForItem($item));
    }

    public function testCachesResolvedRuleIds(): void
    {
        $this->affiliateQuoteResolver->expects($this->once())
            ->method('resolveRuleIds')
            ->willReturn(['10']);

        $resolver = new AffiliateDiscountResolver($this->affiliateQuoteResolver, $this->logger);

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDiscounts'])
            ->getMockForAbstractClass();
        $extensionAttributes->method('getDiscounts')->willReturn([]);

        $item = $this->createMock(AbstractItem::class);
        $item->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $resolver->getAffiliateDiscountForItem($item);
        $resolver->getAffiliateDiscountForItem($item);
    }

    public function testGetAffiliateRuleIdsReturnsResolvedIds(): void
    {
        $this->affiliateQuoteResolver->method('resolveRuleIds')->willReturn(['10', '20']);
        $resolver = new AffiliateDiscountResolver($this->affiliateQuoteResolver, $this->logger);

        $this->assertSame(['10', '20'], $resolver->getAffiliateRuleIds());
    }

    public function testGetAffiliateRuleIdsReturnsEmptyWhenNoResolver(): void
    {
        $resolver = new AffiliateDiscountResolver(null, $this->logger);

        $this->assertSame([], $resolver->getAffiliateRuleIds());
    }

    private function createDiscount(string $ruleId, float $amount): RuleDiscountInterface&MockObject
    {
        $discountData = $this->createMock(DiscountDataInterface::class);
        $discountData->method('getAmount')->willReturn($amount);

        $discount = $this->createMock(RuleDiscountInterface::class);
        $discount->method('getRuleID')->willReturn($ruleId);
        $discount->method('getDiscountData')->willReturn($discountData);

        return $discount;
    }
}

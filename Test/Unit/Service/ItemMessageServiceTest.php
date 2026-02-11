<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Test\Unit\Service;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Api\Data\DiscountDataInterface;
use Magento\SalesRule\Api\Data\RuleDiscountInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PixelPerfect\CheckoutDiscountDisplay\Api\AffiliateDiscountResolverInterface;
use PixelPerfect\CheckoutDiscountDisplay\Service\ItemMessageService;

class ItemMessageServiceTest extends TestCase
{
    private AffiliateDiscountResolverInterface&MockObject $affiliateDiscountResolver;
    private PriceCurrencyInterface&MockObject $priceCurrency;
    private ItemMessageService $service;

    protected function setUp(): void
    {
        $this->affiliateDiscountResolver = $this->createMock(AffiliateDiscountResolverInterface::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->priceCurrency->method('format')
            ->willReturnCallback(fn(float $amount) => '€' . number_format($amount, 2));

        $this->affiliateDiscountResolver->method('getAffiliateRuleIds')->willReturn([]);

        $this->service = new ItemMessageService(
            $this->affiliateDiscountResolver,
            $this->priceCurrency,
        );
    }

    public function testNoMessagesWhenNoFlagsOrDiscounts(): void
    {
        $item = $this->createItemMock();
        $item->expects($this->never())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testStandardExclusionMessage(): void
    {
        $item = $this->createItemMock([
            'pp_discount_excluded' => true,
            'pp_discount_exclusion_reason' => 'standard',
        ]);
        $item->expects($this->once())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testExistingBetterPercentExclusionMessage(): void
    {
        $item = $this->createItemMock([
            'pp_discount_excluded' => true,
            'pp_discount_exclusion_reason' => 'existing_better',
            'pp_discount_exclusion_params' => [
                'simpleAction' => 'by_percent',
                'existingDiscountPercent' => 25.0,
                'ruleDiscountPercent' => 20.0,
            ],
        ]);
        $item->expects($this->once())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testExistingBetterFixedExclusionMessage(): void
    {
        $item = $this->createItemMock([
            'pp_discount_excluded' => true,
            'pp_discount_exclusion_reason' => 'existing_better',
            'pp_discount_exclusion_params' => [
                'simpleAction' => 'by_fixed',
                'existingDiscountAmount' => 7.50,
                'ruleDiscountAmount' => 5.00,
            ],
        ]);
        $item->expects($this->once())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testBypassAdjustedPercentMessage(): void
    {
        $item = $this->createItemMock([
            'pp_discount_bypass_adjusted' => true,
            'pp_discount_exclusion_params' => [
                'simpleAction' => 'by_percent',
                'additionalDiscountPercent' => 5.0,
                'ruleDiscountPercent' => 30.0,
                'existingDiscountPercent' => 25.0,
            ],
        ]);
        $item->expects($this->once())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testBypassAdjustedFixedMessage(): void
    {
        $item = $this->createItemMock([
            'pp_discount_bypass_adjusted' => true,
            'pp_discount_exclusion_params' => [
                'simpleAction' => 'by_fixed',
                'additionalDiscountAmount' => 2.50,
                'ruleDiscountAmount' => 10.00,
                'existingDiscountAmount' => 7.50,
            ],
        ]);
        $item->expects($this->once())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testAffiliateDiscountMessage(): void
    {
        $this->affiliateDiscountResolver->method('getAffiliateDiscountForItem')
            ->willReturn(5.00);

        $item = $this->createItemMock();
        $item->expects($this->once())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testNoAffiliateMessageWhenZero(): void
    {
        $this->affiliateDiscountResolver->method('getAffiliateDiscountForItem')
            ->willReturn(0.0);

        $item = $this->createItemMock();
        $item->expects($this->never())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testCouponDiscountMessage(): void
    {
        $this->affiliateDiscountResolver->method('getAffiliateRuleIds')->willReturn(['10']);
        $this->affiliateDiscountResolver->method('getAffiliateDiscountForItem')->willReturn(0.0);

        $service = new ItemMessageService(
            $this->affiliateDiscountResolver,
            $this->priceCurrency,
        );

        $couponDiscount = $this->createDiscount('99', 3.00);

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDiscounts'])
            ->getMockForAbstractClass();
        $extensionAttributes->method('getDiscounts')->willReturn([$couponDiscount]);

        $item = $this->createItemMock();
        $item->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $item->expects($this->once())->method('addMessage');

        $service->addMessagesForItem($item);
    }

    public function testCouponDiscountSkipsAffiliateRules(): void
    {
        $this->affiliateDiscountResolver->method('getAffiliateRuleIds')->willReturn(['10']);
        $this->affiliateDiscountResolver->method('getAffiliateDiscountForItem')->willReturn(5.00);

        $service = new ItemMessageService(
            $this->affiliateDiscountResolver,
            $this->priceCurrency,
        );

        $affiliateDiscount = $this->createDiscount('10', 5.00);

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDiscounts'])
            ->getMockForAbstractClass();
        $extensionAttributes->method('getDiscounts')->willReturn([$affiliateDiscount]);

        $item = $this->createItemMock();
        $item->method('getExtensionAttributes')->willReturn($extensionAttributes);

        // Should get affiliate message only, NOT a coupon message for rule 10
        $item->expects($this->once())->method('addMessage');

        $service->addMessagesForItem($item);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createItemMock(array $data = []): AbstractItem&MockObject
    {
        $item = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addMessage', 'getExtensionAttributes', 'getProduct'])
            ->addMethods(['getData'])
            ->getMock();

        $item->method('getData')->willReturnCallback(
            fn(string $key) => $data[$key] ?? null
        );

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDiscounts'])
            ->getMockForAbstractClass();
        $extensionAttributes->method('getDiscounts')->willReturn(null);
        $item->method('getExtensionAttributes')->willReturn($extensionAttributes);

        return $item;
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

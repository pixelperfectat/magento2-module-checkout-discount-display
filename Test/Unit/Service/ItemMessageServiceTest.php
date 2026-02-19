<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Test\Unit\Service;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Api\Data\DiscountDataInterface;
use Magento\SalesRule\Api\Data\RuleDiscountInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PixelPerfect\CheckoutDiscountDisplay\Api\AffiliateDiscountResolverInterface;
use PixelPerfect\CheckoutDiscountDisplay\Service\ItemMessageService;
use PixelPerfect\DiscountExclusion\Api\ConfigInterface as DiscountExclusionConfig;

class ItemMessageServiceTest extends TestCase
{
    private AffiliateDiscountResolverInterface&MockObject $affiliateDiscountResolver;
    private PriceCurrencyInterface&MockObject $priceCurrency;
    private DiscountExclusionConfig&MockObject $discountExclusionConfig;
    private ItemMessageService $service;

    protected function setUp(): void
    {
        $this->affiliateDiscountResolver = $this->createMock(AffiliateDiscountResolverInterface::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->priceCurrency->method('format')
            ->willReturnCallback(fn(float $amount) => '€' . number_format($amount, 2));

        $this->affiliateDiscountResolver->method('getAffiliateRuleIds')->willReturn([]);

        $this->discountExclusionConfig = $this->createMock(DiscountExclusionConfig::class);
        $this->discountExclusionConfig->method('isMessagesEnabled')->willReturn(true);

        $this->service = new ItemMessageService(
            $this->affiliateDiscountResolver,
            $this->priceCurrency,
            $this->discountExclusionConfig,
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

    public function testBypassAdjustedPercentSkipsWhenNoReduction(): void
    {
        $item = $this->createItemMock([
            'pp_discount_bypass_adjusted' => true,
            'pp_discount_exclusion_params' => [
                'simpleAction' => 'by_percent',
                'existingDiscountPercent' => 0.0,
                'ruleDiscountPercent' => 5.0,
                'additionalDiscountPercent' => 5.0,
            ],
        ]);
        $item->expects($this->never())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testBypassAdjustedFixedSkipsWhenNoReduction(): void
    {
        $item = $this->createItemMock([
            'pp_discount_bypass_adjusted' => true,
            'pp_discount_exclusion_params' => [
                'simpleAction' => 'by_fixed',
                'existingDiscountAmount' => 0.0,
                'ruleDiscountAmount' => 5.00,
                'additionalDiscountAmount' => 5.00,
            ],
        ]);
        $item->expects($this->never())->method('addMessage');

        $this->service->addMessagesForItem($item);
    }

    public function testCouponDiscountMessage(): void
    {
        $affiliateResolver = $this->createMock(AffiliateDiscountResolverInterface::class);
        $affiliateResolver->method('getAffiliateRuleIds')->willReturn(['10']);
        $affiliateResolver->method('getAffiliateDiscountForItem')->willReturn(0.0);

        $service = new ItemMessageService(
            $affiliateResolver,
            $this->priceCurrency,
            $this->discountExclusionConfig,
        );

        $couponDiscount = $this->createDiscount('99', 3.00);

        $extensionAttributes = $this->createMock(\Magento\Quote\Api\Data\CartItemExtensionInterface::class);
        $extensionAttributes->method('getDiscounts')->willReturn([$couponDiscount]);

        $item = $this->createItemMock(
            extensionAttributes: $extensionAttributes,
            discountAmount: 3.00,
            couponCode: 'SAVE10',
        );
        $item->expects($this->once())->method('addMessage');

        $service->addMessagesForItem($item);
    }

    public function testCouponDiscountSkipsAffiliateRules(): void
    {
        $affiliateResolver = $this->createMock(AffiliateDiscountResolverInterface::class);
        $affiliateResolver->method('getAffiliateRuleIds')->willReturn(['10']);
        $affiliateResolver->method('getAffiliateDiscountForItem')->willReturn(5.00);

        $service = new ItemMessageService(
            $affiliateResolver,
            $this->priceCurrency,
            $this->discountExclusionConfig,
        );

        $affiliateDiscount = $this->createDiscount('10', 5.00);

        $extensionAttributes = $this->createMock(\Magento\Quote\Api\Data\CartItemExtensionInterface::class);
        $extensionAttributes->method('getDiscounts')->willReturn([$affiliateDiscount]);

        $item = $this->createItemMock(
            extensionAttributes: $extensionAttributes,
            discountAmount: 5.00,
        );

        // Should get affiliate message only, NOT a coupon message for rule 10
        $item->expects($this->once())->method('addMessage');

        $service->addMessagesForItem($item);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createItemMock(
        array $data = [],
        ?\Magento\Quote\Api\Data\CartItemExtensionInterface $extensionAttributes = null,
        float $discountAmount = 0.0,
        string $couponCode = '',
    ): Item&MockObject {
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDiscountAmount'])
            ->onlyMethods(['getData', 'getExtensionAttributes', 'addMessage', 'getQuote'])
            ->getMock();

        $item->method('getData')->willReturnCallback(
            fn(string $key) => $data[$key] ?? null
        );

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->createMock(\Magento\Quote\Api\Data\CartItemExtensionInterface::class);
            $extensionAttributes->method('getDiscounts')->willReturn(null);
        }
        $item->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $item->method('getDiscountAmount')->willReturn($discountAmount);

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCouponCode'])
            ->getMock();
        $quote->method('getCouponCode')->willReturn($couponCode);
        $item->method('getQuote')->willReturn($quote);

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

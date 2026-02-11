<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Service;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use PixelPerfect\CheckoutDiscountDisplay\Api\AffiliateDiscountResolverInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ItemMessageServiceInterface;

class ItemMessageService implements ItemMessageServiceInterface
{
    public function __construct(
        private readonly AffiliateDiscountResolverInterface $affiliateDiscountResolver,
        private readonly PriceCurrencyInterface $priceCurrency,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function addMessagesForItem(AbstractItem $item): void
    {
        $this->addExclusionMessages($item);
        $this->addBypassAdjustedMessages($item);
        $this->addCouponMessages($item);
        $this->addAffiliateMessages($item);
    }

    private function addExclusionMessages(AbstractItem $item): void
    {
        if (!$item->getData('pp_discount_excluded')) {
            return;
        }

        $reason = $item->getData('pp_discount_exclusion_reason');
        $params = $item->getData('pp_discount_exclusion_params') ?? [];

        if ($reason === 'standard') {
            $item->addMessage(
                (string) __('This product is already discounted and excluded from the coupon discount.')
            );
            return;
        }

        if ($reason !== 'existing_better') {
            return;
        }

        $simpleAction = $params['simpleAction'] ?? '';

        if ($simpleAction === Rule::BY_PERCENT_ACTION) {
            $item->addMessage((string) __(
                'Your existing %1% discount is better than the coupon\'s %2% — keeping the better price.',
                number_format((float) ($params['existingDiscountPercent'] ?? 0), 0),
                number_format((float) ($params['ruleDiscountPercent'] ?? 0), 0),
            ));
        } elseif ($simpleAction === Rule::BY_FIXED_ACTION) {
            $item->addMessage((string) __(
                'Your existing %1 discount is better than the coupon\'s %2 — keeping the better price.',
                $this->formatPrice((float) ($params['existingDiscountAmount'] ?? 0)),
                $this->formatPrice((float) ($params['ruleDiscountAmount'] ?? 0)),
            ));
        }
    }

    private function addBypassAdjustedMessages(AbstractItem $item): void
    {
        if (!$item->getData('pp_discount_bypass_adjusted')) {
            return;
        }

        $params = $item->getData('pp_discount_exclusion_params') ?? [];
        $simpleAction = $params['simpleAction'] ?? '';

        if ($simpleAction === Rule::BY_PERCENT_ACTION) {
            $item->addMessage((string) __(
                'Already %1% discounted — discount reduced from %2% to %3%.',
                number_format((float) ($params['existingDiscountPercent'] ?? 0), 0),
                number_format((float) ($params['ruleDiscountPercent'] ?? 0), 0),
                number_format((float) ($params['additionalDiscountPercent'] ?? 0), 0),
            ));
        } elseif ($simpleAction === Rule::BY_FIXED_ACTION) {
            $item->addMessage((string) __(
                'Already %1 discounted — discount reduced from %2 to %3.',
                $this->formatPrice((float) ($params['existingDiscountAmount'] ?? 0)),
                $this->formatPrice((float) ($params['ruleDiscountAmount'] ?? 0)),
                $this->formatPrice((float) ($params['additionalDiscountAmount'] ?? 0)),
            ));
        }
    }

    /**
     * Add coupon discount message using the actual applied discount amount
     *
     * Uses $item->getDiscountAmount() instead of extension attribute discount
     * descriptions, because the DiscountExclusion module caps discounts after
     * the extension attributes are populated — making those values stale.
     *
     * @param AbstractItem $item
     * @return void
     */
    private function addCouponMessages(AbstractItem $item): void
    {
        $totalDiscount = (float) $item->getDiscountAmount();
        if ($totalDiscount <= 0.0) {
            return;
        }

        $affiliateAmount = $this->affiliateDiscountResolver->getAffiliateDiscountForItem($item);
        $couponTotal = $totalDiscount - $affiliateAmount;

        if ($couponTotal <= 0.0) {
            return;
        }

        $couponCode = (string) $item->getQuote()->getCouponCode();
        if ($couponCode !== '') {
            $item->addMessage((string) __(
                'Coupon "%1": %2 discount.',
                $couponCode,
                $this->formatPrice($couponTotal),
            ));
        } else {
            $item->addMessage((string) __(
                '%1 discount applied.',
                $this->formatPrice($couponTotal),
            ));
        }
    }

    private function addAffiliateMessages(AbstractItem $item): void
    {
        $amount = $this->affiliateDiscountResolver->getAffiliateDiscountForItem($item);
        if ($amount <= 0.0) {
            return;
        }

        $item->addMessage((string) __(
            '%1 affiliate discount applied.',
            $this->formatPrice($amount),
        ));
    }

    private function formatPrice(float $amount): string
    {
        return $this->priceCurrency->format($amount, false);
    }
}

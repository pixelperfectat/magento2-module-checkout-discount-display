<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Service;

use Amasty\Affiliate\Model\Rule\AffiliateQuoteResolver;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PixelPerfect\CheckoutDiscountDisplay\Api\AffiliateDiscountResolverInterface;
use Psr\Log\LoggerInterface;

class AffiliateDiscountResolver implements AffiliateDiscountResolverInterface
{
    /** @var string[]|null */
    private ?array $affiliateRuleIds = null;

    public function __construct(
        private readonly ?AffiliateQuoteResolver $affiliateQuoteResolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAffiliateDiscountForItem(AbstractItem $item): float
    {
        $ruleIds = $this->getAffiliateRuleIds();
        if (empty($ruleIds)) {
            return 0.0;
        }

        $discounts = $item->getExtensionAttributes()?->getDiscounts();
        if (empty($discounts)) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($discounts as $discount) {
            if (in_array($discount->getRuleID(), $ruleIds, false)) {
                $total += $discount->getDiscountData()->getAmount();
            }
        }

        return $total;
    }

    /**
     * @inheritDoc
     */
    public function getAffiliateRuleIds(): array
    {
        if ($this->affiliateRuleIds === null) {
            if ($this->affiliateQuoteResolver === null) {
                $this->affiliateRuleIds = [];
            } else {
                try {
                    $this->affiliateRuleIds = $this->affiliateQuoteResolver->resolveRuleIds();
                } catch (\Throwable $e) {
                    $this->logger->error('Failed to resolve affiliate rule IDs', ['exception' => $e]);
                    $this->affiliateRuleIds = [];
                }
            }
        }

        return $this->affiliateRuleIds;
    }
}

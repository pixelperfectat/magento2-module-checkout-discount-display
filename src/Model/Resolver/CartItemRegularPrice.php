<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item;
use PixelPerfect\CheckoutDiscountDisplay\Api\ItemPriceResolverInterface;

class CartItemRegularPrice implements ResolverInterface
{
    public function __construct(
        private readonly ItemPriceResolverInterface $priceResolver,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): ?array
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Item $cartItem */
        $cartItem = $value['model'];
        $currencyCode = $cartItem->getQuote()->getQuoteCurrencyCode();

        return [
            'currency' => $currencyCode,
            'value' => $this->priceResolver->getRegularPrice($cartItem),
        ];
    }
}

# PixelPerfect Checkout Discount Display

Per-item discount messages and strikethrough pricing for Magento 2 carts.

## Features

- **Strikethrough pricing** — regular catalog price crossed out next to the discounted price on cart line items
- **Discount exclusion messages** — explains why an item was excluded from a coupon or why only a partial discount was applied
- **Coupon discount messages** — shows the coupon discount amount per item
- **Affiliate discount messages** — shows affiliate-specific discount amounts (requires Amasty Affiliate)
- **Cart drawer support** — adds price data to customer section data for FPC-compatible cart drawers
- **GraphQL support** — extends `CartItemPrices` with `regular_price`, `has_discount`, and `discount_messages` fields
- **Multi-store** — all features configurable per store view
- **Translations** — included for `en_US`, `de_DE`, `it_IT`, `fr_FR`, `es_ES`

## Requirements

- PHP 8.3+
- Magento 2.4.7+
- [pixelperfectat/magento2-module-discount-exclusion](https://packagist.org/packages/pixelperfectat/magento2-module-discount-exclusion)

### Optional

- **Hyvä Theme** — required for cart page strikethrough pricing template
- **Amasty Affiliate** — enables affiliate discount detection and messaging

## Installation

```bash
composer require pixelperfectat/magento2-module-checkout-discount-display
bin/magento module:enable PixelPerfect_CheckoutDiscountDisplay
bin/magento setup:upgrade
```

## Configuration

**Stores > Configuration > Sales > Checkout Discount Display > General**

| Setting | Description | Default |
|---------|-------------|---------|
| Enable Discount Messages | Show per-item messages for exclusions, coupons, and affiliate discounts | Yes |
| Enable Strikethrough Pricing | Show regular price crossed out next to discounted price | Yes |

Both settings are configurable at the default, website, and store view level.

## GraphQL

The module extends the `CartItemPrices` type:

```graphql
{
  cart(cart_id: "...") {
    items {
      prices {
        regular_price {
          value
          currency
        }
        has_discount
        discount_messages
      }
    }
  }
}
```

## How It Works

1. An observer on `sales_quote_collect_totals_after` iterates visible cart items and attaches discount messages via `AbstractItem::addMessage()`
2. Messages are recalculated on every `collectTotals()` call — no persistent storage needed
3. A plugin on `Magento\Checkout\CustomerData\AbstractItem::getItemData()` adds `regular_price`, `regular_price_formatted`, and `has_discount` to section data for the cart drawer
4. The Hyvä template override renders strikethrough pricing when the regular price differs from the final price

## License

MIT

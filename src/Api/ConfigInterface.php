<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Api;

interface ConfigInterface
{
    public const XML_PATH_ENABLE_MESSAGES_CART = 'checkout_discount_display/general/enable_messages_cart';
    public const XML_PATH_ENABLE_MESSAGES_MINICART = 'checkout_discount_display/general/enable_messages_minicart';
    public const XML_PATH_ENABLE_MESSAGES_GRAPHQL = 'checkout_discount_display/general/enable_messages_graphql';

    /**
     * Check if discount messages are enabled on the cart page
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCartMessagesEnabled(?int $storeId = null): bool;

    /**
     * Check if discount messages are enabled in the mini cart
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isMiniCartMessagesEnabled(?int $storeId = null): bool;

    /**
     * Check if discount messages are enabled in GraphQL
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isGraphqlMessagesEnabled(?int $storeId = null): bool;
}

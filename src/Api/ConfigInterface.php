<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Api;

interface ConfigInterface
{
    public const XML_PATH_ENABLE_MESSAGES = 'checkout_discount_display/general/enable_messages';
    public const XML_PATH_ENABLE_STRIKETHROUGH = 'checkout_discount_display/general/enable_strikethrough';

    /**
     * Check if discount messages are enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isMessagesEnabled(?int $storeId = null): bool;

    /**
     * Check if strikethrough pricing is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isStrikethroughEnabled(?int $storeId = null): bool;
}

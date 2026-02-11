<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PixelPerfect\CheckoutDiscountDisplay\Api\ConfigInterface;

readonly class Config implements ConfigInterface
{
    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isCartMessagesEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_MESSAGES_CART,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function isMiniCartMessagesEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_MESSAGES_MINICART,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function isGraphqlMessagesEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_MESSAGES_GRAPHQL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}

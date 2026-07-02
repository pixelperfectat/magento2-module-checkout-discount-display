<?php declare(strict_types=1);

namespace PixelPerfect\CheckoutDiscountDisplay\Test\Unit\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PixelPerfect\CheckoutDiscountDisplay\Api\ConfigInterface;
use PixelPerfect\CheckoutDiscountDisplay\Service\Config;

class ConfigTest extends TestCase
{
    private ScopeConfigInterface&MockObject $scopeConfig;
    private Config $config;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->config = new Config($this->scopeConfig);
    }

    public function testIsCartMessagesEnabledReturnsTrueWhenEnabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_MESSAGES_CART, 'store', 5)
            ->willReturn(true);

        $this->assertTrue($this->config->isCartMessagesEnabled(5));
    }

    public function testIsCartMessagesEnabledReturnsFalseWhenDisabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_MESSAGES_CART, 'store', 5)
            ->willReturn(false);

        $this->assertFalse($this->config->isCartMessagesEnabled(5));
    }

    public function testIsMiniCartMessagesEnabledReturnsTrueWhenEnabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_MESSAGES_MINICART, 'store', 5)
            ->willReturn(true);

        $this->assertTrue($this->config->isMiniCartMessagesEnabled(5));
    }

    public function testIsMiniCartMessagesEnabledReturnsFalseWhenDisabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_MESSAGES_MINICART, 'store', 5)
            ->willReturn(false);

        $this->assertFalse($this->config->isMiniCartMessagesEnabled(5));
    }

    public function testIsGraphqlMessagesEnabledReturnsTrueWhenEnabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_MESSAGES_GRAPHQL, 'store', 5)
            ->willReturn(true);

        $this->assertTrue($this->config->isGraphqlMessagesEnabled(5));
    }

    public function testIsGraphqlMessagesEnabledReturnsFalseWhenDisabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_MESSAGES_GRAPHQL, 'store', 5)
            ->willReturn(false);

        $this->assertFalse($this->config->isGraphqlMessagesEnabled(5));
    }
}

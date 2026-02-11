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

    public function testIsMessagesEnabledReturnsTrueWhenEnabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_MESSAGES, 'store', 5)
            ->willReturn(true);

        $this->assertTrue($this->config->isMessagesEnabled(5));
    }

    public function testIsMessagesEnabledReturnsFalseWhenDisabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_MESSAGES, 'store', 5)
            ->willReturn(false);

        $this->assertFalse($this->config->isMessagesEnabled(5));
    }

    public function testIsStrikethroughEnabledReturnsTrueWhenEnabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_STRIKETHROUGH, 'store', 5)
            ->willReturn(true);

        $this->assertTrue($this->config->isStrikethroughEnabled(5));
    }

    public function testIsStrikethroughEnabledReturnsFalseWhenDisabled(): void
    {
        $this->scopeConfig->method('isSetFlag')
            ->with(ConfigInterface::XML_PATH_ENABLE_STRIKETHROUGH, 'store', 5)
            ->willReturn(false);

        $this->assertFalse($this->config->isStrikethroughEnabled(5));
    }
}

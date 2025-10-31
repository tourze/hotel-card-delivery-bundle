<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Service;

use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

/**
 * Mock 的 LinkGenerator 实现，用于测试
 */
class MockLinkGenerator implements LinkGeneratorInterface
{
    public function getCurdListPage(string $entityClass): string
    {
        // HotelCardDeliveryBundle entities
        if ('Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery' === $entityClass) {
            return '/admin/keycarddelivery/list';
        }

        if ('Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost' === $entityClass) {
            return '/admin/deliverycost/list';
        }

        // DeliverOrderBundle entities
        if ('DeliverOrderBundle\Entity\DeliverOrder' === $entityClass) {
            return '/admin/deliver/order';
        }

        if ('DeliverOrderBundle\Entity\DeliverStock' === $entityClass) {
            return '/admin/deliver/stock';
        }

        // WeChatBotBundle entities
        if ('Tourze\WechatBotBundle\Entity\WeChatApiAccount' === $entityClass) {
            return '/admin/wechat-bot/api-account';
        }

        if ('Tourze\WechatBotBundle\Entity\WeChatAccount' === $entityClass) {
            return '/admin/wechat-bot/account';
        }

        // Default fallback
        return '/admin/hotel-card-delivery';
    }

    public function extractEntityFqcn(string $url): ?string
    {
        return null;
    }

    public function setDashboard(string $dashboardControllerFqcn): void
    {
        // Mock implementation - no-op for testing
    }
}

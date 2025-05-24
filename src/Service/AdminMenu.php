<?php

namespace Tourze\HotelCardDeliveryBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;

/**
 * 房卡配送管理菜单服务
 */
class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (!$item->getChild('房卡配送')) {
            $item->addChild('房卡配送');
        }

        $deliveryMenu = $item->getChild('房卡配送');

        // 配送任务管理菜单
        $deliveryMenu->addChild('配送任务')
            ->setUri($this->linkGenerator->getCurdListPage(KeyCardDelivery::class))
            ->setAttribute('icon', 'fas fa-truck');

        // 配送费用管理菜单
        $deliveryMenu->addChild('配送费用')
            ->setUri($this->linkGenerator->getCurdListPage(DeliveryCost::class))
            ->setAttribute('icon', 'fas fa-calculator');
    }
}

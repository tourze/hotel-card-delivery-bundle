<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;

/**
 * 测试实体工厂类
 *
 * 为了避免外键依赖问题，我们通过直接插入数据库绕过 Doctrine 的外键检查
 */
class TestEntityFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * 创建测试用的 KeyCardDelivery 实体
     *
     * 使用原生 SQL 插入来绕过外键检查
     */
    public function createKeyCardDelivery(array $data = []): KeyCardDelivery
    {
        $connection = $this->entityManager->getConnection();
        
        // 默认数据
        $defaultData = [
            'order_id' => 1, // 假的外键ID
            'hotel_id' => 1, // 假的外键ID
            'room_count' => 2,
            'delivery_time' => '2024-12-31 14:00:00',
            'status' => DeliveryStatusEnum::PENDING->value,
            'fee' => '25.00',
            'receipt_photo_url' => null,
            'completed_time' => null,
            'remark' => null,
            'create_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'update_time' => null,
        ];
        
        $data = array_merge($defaultData, $data);
        
        // 插入原始数据，绕过外键检查
        $connection->executeStatement(
            'INSERT INTO key_card_delivery (order_id, hotel_id, room_count, delivery_time, status, fee, receipt_photo_url, completed_time, remark, create_time, update_time) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['order_id'],
                $data['hotel_id'], 
                $data['room_count'],
                $data['delivery_time'],
                $data['status'],
                $data['fee'],
                $data['receipt_photo_url'],
                $data['completed_time'],
                $data['remark'],
                $data['create_time'],
                $data['update_time'],
            ]
        );
        
        $id = $connection->lastInsertId();
        
        // 通过 ID 获取实体，让 Doctrine 正确管理
        return $this->entityManager->find(KeyCardDelivery::class, $id);
    }

    /**
     * 创建测试用的 DeliveryCost 实体
     */
    public function createDeliveryCost(KeyCardDelivery $delivery, array $data = []): DeliveryCost
    {
        $cost = new DeliveryCost();
        $cost->setDelivery($delivery);
        $cost->setBaseCost($data['baseCost'] ?? '10.00');
        $cost->setDistanceCost($data['distanceCost'] ?? '5.00');
        $cost->setUrgencyCost($data['urgencyCost'] ?? '3.00');
        $cost->setExtraCost($data['extraCost'] ?? '2.00');
        $cost->setDistance($data['distance'] ?? 2.5);
        $cost->setSettled($data['settled'] ?? false);
        $cost->setRemarks($data['remarks'] ?? null);
        
        return $cost;
    }

    /**
     * 使用简化方式创建 KeyCardDelivery（仅用于不需要持久化的测试）
     */
    public function createSimpleKeyCardDelivery(array $data = []): KeyCardDelivery
    {
        $delivery = new KeyCardDelivery();
        $delivery->setRoomCount($data['roomCount'] ?? 2);
        $delivery->setFee($data['fee'] ?? '25.00');
        $delivery->setDeliveryTime($data['deliveryTime'] ?? new \DateTime('2024-12-31 14:00:00'));
        $delivery->setStatus($data['status'] ?? DeliveryStatusEnum::PENDING);
        $delivery->setReceiptPhotoUrl($data['receiptPhotoUrl'] ?? null);
        $delivery->setCompletedTime($data['completedTime'] ?? null);
        $delivery->setRemark($data['remark'] ?? null);
        
        return $delivery;
    }
}
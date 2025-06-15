<?php

namespace Tourze\HotelCardDeliveryBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\HotelAgentBundle\Entity\Order;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;
use Tourze\HotelProfileBundle\Entity\Hotel;

/**
 * @extends ServiceEntityRepository<KeyCardDelivery>
 *
 * @method KeyCardDelivery|null find($id, $lockMode = null, $lockVersion = null)
 * @method KeyCardDelivery|null findOneBy(array $criteria, array $orderBy = null)
 * @method KeyCardDelivery[]    findAll()
 * @method KeyCardDelivery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KeyCardDeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KeyCardDelivery::class);
    }

    /**
     * 查找待分配的配送任务
     */
    public function findPendingDeliveries(): array
    {
        return $this->createQueryBuilder('kcd')
            ->andWhere('kcd.status = :status')
            ->setParameter('status', DeliveryStatusEnum::PENDING)
            ->orderBy('kcd.deliveryTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定酒店的配送任务
     */
    public function findByHotel(Hotel $hotel): array
    {
        return $this->createQueryBuilder('kcd')
            ->andWhere('kcd.hotel = :hotel')
            ->setParameter('hotel', $hotel)
            ->orderBy('kcd.deliveryTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定订单的配送任务
     */
    public function findByOrder(Order $order): array
    {
        return $this->createQueryBuilder('kcd')
            ->andWhere('kcd.order = :order')
            ->setParameter('order', $order)
            ->orderBy('kcd.deliveryTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定状态的配送任务
     */
    public function findByStatus(DeliveryStatusEnum $status): array
    {
        return $this->createQueryBuilder('kcd')
            ->andWhere('kcd.status = :status')
            ->setParameter('status', $status)
            ->orderBy('kcd.deliveryTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定日期的配送任务
     */
    public function findByDeliveryDate(\DateTimeInterface $date): array
    {
        $startOfDay = new \DateTime($date->format('Y-m-d') . ' 00:00:00');
        $endOfDay = new \DateTime($date->format('Y-m-d') . ' 23:59:59');
        
        return $this->createQueryBuilder('kcd')
            ->andWhere('kcd.deliveryTime >= :startOfDay')
            ->andWhere('kcd.deliveryTime <= :endOfDay')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->orderBy('kcd.deliveryTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 统计指定日期的配送数量
     */
    public function countByDeliveryDate(\DateTimeInterface $date): int
    {
        $startOfDay = new \DateTime($date->format('Y-m-d') . ' 00:00:00');
        $endOfDay = new \DateTime($date->format('Y-m-d') . ' 23:59:59');
        
        return $this->createQueryBuilder('kcd')
            ->select('COUNT(kcd.id)')
            ->andWhere('kcd.deliveryTime >= :startOfDay')
            ->andWhere('kcd.deliveryTime <= :endOfDay')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * 查找需要尽快处理的配送任务（按时间紧急程度排序）
     */
    public function findUrgentDeliveries(?\DateTimeInterface $today = null): array
    {
        $today = $today ?? new \DateTime();
        
        return $this->createQueryBuilder('kcd')
            ->andWhere('kcd.status = :status')
            ->andWhere('kcd.deliveryTime >= :today')
            ->setParameter('status', DeliveryStatusEnum::PENDING)
            ->setParameter('today', $today)
            ->orderBy('kcd.deliveryTime', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找今日待配送的任务
     */
    public function findTodayDeliveries(): array
    {
        $today = new \DateTime();
        $startOfDay = new \DateTime($today->format('Y-m-d') . ' 00:00:00');
        $endOfDay = new \DateTime($today->format('Y-m-d') . ' 23:59:59');
        
        return $this->createQueryBuilder('kcd')
            ->andWhere('kcd.deliveryTime >= :startOfDay')
            ->andWhere('kcd.deliveryTime <= :endOfDay')
            ->andWhere('kcd.status IN (:statuses)')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('statuses', [DeliveryStatusEnum::PENDING, DeliveryStatusEnum::ASSIGNED, DeliveryStatusEnum::IN_PROGRESS])
            ->orderBy('kcd.deliveryTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找一段时间内的配送费用总和
     */
    public function getTotalFeeInPeriod(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        $result = $this->createQueryBuilder('kcd')
            ->select('SUM(kcd.fee)')
            ->andWhere('kcd.deliveryTime >= :startDate')
            ->andWhere('kcd.deliveryTime <= :endDate')
            ->andWhere('kcd.status = :status')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('status', DeliveryStatusEnum::COMPLETED)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $result ? (float)$result : 0.0;
    }
}

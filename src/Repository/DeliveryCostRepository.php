<?php

namespace Tourze\HotelCardDeliveryBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;

/**
 * 配送费用仓库类
 *
 * @extends ServiceEntityRepository<DeliveryCost>
 *
 * @method DeliveryCost|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryCost|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryCost[]    findAll()
 * @method DeliveryCost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryCostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryCost::class);
    }

    /**
     * 保存配送费用实体
     */
    public function save(DeliveryCost $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 删除配送费用实体
     */
    public function remove(DeliveryCost $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * 根据配送任务查找配送费用
     */
    public function findByDelivery(KeyCardDelivery $delivery): ?DeliveryCost
    {
        return $this->createQueryBuilder('dc')
            ->andWhere('dc.delivery = :delivery')
            ->setParameter('delivery', $delivery)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 查找未结算的配送费用
     */
    public function findUnsettled(): array
    {
        return $this->createQueryBuilder('dc')
            ->andWhere('dc.settled = :settled')
            ->setParameter('settled', false)
            ->orderBy('dc.createTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定时间段内的配送费用
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('dc')
            ->andWhere('dc.createTime >= :start')
            ->andWhere('dc.createTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('dc.createTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 计算指定时间段内的总配送费用
     */
    public function calculateTotalCostByPeriod(
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): float {
        $result = $this->createQueryBuilder('dc')
            ->select('SUM(dc.totalCost) as totalCost')
            ->andWhere('dc.createTime >= :start')
            ->andWhere('dc.createTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
        
        return (float)($result ?? 0);
    }
}

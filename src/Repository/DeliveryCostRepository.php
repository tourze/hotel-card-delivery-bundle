<?php

namespace Tourze\HotelCardDeliveryBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * 配送费用仓库类
 *
 * @extends ServiceEntityRepository<DeliveryCost>
 */
#[AsRepository(entityClass: DeliveryCost::class)]
class DeliveryCostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryCost::class);
    }

    /**
     * 根据配送任务查找配送费用
     */
    public function findByDelivery(KeyCardDelivery $delivery): ?DeliveryCost
    {
        $result = $this->createQueryBuilder('dc')
            ->andWhere('dc.delivery = :delivery')
            ->setParameter('delivery', $delivery)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        assert($result instanceof DeliveryCost || null === $result);

        return $result;
    }

    /**
     * 查找未结算的配送费用
     *
     * @return DeliveryCost[]
     */
    public function findUnsettled(): array
    {
        $result = $this->createQueryBuilder('dc')
            ->andWhere('dc.settled = :settled')
            ->setParameter('settled', false)
            ->orderBy('dc.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));

        /** @var list<DeliveryCost> */
        return $result;
    }

    /**
     * 查找指定时间段内的配送费用
     *
     * @return DeliveryCost[]
     */
    public function findByDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $result = $this->createQueryBuilder('dc')
            ->andWhere('dc.createTime >= :start')
            ->andWhere('dc.createTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('dc.createTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));

        /** @var list<DeliveryCost> */
        return $result;
    }

    /**
     * 计算指定时间段内的总配送费用
     */
    public function calculateTotalCostByPeriod(
        \DateTimeInterface $start,
        \DateTimeInterface $end,
    ): float {
        $result = $this->createQueryBuilder('dc')
            ->select('SUM(dc.totalCost) as totalCost')
            ->andWhere('dc.createTime >= :start')
            ->andWhere('dc.createTime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (float) ($result ?? 0);
    }

    public function save(DeliveryCost $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DeliveryCost $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

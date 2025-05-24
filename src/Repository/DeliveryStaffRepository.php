<?php

namespace Tourze\HotelCardDeliveryBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryStaff;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStaffStatusEnum;

/**
 * @extends ServiceEntityRepository<DeliveryStaff>
 *
 * @method DeliveryStaff|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryStaff|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryStaff[]    findAll()
 * @method DeliveryStaff[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryStaffRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryStaff::class);
    }

    /**
     * 查找当前可用的配送员
     */
    public function findAvailableStaff(): array
    {
        return $this->createQueryBuilder('ds')
            ->andWhere('ds.status = :status')
            ->setParameter('status', DeliveryStaffStatusEnum::IDLE)
            ->orderBy('ds.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找符合指定工作量的配送员
     */
    public function findStaffByWorkloadLimit(int $limit): array
    {
        return $this->createQueryBuilder('ds')
            ->andWhere('ds.workloadLimit >= :limit')
            ->andWhere('ds.status = :status')
            ->setParameter('limit', $limit)
            ->setParameter('status', DeliveryStaffStatusEnum::IDLE)
            ->orderBy('ds.workloadLimit', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据状态查找配送员
     */
    public function findByStatus(DeliveryStaffStatusEnum $status): array
    {
        return $this->createQueryBuilder('ds')
            ->andWhere('ds.status = :status')
            ->setParameter('status', $status)
            ->orderBy('ds.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据名称搜索配送员
     */
    public function findByNameLike(string $name): array
    {
        return $this->createQueryBuilder('ds')
            ->andWhere('ds.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('ds.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据电话搜索配送员
     */
    public function findByPhoneLike(string $phone): array
    {
        return $this->createQueryBuilder('ds')
            ->andWhere('ds.phone LIKE :phone')
            ->setParameter('phone', '%' . $phone . '%')
            ->orderBy('ds.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找工作量低于指定值的配送员
     */
    public function findByLowWorkload(int $threshold = 5): array
    {
        $today = new \DateTime();
        $date = $today->format('Y-m-d');

        $qb = $this->createQueryBuilder('ds')
            ->leftJoin('ds.deliveries', 'd')
            ->andWhere('ds.status = :status')
            ->setParameter('status', DeliveryStaffStatusEnum::IDLE)
            ->groupBy('ds.id')
            ->having('COUNT(d.id) < :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('COUNT(d.id)', 'ASC');

        // 添加日期筛选条件到JOIN
        $qb->andWhere('(d.deliveryTime IS NULL OR DATE(d.deliveryTime) = :date)')
            ->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找可接受工作的配送员
     */
    public function findStaffForTaskAssignment(\DateTime $deliveryDate = null): array
    {
        $deliveryDate = $deliveryDate ?? new \DateTime();

        // 只查找当前空闲的配送员
        return $this->createQueryBuilder('ds')
            ->andWhere('ds.status = :status')
            ->setParameter('status', DeliveryStaffStatusEnum::IDLE)
            ->orderBy('ds.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找最近创建的配送员
     */
    public function findRecentlyCreated(int $limit = 10): array
    {
        return $this->createQueryBuilder('ds')
            ->orderBy('ds.createTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}

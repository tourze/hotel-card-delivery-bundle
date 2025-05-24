<?php

namespace Tourze\HotelCardDeliveryBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository;

#[ORM\Entity(repositoryClass: DeliveryCostRepository::class)]
#[ORM\Table(name: 'delivery_cost', options: ['comment' => '房卡配送费用表'])]
#[ORM\Index(name: 'delivery_cost_idx_delivery', columns: ['delivery_id'])]
class DeliveryCost implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: KeyCardDelivery::class)]
    #[ORM\JoinColumn(name: 'delivery_id', referencedColumnName: 'id', nullable: false)]
    private KeyCardDelivery $delivery;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '配送基础费用'])]
    #[Assert\PositiveOrZero]
    private string $baseCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '配送距离费用'])]
    #[Assert\PositiveOrZero]
    private string $distanceCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '配送加急费用'])]
    #[Assert\PositiveOrZero]
    private string $urgencyCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '其他额外费用'])]
    #[Assert\PositiveOrZero]
    private string $extraCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '总费用'])]
    #[Assert\PositiveOrZero]
    private string $totalCost = '0.00';

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => '配送距离（公里）'])]
    #[Assert\PositiveOrZero]
    private float $distance = 0.0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '结算时间'])]
    private ?\DateTimeInterface $settlementTime = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否已结算'])]
    private bool $settled = false;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remarks = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateTime = null;

    #[CreatedByColumn]
    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?int $createdBy = null;

    public function __toString(): string
    {
        $orderNo = 'New';
        
        if ($this->delivery !== null && $this->delivery->getOrder() !== null) {
            $orderNo = $this->delivery->getOrder()->getOrderNo() ?? 'New';
        }
        
        return sprintf('配送费用 #%d - %s', $this->id ?? 0, $orderNo);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDelivery(): KeyCardDelivery
    {
        return $this->delivery;
    }

    public function setDelivery(KeyCardDelivery $delivery): self
    {
        $this->delivery = $delivery;
        return $this;
    }

    public function getBaseCost(): string
    {
        return $this->baseCost;
    }

    public function setBaseCost(string $baseCost): self
    {
        $this->baseCost = $baseCost;
        $this->calculateTotalCost();
        return $this;
    }

    public function getDistanceCost(): string
    {
        return $this->distanceCost;
    }

    public function setDistanceCost(string $distanceCost): self
    {
        $this->distanceCost = $distanceCost;
        $this->calculateTotalCost();
        return $this;
    }

    public function getUrgencyCost(): string
    {
        return $this->urgencyCost;
    }

    public function setUrgencyCost(string $urgencyCost): self
    {
        $this->urgencyCost = $urgencyCost;
        $this->calculateTotalCost();
        return $this;
    }

    public function getExtraCost(): string
    {
        return $this->extraCost;
    }

    public function setExtraCost(string $extraCost): self
    {
        $this->extraCost = $extraCost;
        $this->calculateTotalCost();
        return $this;
    }

    public function getTotalCost(): string
    {
        return $this->totalCost;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): self
    {
        $this->distance = $distance;
        return $this;
    }

    public function getSettlementTime(): ?\DateTimeInterface
    {
        return $this->settlementTime;
    }

    public function setSettlementTime(?\DateTimeInterface $settlementTime): self
    {
        $this->settlementTime = $settlementTime;
        return $this;
    }

    public function isSettled(): bool
    {
        return $this->settled;
    }

    public function setSettled(bool $settled): self
    {
        $this->settled = $settled;
        if ($settled && $this->settlementTime === null) {
            $this->settlementTime = new \DateTime();
        }
        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;
        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    /**
     * 计算总费用
     */
    private function calculateTotalCost(): void
    {
        $total = (float)$this->baseCost +
            (float)$this->distanceCost +
            (float)$this->urgencyCost +
            (float)$this->extraCost;
        $this->totalCost = (string)round($total, 2);
    }

    /**
     * 计算距离费用
     * 
     * 根据距离计算费用，每公里的费率可在调用时指定
     */
    public function calculateDistanceCost(float $ratePerKm = 2.0): self
    {
        $distanceCost = round($this->distance * $ratePerKm, 2);
        $this->distanceCost = (string)$distanceCost;
        $this->calculateTotalCost();
        return $this;
    }

    /**
     * 标记为已结算
     */
    public function markAsSettled(): self
    {
        $this->settled = true;
        $this->settlementTime = new \DateTime();
        return $this;
    }

    public function setCreateTime(?\DateTimeInterface $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }
}

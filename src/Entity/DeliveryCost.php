<?php

namespace Tourze\HotelCardDeliveryBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;
use Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository;

#[ORM\Entity(repositoryClass: DeliveryCostRepository::class)]
#[ORM\Table(name: 'delivery_cost', options: ['comment' => '房卡配送费用表'])]
class DeliveryCost implements \Stringable
{
    use TimestampableAware;
    use CreatedByAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: KeyCardDelivery::class)]
    #[ORM\JoinColumn(name: 'delivery_id', referencedColumnName: 'id', nullable: false)]
    private KeyCardDelivery $delivery;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '配送基础费用'])]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 255)]
    private string $baseCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '配送距离费用'])]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 255)]
    private string $distanceCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '配送加急费用'])]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 255)]
    private string $urgencyCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '其他额外费用'])]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 255)]
    private string $extraCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '总费用'])]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 255)]
    private string $totalCost = '0.00';

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => '配送距离（公里）'])]
    #[Assert\PositiveOrZero]
    private float $distance = 0.0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '结算时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeImmutable $settlementTime = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否已结算'])]
    #[Assert\Type(type: 'bool')]
    private bool $settled = false;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 65535)]
    private ?string $remarks = null;

    public function __toString(): string
    {
        $orderNo = 'New';

        if (null !== $this->delivery->getOrder()) {
            $orderNo = $this->delivery->getOrder()->getOrderNo();
        }

        return sprintf('配送费用 #%d - %s', $this->id, $orderNo);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDelivery(): KeyCardDelivery
    {
        return $this->delivery;
    }

    public function setDelivery(KeyCardDelivery $delivery): void
    {
        $this->delivery = $delivery;
    }

    public function getBaseCost(): string
    {
        return $this->baseCost;
    }

    public function setBaseCost(string $baseCost): void
    {
        $this->baseCost = $baseCost;
        $this->calculateTotalCost();
    }

    public function getDistanceCost(): string
    {
        return $this->distanceCost;
    }

    public function setDistanceCost(string $distanceCost): void
    {
        $this->distanceCost = $distanceCost;
        $this->calculateTotalCost();
    }

    public function getUrgencyCost(): string
    {
        return $this->urgencyCost;
    }

    public function setUrgencyCost(string $urgencyCost): void
    {
        $this->urgencyCost = $urgencyCost;
        $this->calculateTotalCost();
    }

    public function getExtraCost(): string
    {
        return $this->extraCost;
    }

    public function setExtraCost(string $extraCost): void
    {
        $this->extraCost = $extraCost;
        $this->calculateTotalCost();
    }

    public function getTotalCost(): string
    {
        return $this->totalCost;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }

    public function getSettlementTime(): ?\DateTimeImmutable
    {
        return $this->settlementTime;
    }

    public function setSettlementTime(?\DateTimeInterface $settlementTime): void
    {
        $this->settlementTime = $settlementTime instanceof \DateTimeImmutable ? $settlementTime : (null !== $settlementTime ? \DateTimeImmutable::createFromInterface($settlementTime) : null);
    }

    public function isSettled(): bool
    {
        return $this->settled;
    }

    public function setSettled(bool $settled): void
    {
        $this->settled = $settled;
        if ($settled && null === $this->settlementTime) {
            $this->settlementTime = new \DateTimeImmutable();
        }
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): void
    {
        $this->remarks = $remarks;
    }

    /**
     * 计算总费用
     */
    private function calculateTotalCost(): void
    {
        $total = (float) $this->baseCost +
            (float) $this->distanceCost +
            (float) $this->urgencyCost +
            (float) $this->extraCost;
        $this->totalCost = number_format(round($total, 2), 2, '.', '');
    }

    /**
     * 计算距离费用
     *
     * 根据距离计算费用，每公里的费率可在调用时指定
     */
    public function calculateDistanceCost(float $ratePerKm = 2.0): self
    {
        $distanceCost = round($this->distance * $ratePerKm, 2);
        $this->distanceCost = number_format($distanceCost, 2, '.', '');
        $this->calculateTotalCost();

        return $this;
    }

    /**
     * 标记为已结算
     */
    public function markAsSettled(): self
    {
        $this->settled = true;
        $this->settlementTime = new \DateTimeImmutable();

        return $this;
    }
}

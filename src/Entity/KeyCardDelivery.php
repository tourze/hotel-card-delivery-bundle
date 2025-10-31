<?php

namespace Tourze\HotelCardDeliveryBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\HotelAgentBundle\Entity\Order;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;
use Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository;
use Tourze\HotelProfileBundle\Entity\Hotel;

#[ORM\Entity(repositoryClass: KeyCardDeliveryRepository::class)]
#[ORM\Table(name: 'key_card_delivery', options: ['comment' => '房卡配送任务表'])]
class KeyCardDelivery implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'order_id', nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'hotel_id', nullable: false)]
    private ?Hotel $hotel = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '房卡数量'])]
    #[Assert\PositiveOrZero]
    private int $roomCount = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '配送时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeImmutable $deliveryTime = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 20, enumType: DeliveryStatusEnum::class, options: ['comment' => '配送状态'])]
    #[Assert\Choice(callback: [DeliveryStatusEnum::class, 'cases'])]
    private DeliveryStatusEnum $status = DeliveryStatusEnum::PENDING;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '配送费用'])]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 255)]
    private string $fee = '0.00';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '交接凭证照片URL'])]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private ?string $receiptPhotoUrl = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeImmutable $completedTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

    public function __toString(): string
    {
        $orderNo = null !== $this->order ? $this->order->getOrderNo() : 'Unknown';
        $hotelName = null !== $this->hotel ? $this->hotel->getName() : 'Unknown';

        return sprintf('房卡配送: %s - %s', $orderNo, $hotelName);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): void
    {
        $this->order = $order;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): void
    {
        $this->hotel = $hotel;
    }

    public function getRoomCount(): int
    {
        return $this->roomCount;
    }

    public function setRoomCount(int $roomCount): void
    {
        $this->roomCount = $roomCount;
    }

    public function getDeliveryTime(): ?\DateTimeImmutable
    {
        return $this->deliveryTime;
    }

    public function setDeliveryTime(?\DateTimeInterface $deliveryTime): void
    {
        $this->deliveryTime = $deliveryTime instanceof \DateTimeImmutable ? $deliveryTime : (null !== $deliveryTime ? \DateTimeImmutable::createFromInterface($deliveryTime) : null);
    }

    public function getStatus(): DeliveryStatusEnum
    {
        return $this->status;
    }

    public function setStatus(DeliveryStatusEnum $status): void
    {
        $this->status = $status;
    }

    public function getFee(): string
    {
        return $this->fee;
    }

    public function setFee(string $fee): void
    {
        $this->fee = $fee;
    }

    public function getReceiptPhotoUrl(): ?string
    {
        return $this->receiptPhotoUrl;
    }

    public function setReceiptPhotoUrl(?string $receiptPhotoUrl): void
    {
        $this->receiptPhotoUrl = $receiptPhotoUrl;
    }

    public function getCompletedTime(): ?\DateTimeImmutable
    {
        return $this->completedTime;
    }

    public function setCompletedTime(?\DateTimeInterface $completedTime): void
    {
        $this->completedTime = $completedTime instanceof \DateTimeImmutable ? $completedTime : (null !== $completedTime ? \DateTimeImmutable::createFromInterface($completedTime) : null);
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    /**
     * 标记为配送中
     */
    public function markAsInProgress(): self
    {
        $this->status = DeliveryStatusEnum::IN_PROGRESS;

        return $this;
    }

    /**
     * 标记为已完成
     */
    public function markAsCompleted(string $receiptPhotoUrl): self
    {
        $this->status = DeliveryStatusEnum::COMPLETED;
        $this->receiptPhotoUrl = $receiptPhotoUrl;
        $this->completedTime = new \DateTimeImmutable();

        return $this;
    }

    /**
     * 标记为已取消
     */
    public function markAsCancelled(string $reason): self
    {
        $this->status = DeliveryStatusEnum::CANCELLED;
        $this->remark = $reason;

        return $this;
    }

    /**
     * 标记为异常
     */
    public function markAsException(string $reason): self
    {
        $this->status = DeliveryStatusEnum::EXCEPTION;
        $this->remark = $reason;

        return $this;
    }

    /**
     * 计算配送费用
     */
    public function calculateFee(float $perCardFee = 100.00): self
    {
        $this->fee = (string) ($this->roomCount * $perCardFee);

        return $this;
    }

    /**
     * 判断是否已经完成
     */
    public function isCompleted(): bool
    {
        return DeliveryStatusEnum::COMPLETED === $this->status;
    }

    /**
     * 判断是否已经取消
     */
    public function isCancelled(): bool
    {
        return DeliveryStatusEnum::CANCELLED === $this->status;
    }

    /**
     * 判断是否可以开始配送
     */
    public function canStartDelivery(): bool
    {
        return DeliveryStatusEnum::PENDING === $this->status;
    }
}

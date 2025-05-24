<?php

namespace Tourze\HotelCardDeliveryBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\HotelAgentBundle\Entity\Order;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;
use Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository;
use Tourze\HotelProfileBundle\Entity\Hotel;

#[ORM\Entity(repositoryClass: KeyCardDeliveryRepository::class)]
#[ORM\Table(name: 'key_card_delivery', options: ['comment' => '房卡配送任务表'])]
#[ORM\Index(name: 'key_card_delivery_idx_order_id', columns: ['order_id'])]
#[ORM\Index(name: 'key_card_delivery_idx_hotel_id', columns: ['hotel_id'])]
#[ORM\Index(name: 'key_card_delivery_idx_staff_id', columns: ['delivery_staff_id'])]
#[ORM\Index(name: 'key_card_delivery_idx_status', columns: ['status'])]
class KeyCardDelivery implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'order_id', nullable: false)]
    private ?Order $order = null;
    
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'hotel_id', nullable: false)]
    private ?Hotel $hotel = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '房卡数量'])]
    private int $roomCount = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '配送时间'])]
    private ?\DateTimeInterface $deliveryTime = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'delivery_staff_id', nullable: true)]
    private ?DeliveryStaff $deliveryStaff = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: DeliveryStatusEnum::class, options: ['comment' => '配送状态'])]
    private DeliveryStatusEnum $status = DeliveryStatusEnum::PENDING;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '配送费用'])]
    private string $fee = '0.00';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '交接凭证照片URL'])]
    private ?string $receiptPhotoUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeInterface $completedTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remark = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateTime = null;

    public function __toString(): string
    {
        $orderNo = $this->order ? $this->order->getOrderNo() : 'Unknown';
        $hotelName = $this->hotel ? $this->hotel->getName() : 'Unknown';
        
        return sprintf('房卡配送: %s - %s', $orderNo, $hotelName);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): self
    {
        $this->hotel = $hotel;
        return $this;
    }

    public function getRoomCount(): int
    {
        return $this->roomCount;
    }

    public function setRoomCount(int $roomCount): self
    {
        $this->roomCount = $roomCount;
        return $this;
    }

    public function getDeliveryTime(): ?\DateTimeInterface
    {
        return $this->deliveryTime;
    }

    public function setDeliveryTime(?\DateTimeInterface $deliveryTime): self
    {
        $this->deliveryTime = $deliveryTime;
        return $this;
    }

    public function getDeliveryStaff(): ?DeliveryStaff
    {
        return $this->deliveryStaff;
    }

    public function setDeliveryStaff(?DeliveryStaff $deliveryStaff): self
    {
        $this->deliveryStaff = $deliveryStaff;
        return $this;
    }

    public function getStatus(): DeliveryStatusEnum
    {
        return $this->status;
    }

    public function setStatus(DeliveryStatusEnum $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getFee(): string
    {
        return $this->fee;
    }

    public function setFee(string $fee): self
    {
        $this->fee = $fee;
        return $this;
    }

    public function getReceiptPhotoUrl(): ?string
    {
        return $this->receiptPhotoUrl;
    }

    public function setReceiptPhotoUrl(?string $receiptPhotoUrl): self
    {
        $this->receiptPhotoUrl = $receiptPhotoUrl;
        return $this;
    }

    public function getCompletedTime(): ?\DateTimeInterface
    {
        return $this->completedTime;
    }

    public function setCompletedTime(?\DateTimeInterface $completedTime): self
    {
        $this->completedTime = $completedTime;
        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;
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

    /**
     * 分配配送员
     */
    public function assignDeliveryStaff(DeliveryStaff $deliveryStaff): self
    {
        $this->deliveryStaff = $deliveryStaff;
        $this->status = DeliveryStatusEnum::ASSIGNED;
        return $this;
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
        $this->completedTime = new \DateTime();
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
        $this->fee = (string)($this->roomCount * $perCardFee);
        return $this;
    }

    /**
     * 判断是否已经完成
     */
    public function isCompleted(): bool
    {
        return $this->status === DeliveryStatusEnum::COMPLETED;
    }

    /**
     * 判断是否已经取消
     */
    public function isCancelled(): bool
    {
        return $this->status === DeliveryStatusEnum::CANCELLED;
    }

    /**
     * 判断是否可以分配配送员
     */
    public function canAssignStaff(): bool
    {
        return $this->status === DeliveryStatusEnum::PENDING;
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
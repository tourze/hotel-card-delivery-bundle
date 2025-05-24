<?php

namespace Tourze\HotelCardDeliveryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStaffStatusEnum;
use Tourze\HotelCardDeliveryBundle\Repository\DeliveryStaffRepository;

#[ORM\Entity(repositoryClass: DeliveryStaffRepository::class)]
#[ORM\Table(name: 'delivery_staff', options: ['comment' => '配送员信息表'])]
#[ORM\Index(name: 'delivery_staff_idx_status', columns: ['status'])]
class DeliveryStaff implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '姓名'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '电话'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $phone = '';

    #[ORM\Column(type: Types::STRING, length: 20, enumType: DeliveryStaffStatusEnum::class, options: ['comment' => '状态'])]
    private DeliveryStaffStatusEnum $status = DeliveryStaffStatusEnum::IDLE;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '每日工作量上限', 'default' => 10])]
    private int $workloadLimit = 10;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updateTime = null;

    #[ORM\OneToMany(mappedBy: 'deliveryStaff', targetEntity: KeyCardDelivery::class, fetch: 'EXTRA_LAZY')]
    private Collection $deliveries;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getStatus(): DeliveryStaffStatusEnum
    {
        return $this->status;
    }

    public function setStatus(DeliveryStaffStatusEnum $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getWorkloadLimit(): int
    {
        return $this->workloadLimit;
    }

    public function setWorkloadLimit(int $workloadLimit): self
    {
        $this->workloadLimit = $workloadLimit;
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
     * @return Collection<int, KeyCardDelivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(KeyCardDelivery $delivery): self
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setDeliveryStaff($this);
        }

        return $this;
    }

    public function removeDelivery(KeyCardDelivery $delivery): self
    {
        if ($this->deliveries->removeElement($delivery)) {
            // 如果是所属者，设置为null
            if ($delivery->getDeliveryStaff() === $this) {
                $delivery->setDeliveryStaff(null);
            }
        }

        return $this;
    }

    /**
     * 计算工作量
     */
    public function calculateWorkload(\DateTimeInterface $date): int
    {
        $count = 0;
        foreach ($this->deliveries as $delivery) {
            $deliveryDate = $delivery->getDeliveryTime();
            // 判断是否同一天
            if ($deliveryDate && $deliveryDate->format('Y-m-d') === $date->format('Y-m-d')) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 判断是否可接受更多工作
     */
    public function canAcceptMoreWork(\DateTimeInterface $date): bool
    {
        return $this->status === DeliveryStaffStatusEnum::IDLE && 
               $this->calculateWorkload($date) < $this->workloadLimit;
    }

    /**
     * 标记为繁忙
     */
    public function markAsBusy(): self
    {
        $this->status = DeliveryStaffStatusEnum::BUSY;
        return $this;
    }

    /**
     * 标记为空闲
     */
    public function markAsIdle(): self
    {
        $this->status = DeliveryStaffStatusEnum::IDLE;
        return $this;
    }

    /**
     * 标记为休假
     */
    public function markAsOnLeave(): self
    {
        $this->status = DeliveryStaffStatusEnum::ON_LEAVE;
        return $this;
    }

    /**
     * 获取今日工作量
     */
    public function getTodayWorkload(): int
    {
        return $this->calculateWorkload(new \DateTime());
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
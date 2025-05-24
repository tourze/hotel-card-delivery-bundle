<?php

namespace Tourze\HotelCardDeliveryBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;

class KeyCardDeliveryCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return KeyCardDelivery::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('房卡配送任务')
            ->setEntityLabelInPlural('房卡配送任务')
            ->setPageTitle('index', '房卡配送任务列表')
            ->setPageTitle('detail', '房卡配送任务详情')
            ->setPageTitle('edit', '编辑房卡配送任务')
            ->setPageTitle('new', '新建房卡配送任务')
            ->setHelp('index', '管理酒店房卡配送任务，包括配送状态跟踪和费用管理')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'order.orderNo', 'hotel.name', 'remark']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->onlyOnIndex();

        yield AssociationField::new('order', '订单')
            ->setRequired(true)
            ->formatValue(function ($value) {
                return $value ? $value->getOrderNo() : '';
            });

        yield AssociationField::new('hotel', '酒店')
            ->setRequired(true)
            ->formatValue(function ($value) {
                return $value ? $value->getName() : '';
            });

        yield IntegerField::new('roomCount', '房卡数量')
            ->setRequired(true)
            ->setHelp('需要配送的房卡数量');

        yield DateTimeField::new('deliveryTime', '配送时间')
            ->setRequired(true)
            ->setFormat('yyyy-MM-dd HH:mm')
            ->setHelp('计划配送时间');

        yield ChoiceField::new('status', '配送状态')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => DeliveryStatusEnum::class])
            ->formatValue(function ($value) {
                return $value instanceof DeliveryStatusEnum ? $value->getLabel() : '';
            })
            ->setRequired(true);

        yield MoneyField::new('fee', '配送费用')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2);

        yield ImageField::new('receiptPhotoUrl', '交接凭证照片')
            ->setBasePath('/uploads/receipts/')
            ->setUploadDir('public/uploads/receipts')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->onlyOnForms();

        yield DateTimeField::new('completedTime', '完成时间')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->hideOnForm();

        yield TextareaField::new('remark', '备注')
            ->setNumOfRows(3)
            ->onlyOnForms();

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->hideOnForm();

        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        $statusChoices = [];
        foreach (DeliveryStatusEnum::cases() as $case) {
            $statusChoices[$case->getLabel()] = $case->value;
        }

        return $filters
            ->add(EntityFilter::new('order', '订单'))
            ->add(EntityFilter::new('hotel', '酒店'))
            ->add(ChoiceFilter::new('status', '配送状态')->setChoices($statusChoices))
            ->add(DateTimeFilter::new('deliveryTime', '配送时间'))
            ->add(DateTimeFilter::new('completedTime', '完成时间'));
    }

    public function configureActions(Actions $actions): Actions
    {
        // 开始配送操作
        $startDelivery = Action::new('startDelivery', '开始配送')
            ->linkToCrudAction('startDelivery')
            ->setCssClass('btn btn-success')
            ->setIcon('fa fa-play')
            ->displayIf(function (KeyCardDelivery $entity) {
                return $entity->canStartDelivery();
            });

        // 标记完成操作
        $markCompleted = Action::new('markCompleted', '标记完成')
            ->linkToCrudAction('markCompleted')
            ->setCssClass('btn btn-primary')
            ->setIcon('fa fa-check')
            ->displayIf(function (KeyCardDelivery $entity) {
                return $entity->getStatus() === DeliveryStatusEnum::IN_PROGRESS;
            });

        // 取消配送操作
        $cancelDelivery = Action::new('cancelDelivery', '取消配送')
            ->linkToCrudAction('cancelDelivery')
            ->setCssClass('btn btn-warning')
            ->setIcon('fa fa-times')
            ->displayIf(function (KeyCardDelivery $entity) {
                return in_array($entity->getStatus(), [
                    DeliveryStatusEnum::PENDING,
                    DeliveryStatusEnum::ASSIGNED,
                    DeliveryStatusEnum::IN_PROGRESS
                ]);
            });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $startDelivery)
            ->add(Crud::PAGE_INDEX, $markCompleted)
            ->add(Crud::PAGE_INDEX, $cancelDelivery)
            ->add(Crud::PAGE_DETAIL, $startDelivery)
            ->add(Crud::PAGE_DETAIL, $markCompleted)
            ->add(Crud::PAGE_DETAIL, $cancelDelivery)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, 'startDelivery', 'markCompleted', 'cancelDelivery', Action::DELETE]);
    }

    /**
     * 开始配送
     */
    #[AdminAction('{entityId}/start-delivery', 'start_delivery')]
    public function startDelivery(AdminContext $context, Request $request): Response
    {
        /** @var KeyCardDelivery $delivery */
        $delivery = $context->getEntity()->getInstance();

        if (!$delivery->canStartDelivery()) {
            $this->addFlash('danger', '当前状态不允许开始配送');
            return $this->redirect($this->getIndexUrl());
        }

        $delivery->markAsInProgress();
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('配送任务 #%d 已开始配送', $delivery->getId()));

        return $this->redirect($this->getIndexUrl());
    }

    /**
     * 标记为已完成
     */
    #[AdminAction('{entityId}/mark-completed', 'mark_completed')]
    public function markCompleted(AdminContext $context, Request $request): Response
    {
        /** @var KeyCardDelivery $delivery */
        $delivery = $context->getEntity()->getInstance();

        if ($delivery->getStatus() !== DeliveryStatusEnum::IN_PROGRESS) {
            $this->addFlash('danger', '只有配送中的任务才能标记为完成');
            return $this->redirect($this->getIndexUrl());
        }

        // 这里可以要求上传交接凭证，暂时用默认值
        $receiptUrl = '/uploads/receipts/default.jpg';
        $delivery->markAsCompleted($receiptUrl);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('配送任务 #%d 已标记为完成', $delivery->getId()));

        return $this->redirect($this->getIndexUrl());
    }

    /**
     * 取消配送
     */
    #[AdminAction('{entityId}/cancel-delivery', 'cancel_delivery')]
    public function cancelDelivery(AdminContext $context, Request $request): Response
    {
        /** @var KeyCardDelivery $delivery */
        $delivery = $context->getEntity()->getInstance();

        if (!in_array($delivery->getStatus(), [
            DeliveryStatusEnum::PENDING,
            DeliveryStatusEnum::ASSIGNED,
            DeliveryStatusEnum::IN_PROGRESS
        ])) {
            $this->addFlash('danger', '当前状态不允许取消配送');
            return $this->redirect($this->getIndexUrl());
        }

        $reason = '管理员手动取消';
        $delivery->markAsCancelled($reason);
        $this->entityManager->flush();

        $this->addFlash('warning', sprintf('配送任务 #%d 已取消', $delivery->getId()));

        return $this->redirect($this->getIndexUrl());
    }

    private function getIndexUrl(): string
    {
        return $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
    }
} 
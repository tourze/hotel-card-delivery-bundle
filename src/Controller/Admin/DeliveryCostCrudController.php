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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;

class DeliveryCostCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return DeliveryCost::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('配送费用')
            ->setEntityLabelInPlural('配送费用')
            ->setPageTitle('index', '配送费用列表')
            ->setPageTitle('detail', '配送费用详情')
            ->setPageTitle('edit', '编辑配送费用')
            ->setPageTitle('new', '新建配送费用')
            ->setHelp('index', '管理房卡配送费用，包括基础费用、距离费用、加急费用等')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'delivery.order.orderNo', 'remarks']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->onlyOnIndex();

        yield AssociationField::new('delivery', '配送任务')
            ->setRequired(true)
            ->formatValue(function ($value) {
                if (!$value) return '';
                $order = $value->getOrder();
                $hotel = $value->getHotel();
                return sprintf('%s - %s', 
                    $order ? $order->getOrderNo() : 'Unknown',
                    $hotel ? $hotel->getName() : 'Unknown'
                );
            });

        yield MoneyField::new('baseCost', '基础费用')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('配送基础费用');

        yield NumberField::new('distance', '配送距离')
            ->setNumDecimals(2)
            ->setHelp('配送距离（公里）');

        yield MoneyField::new('distanceCost', '距离费用')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('基于距离计算的费用');

        yield MoneyField::new('urgencyCost', '加急费用')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('加急配送产生的额外费用');

        yield MoneyField::new('extraCost', '其他费用')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('其他额外费用');

        yield MoneyField::new('totalCost', '总费用')
            ->setCurrency('CNY')
            ->setStoredAsCents(false)
            ->setNumDecimals(2)
            ->setHelp('配送总费用（自动计算）')
            ->hideOnForm();

        yield BooleanField::new('settled', '已结算')
            ->setHelp('是否已完成结算');

        yield DateTimeField::new('settlementTime', '结算时间')
            ->setFormat('yyyy-MM-dd HH:mm')
            ->hideOnForm();

        yield TextareaField::new('remarks', '备注')
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
        return $filters
            ->add(EntityFilter::new('delivery', '配送任务'))
            ->add(BooleanFilter::new('settled', '已结算'))
            ->add(NumericFilter::new('totalCost', '总费用'))
            ->add(NumericFilter::new('distance', '配送距离'))
            ->add(DateTimeFilter::new('settlementTime', '结算时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'));
    }

    public function configureActions(Actions $actions): Actions
    {
        // 标记结算操作
        $markSettled = Action::new('markSettled', '标记结算')
            ->linkToCrudAction('markSettled')
            ->setCssClass('btn btn-success')
            ->setIcon('fa fa-check-circle')
            ->displayIf(function (DeliveryCost $entity) {
                return !$entity->isSettled();
            });

        // 取消结算操作
        $cancelSettled = Action::new('cancelSettled', '取消结算')
            ->linkToCrudAction('cancelSettled')
            ->setCssClass('btn btn-warning')
            ->setIcon('fa fa-undo')
            ->displayIf(function (DeliveryCost $entity) {
                return $entity->isSettled();
            });

        // 重新计算距离费用操作
        $recalculateDistance = Action::new('recalculateDistance', '重算距离费用')
            ->linkToCrudAction('recalculateDistance')
            ->setCssClass('btn btn-info')
            ->setIcon('fa fa-calculator');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $markSettled)
            ->add(Crud::PAGE_INDEX, $cancelSettled)
            ->add(Crud::PAGE_INDEX, $recalculateDistance)
            ->add(Crud::PAGE_DETAIL, $markSettled)
            ->add(Crud::PAGE_DETAIL, $cancelSettled)
            ->add(Crud::PAGE_DETAIL, $recalculateDistance)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, 'markSettled', 'cancelSettled', 'recalculateDistance', Action::DELETE]);
    }

    /**
     * 标记为已结算
     */
    #[AdminAction('{entityId}/mark-settled', 'mark_settled')]
    public function markSettled(AdminContext $context, Request $request): Response
    {
        /** @var DeliveryCost $cost */
        $cost = $context->getEntity()->getInstance();

        if ($cost->isSettled()) {
            $this->addFlash('warning', '该费用已经结算');
            return $this->redirect($this->getIndexUrl());
        }

        $cost->markAsSettled();
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('配送费用 #%d 已标记为结算', $cost->getId()));

        return $this->redirect($this->getIndexUrl());
    }

    /**
     * 取消结算状态
     */
    #[AdminAction('{entityId}/cancel-settled', 'cancel_settled')]
    public function cancelSettled(AdminContext $context, Request $request): Response
    {
        /** @var DeliveryCost $cost */
        $cost = $context->getEntity()->getInstance();

        if (!$cost->isSettled()) {
            $this->addFlash('warning', '该费用尚未结算');
            return $this->redirect($this->getIndexUrl());
        }

        $cost->setSettled(false);
        $cost->setSettlementTime(null);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('配送费用 #%d 已取消结算状态', $cost->getId()));

        return $this->redirect($this->getIndexUrl());
    }

    /**
     * 重新计算距离费用
     */
    #[AdminAction('{entityId}/recalculate-distance', 'recalculate_distance')]
    public function recalculateDistance(AdminContext $context, Request $request): Response
    {
        /** @var DeliveryCost $cost */
        $cost = $context->getEntity()->getInstance();

        // 使用默认的每公里2元费率重新计算
        $cost->calculateDistanceCost(2.0);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('配送费用 #%d 的距离费用已重新计算', $cost->getId()));

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
<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\HttpFoundation\Response;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Repository\SupervisionPlanRepository;

/**
 * 监督计划管理控制器.
 *
 * @extends AbstractCrudController<SupervisionPlan>
 */
#[AdminCrud(routePath: '/train-supervisor/supervision-plan', routeName: 'train_supervisor_supervision_plan')]
final class SupervisionPlanCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SupervisionPlanRepository $planRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SupervisionPlan::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('监督计划')
            ->setEntityLabelInPlural('监督计划')
            ->setPageTitle('index', '监督计划管理')
            ->setPageTitle('new', '创建监督计划')
            ->setPageTitle('edit', '编辑监督计划')
            ->setPageTitle('detail', '监督计划详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['planName', 'remarks'])
            ->setDateTimeFormat('Y-m-d H:i:s')
            ->setTimezone('Asia/Shanghai')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm(),

            TextField::new('planName', '计划名称')
                ->setRequired(true)
                ->setMaxLength(255)
                ->setHelp('监督计划的名称'),

            TextareaField::new('remarks', '备注')
                ->setMaxLength(65535)
                ->hideOnIndex(),

            ChoiceField::new('planType', '计划类型')
                ->setChoices([
                    '定期' => '定期',
                    '专项' => '专项',
                    '随机' => '随机',
                ])
                ->setRequired(true),

            ChoiceField::new('planStatus', '计划状态')
                ->setChoices([
                    '待执行' => '待执行',
                    '执行中' => '执行中',
                    '已完成' => '已完成',
                    '已取消' => '已取消',
                ])
                ->setRequired(true)
                ->renderAsBadges([
                    '待执行' => 'secondary',
                    '执行中' => 'success',
                    '已完成' => 'primary',
                    '已取消' => 'danger',
                ]),

            DateTimeField::new('planStartDate', '计划开始日期')
                ->setRequired(true)
                ->setFormat('yyyy-MM-dd'),

            DateTimeField::new('planEndDate', '计划结束日期')
                ->setRequired(true)
                ->setFormat('yyyy-MM-dd'),

            ArrayField::new('supervisionScope', '监督范围')
                ->hideOnIndex()
                ->setHelp('监督覆盖的机构或区域'),

            ArrayField::new('supervisionItems', '监督项目')
                ->hideOnIndex()
                ->setHelp('具体的监督项目'),

            TextField::new('supervisor', '监督人')
                ->setRequired(true)
                ->setMaxLength(100),

            DateTimeField::new('createTime', '创建时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),

            DateTimeField::new('updateTime', '更新时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('planType', '计划类型')
                ->setChoices([
                    '定期' => '定期',
                    '专项' => '专项',
                    '随机' => '随机',
                ]))
            ->add(ChoiceFilter::new('planStatus', '计划状态')
                ->setChoices([
                    '待执行' => '待执行',
                    '执行中' => '执行中',
                    '已完成' => '已完成',
                    '已取消' => '已取消',
                ]))
            ->add(DateTimeFilter::new('planStartDate', '计划开始日期'))
            ->add(DateTimeFilter::new('planEndDate', '计划结束日期'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $activateAction = Action::new('activate', '激活计划', 'fas fa-play')
            ->linkToCrudAction('activatePlan')
            ->displayIf(function (SupervisionPlan $plan) {
                return '待执行' === $plan->getPlanStatus();
            })
            ->setCssClass('btn btn-success')
        ;

        $completeAction = Action::new('complete', '完成计划', 'fas fa-check')
            ->linkToCrudAction('completePlan')
            ->displayIf(function (SupervisionPlan $plan) {
                return '执行中' === $plan->getPlanStatus();
            })
            ->setCssClass('btn btn-primary')
        ;

        $cancelAction = Action::new('cancel', '取消计划', 'fas fa-times')
            ->linkToCrudAction('cancelPlan')
            ->displayIf(function (SupervisionPlan $plan) {
                return in_array($plan->getPlanStatus(), ['待执行', '执行中'], true);
            })
            ->setCssClass('btn btn-danger')
        ;

        $exportAction = Action::new('export', '导出数据', 'fas fa-download')
            ->linkToCrudAction('exportData')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-info')
        ;

        $statisticsAction = Action::new('statistics', '统计报告', 'fas fa-chart-bar')
            ->linkToRoute('admin_supervision_statistics')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-warning')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $activateAction)
            ->add(Crud::PAGE_INDEX, $completeAction)
            ->add(Crud::PAGE_INDEX, $cancelAction)
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->add(Crud::PAGE_INDEX, $statisticsAction)
            ->add(Crud::PAGE_DETAIL, $activateAction)
            ->add(Crud::PAGE_DETAIL, $completeAction)
            ->add(Crud::PAGE_DETAIL, $cancelAction)
        ;
    }

    /**
     * 激活监督计划.
     */
    #[AdminAction(routeName: 'activate_plan', routePath: '{entityId}/activate-plan')]
    public function activatePlan(AdminContext $context): Response
    {
        $plan = $context->getEntity()->getInstance();

        if (!$plan instanceof SupervisionPlan) {
            $this->addFlash('danger', '计划不存在');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        if ('待执行' !== $plan->getPlanStatus()) {
            $this->addFlash('danger', '只能激活待执行状态的计划');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        $plan->setPlanStatus('执行中');

        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof Registry);
        $entityManager = $doctrine->getManager();
        assert($entityManager instanceof EntityManagerInterface);
        $entityManager->flush();

        $this->addFlash('success', '监督计划已激活');

        return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    /**
     * 完成监督计划.
     */
    #[AdminAction(routeName: 'complete_plan', routePath: '{entityId}/complete-plan')]
    public function completePlan(AdminContext $context): Response
    {
        $plan = $context->getEntity()->getInstance();

        if (!$plan instanceof SupervisionPlan) {
            $this->addFlash('danger', '计划不存在');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        if ('执行中' !== $plan->getPlanStatus()) {
            $this->addFlash('danger', '只能完成执行中状态的计划');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        $plan->setPlanStatus('已完成');

        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof Registry);
        $entityManager = $doctrine->getManager();
        assert($entityManager instanceof EntityManagerInterface);
        $entityManager->flush();

        $this->addFlash('success', '监督计划已完成');

        return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    /**
     * 取消监督计划.
     */
    #[AdminAction(routeName: 'cancel_plan', routePath: '{entityId}/cancel-plan')]
    public function cancelPlan(AdminContext $context): Response
    {
        $plan = $context->getEntity()->getInstance();

        if (!$plan instanceof SupervisionPlan) {
            $this->addFlash('danger', '计划不存在');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        if (!in_array($plan->getPlanStatus(), ['待执行', '执行中'], true)) {
            $this->addFlash('danger', '只能取消待执行或执行中状态的计划');

            return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
        }

        $plan->setPlanStatus('已取消');
        $plan->setRemarks('管理员手动取消');

        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof Registry);
        $entityManager = $doctrine->getManager();
        assert($entityManager instanceof EntityManagerInterface);
        $entityManager->flush();

        $this->addFlash('warning', '监督计划已取消');

        return $this->redirect($context->getRequest()->headers->get('referer') ?? $this->generateUrl('admin'));
    }

    /**
     * 导出数据.
     */
    #[AdminAction(routeName: 'export_data', routePath: '/export-data')]
    public function exportData(AdminContext $context): Response
    {
        /** @var SupervisionPlan[] $plans */
        $plans = $this->planRepository->findAll();

        // 生成CSV数据
        $csvData = "ID,计划名称,计划类型,计划状态,开始日期,结束日期,创建时间\n";

        foreach ($plans as $plan) {
            $csvData .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s\n",
                $plan->getId(),
                $plan->getPlanName(),
                $plan->getPlanType(),
                $plan->getPlanStatus(),
                $plan->getPlanStartDate()->format('Y-m-d'),
                $plan->getPlanEndDate()->format('Y-m-d'),
                $plan->getCreateTime()?->format('Y-m-d H:i:s') ?? ''
            );
        }

        $response = new Response($csvData);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="supervision_plans_' . date('Y-m-d') . '.csv"');

        return $response;
    }
}

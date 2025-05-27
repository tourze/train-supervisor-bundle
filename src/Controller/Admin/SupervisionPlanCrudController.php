<?php

declare(strict_types=1);

namespace Aqacms\TrainSupervisorBundle\Controller\Admin;

use Aqacms\TrainSupervisorBundle\Entity\SupervisionPlan;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\HttpFoundation\Response;

/**
 * 监督计划管理控制器
 */
class SupervisionPlanCrudController extends AbstractCrudController
{
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
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['title', 'description', 'institutionName'])
            ->setDateTimeFormat('Y-m-d H:i:s')
            ->setTimezone('Asia/Shanghai');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm(),

            TextField::new('title', '计划标题')
                ->setRequired(true)
                ->setMaxLength(255)
                ->setHelp('监督计划的标题'),

            TextareaField::new('description', '计划描述')
                ->setMaxLength(1000)
                ->hideOnIndex(),

            ChoiceField::new('type', '计划类型')
                ->setChoices([
                    '年度计划' => 'annual',
                    '季度计划' => 'quarterly', 
                    '月度计划' => 'monthly',
                    '专项计划' => 'special'
                ])
                ->setRequired(true),

            ChoiceField::new('status', '状态')
                ->setChoices([
                    '草稿' => 'draft',
                    '活跃' => 'active',
                    '已完成' => 'completed',
                    '已取消' => 'cancelled'
                ])
                ->setRequired(true)
                ->renderAsBadges([
                    'draft' => 'secondary',
                    'active' => 'success',
                    'completed' => 'primary',
                    'cancelled' => 'danger'
                ]),

            ChoiceField::new('priority', '优先级')
                ->setChoices([
                    '低' => 'low',
                    '中' => 'medium',
                    '高' => 'high',
                    '紧急' => 'urgent'
                ])
                ->setRequired(true)
                ->renderAsBadges([
                    'low' => 'light',
                    'medium' => 'info',
                    'high' => 'warning',
                    'urgent' => 'danger'
                ]),

            DateTimeField::new('startDate', '开始日期')
                ->setRequired(true)
                ->setFormat('Y-m-d'),

            DateTimeField::new('endDate', '结束日期')
                ->setRequired(true)
                ->setFormat('Y-m-d'),

            IntegerField::new('targetInstitutions', '目标机构数')
                ->setRequired(true)
                ->hideOnIndex(),

            IntegerField::new('completedInstitutions', '已完成机构数')
                ->hideOnForm(),

            NumberField::new('progress', '进度(%)')
                ->setNumDecimals(1)
                ->hideOnForm()
                ->formatValue(function ($value) {
                    return $value . '%';
                }),

            ArrayField::new('objectives', '监督目标')
                ->hideOnIndex()
                ->setHelp('监督计划的具体目标'),

            ArrayField::new('scope', '监督范围')
                ->hideOnIndex()
                ->setHelp('监督覆盖的机构或区域'),

            ArrayField::new('methods', '监督方法')
                ->hideOnIndex()
                ->setHelp('采用的监督方法'),

            ArrayField::new('resources', '资源配置')
                ->hideOnIndex()
                ->setHelp('人员、预算等资源配置'),

            ArrayField::new('criteria', '评估标准')
                ->hideOnIndex()
                ->setHelp('监督评估的标准'),

            TextareaField::new('remarks', '备注')
                ->hideOnIndex()
                ->setMaxLength(500),

            AssociationField::new('inspections', '相关检查')
                ->hideOnForm()
                ->setTemplatePath('admin/supervision_plan/inspections.html.twig'),

            DateTimeField::new('createdAt', '创建时间')
                ->hideOnForm()
                ->setFormat('Y-m-d H:i:s'),

            DateTimeField::new('updatedAt', '更新时间')
                ->hideOnForm()
                ->setFormat('Y-m-d H:i:s')
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('type', '计划类型')
                ->setChoices([
                    '年度计划' => 'annual',
                    '季度计划' => 'quarterly',
                    '月度计划' => 'monthly',
                    '专项计划' => 'special'
                ]))
            ->add(ChoiceFilter::new('status', '状态')
                ->setChoices([
                    '草稿' => 'draft',
                    '活跃' => 'active',
                    '已完成' => 'completed',
                    '已取消' => 'cancelled'
                ]))
            ->add(ChoiceFilter::new('priority', '优先级')
                ->setChoices([
                    '低' => 'low',
                    '中' => 'medium',
                    '高' => 'high',
                    '紧急' => 'urgent'
                ]))
            ->add(DateTimeFilter::new('startDate', '开始日期'))
            ->add(DateTimeFilter::new('endDate', '结束日期'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $activateAction = Action::new('activate', '激活计划', 'fas fa-play')
            ->linkToCrudAction('activatePlan')
            ->displayIf(function (SupervisionPlan $plan) {
                return $plan->getStatus() === 'draft';
            })
            ->setCssClass('btn btn-success');

        $completeAction = Action::new('complete', '完成计划', 'fas fa-check')
            ->linkToCrudAction('completePlan')
            ->displayIf(function (SupervisionPlan $plan) {
                return $plan->getStatus() === 'active';
            })
            ->setCssClass('btn btn-primary');

        $cancelAction = Action::new('cancel', '取消计划', 'fas fa-times')
            ->linkToCrudAction('cancelPlan')
            ->displayIf(function (SupervisionPlan $plan) {
                return in_array($plan->getStatus(), ['draft', 'active']);
            })
            ->setCssClass('btn btn-danger');

        $exportAction = Action::new('export', '导出数据', 'fas fa-download')
            ->linkToCrudAction('exportData')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-info');

        $statisticsAction = Action::new('statistics', '统计报告', 'fas fa-chart-bar')
            ->linkToRoute('admin_supervision_statistics')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-warning');

        return $actions
            ->add(Crud::PAGE_INDEX, $activateAction)
            ->add(Crud::PAGE_INDEX, $completeAction)
            ->add(Crud::PAGE_INDEX, $cancelAction)
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->add(Crud::PAGE_INDEX, $statisticsAction)
            ->add(Crud::PAGE_DETAIL, $activateAction)
            ->add(Crud::PAGE_DETAIL, $completeAction)
            ->add(Crud::PAGE_DETAIL, $cancelAction);
    }

    /**
     * 激活监督计划
     */
    public function activatePlan(AdminContext $context): Response
    {
        $plan = $context->getEntity()->getInstance();
        
        if ($plan->getStatus() !== 'draft') {
            $this->addFlash('error', '只能激活草稿状态的计划');
            return $this->redirect($context->getReferrer());
        }

        $plan->setStatus('active');
        $this->container->get('doctrine')->getManager()->flush();

        $this->addFlash('success', '监督计划已激活');
        return $this->redirect($context->getReferrer());
    }

    /**
     * 完成监督计划
     */
    public function completePlan(AdminContext $context): Response
    {
        $plan = $context->getEntity()->getInstance();
        
        if ($plan->getStatus() !== 'active') {
            $this->addFlash('error', '只能完成活跃状态的计划');
            return $this->redirect($context->getReferrer());
        }

        $plan->setStatus('completed');
        $plan->setProgress(100.0);
        $this->container->get('doctrine')->getManager()->flush();

        $this->addFlash('success', '监督计划已完成');
        return $this->redirect($context->getReferrer());
    }

    /**
     * 取消监督计划
     */
    public function cancelPlan(AdminContext $context): Response
    {
        $plan = $context->getEntity()->getInstance();
        
        if (!in_array($plan->getStatus(), ['draft', 'active'])) {
            $this->addFlash('error', '只能取消草稿或活跃状态的计划');
            return $this->redirect($context->getReferrer());
        }

        $plan->setStatus('cancelled');
        $plan->setRemarks('管理员手动取消');
        $this->container->get('doctrine')->getManager()->flush();

        $this->addFlash('warning', '监督计划已取消');
        return $this->redirect($context->getReferrer());
    }

    /**
     * 导出数据
     */
    public function exportData(AdminContext $context): Response
    {
        $repository = $this->container->get('doctrine')
            ->getRepository(SupervisionPlan::class);
        
        $plans = $repository->findAll();
        
        // 生成CSV数据
        $csvData = "ID,标题,类型,状态,优先级,开始日期,结束日期,目标机构数,已完成机构数,进度,创建时间\n";
        
        foreach ($plans as $plan) {
            $csvData .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%d,%d,%.1f,%s\n",
                $plan->getId(),
                $plan->getTitle(),
                $plan->getType(),
                $plan->getStatus(),
                $plan->getPriority(),
                $plan->getStartDate()->format('Y-m-d'),
                $plan->getEndDate()->format('Y-m-d'),
                $plan->getTargetInstitutions(),
                $plan->getCompletedInstitutions(),
                $plan->getProgress(),
                $plan->getCreatedAt()->format('Y-m-d H:i:s')
            );
        }

        $response = new Response($csvData);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="supervision_plans_' . date('Y-m-d') . '.csv"');
        
        return $response;
    }
} 
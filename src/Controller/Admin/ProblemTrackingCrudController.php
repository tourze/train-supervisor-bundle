<?php

declare(strict_types=1);

namespace TrainSupervisorBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;

/**
 * 问题跟踪CRUD控制器
 */
class ProblemTrackingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProblemTracking::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('问题跟踪')
            ->setEntityLabelInPlural('问题跟踪')
            ->setPageTitle('index', '问题跟踪管理')
            ->setPageTitle('new', '创建问题跟踪')
            ->setPageTitle('edit', '编辑问题跟踪')
            ->setPageTitle('detail', '问题跟踪详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            AssociationField::new('supervisionInspection', '关联检查'),
            TextField::new('problemTitle', '问题标题')->setRequired(true),
            ChoiceField::new('problemType', '问题类型')
                ->setChoices([
                    '制度问题' => '制度问题',
                    '师资问题' => '师资问题',
                    '设施问题' => '设施问题',
                    '管理问题' => '管理问题',
                    '其他问题' => '其他问题'
                ])
                ->setRequired(true),
            ChoiceField::new('severityLevel', '严重程度')
                ->setChoices([
                    '轻微' => '轻微',
                    '一般' => '一般',
                    '严重' => '严重',
                    '重大' => '重大'
                ])
                ->setRequired(true),
            ChoiceField::new('problemStatus', '问题状态')
                ->setChoices([
                    '待处理' => '待处理',
                    '处理中' => '处理中',
                    '待验证' => '待验证',
                    '已解决' => '已解决',
                    '已关闭' => '已关闭'
                ])
                ->setRequired(true),
            TextField::new('responsiblePerson', '责任人'),
            DateTimeField::new('discoveryDate', '发现日期')->setRequired(true),
            DateTimeField::new('expectedResolutionDate', '预期解决日期'),
            DateTimeField::new('actualResolutionDate', '实际解决日期'),
            TextareaField::new('problemDescription', '问题描述')->hideOnIndex(),
            TextareaField::new('rootCauseAnalysis', '根因分析')->hideOnIndex(),
            TextareaField::new('correctionMeasures', '纠正措施')->hideOnIndex(),
            TextareaField::new('preventiveMeasures', '预防措施')->hideOnIndex(),
            TextareaField::new('verificationResult', '验证结果')->hideOnIndex(),
            TextareaField::new('remarks', '备注')->hideOnIndex(),
            DateTimeField::new('createTime', '创建时间')->hideOnForm(),
            DateTimeField::new('updateTime', '更新时间')->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $startCorrectionAction = Action::new('startCorrection', '开始整改', 'fa fa-play')
            ->linkToCrudAction('startCorrection')
            ->displayIf(static function (ProblemTracking $problem): bool {
                return $problem->getCorrectionStatus() === '待整改';
            });

        $verifyAction = Action::new('verify', '验证整改', 'fa fa-check')
            ->linkToCrudAction('verifyCorrection')
            ->displayIf(static function (ProblemTracking $problem): bool {
                return $problem->getCorrectionStatus() === '已整改';
            });

        $closeAction = Action::new('close', '关闭问题', 'fa fa-times')
            ->linkToCrudAction('closeProblem')
            ->displayIf(static function (ProblemTracking $problem): bool {
                return $problem->getCorrectionStatus() === '已验证';
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $startCorrectionAction)
            ->add(Crud::PAGE_INDEX, $verifyAction)
            ->add(Crud::PAGE_INDEX, $closeAction)
            ->add(Crud::PAGE_DETAIL, $startCorrectionAction)
            ->add(Crud::PAGE_DETAIL, $verifyAction)
            ->add(Crud::PAGE_DETAIL, $closeAction);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('problemType', '问题类型')
                ->setChoices([
                    '制度问题' => '制度问题',
                    '师资问题' => '师资问题',
                    '设施问题' => '设施问题',
                    '管理问题' => '管理问题',
                    '其他问题' => '其他问题'
                ]))
            ->add(ChoiceFilter::new('severityLevel', '严重程度')
                ->setChoices([
                    '轻微' => '轻微',
                    '一般' => '一般',
                    '严重' => '严重',
                    '重大' => '重大'
                ]))
            ->add(ChoiceFilter::new('problemStatus', '问题状态')
                ->setChoices([
                    '待处理' => '待处理',
                    '处理中' => '处理中',
                    '待验证' => '待验证',
                    '已解决' => '已解决',
                    '已关闭' => '已关闭'
                ]))
            ->add(DateTimeFilter::new('discoveryDate', '发现日期'));
    }

    /**
     * 开始整改
     */
    public function startCorrection(): void
    {
        $this->addFlash('success', '问题整改已开始');
    }

    /**
     * 验证整改
     */
    public function verifyCorrection(): void
    {
        $this->addFlash('success', '问题整改验证完成');
    }

    /**
     * 关闭问题
     */
    public function closeProblem(): void
    {
        $this->addFlash('success', '问题已关闭');
    }
} 
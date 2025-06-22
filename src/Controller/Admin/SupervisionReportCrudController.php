<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;

/**
 * 监督报告CRUD控制器
 */
class SupervisionReportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SupervisionReport::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('监督报告')
            ->setEntityLabelInPlural('监督报告')
            ->setPageTitle('index', '监督报告管理')
            ->setPageTitle('new', '创建监督报告')
            ->setPageTitle('edit', '编辑监督报告')
            ->setPageTitle('detail', '监督报告详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            TextField::new('reportTitle', '报告标题')->setRequired(true),
            ChoiceField::new('reportType', '报告类型')
                ->setChoices([
                    '日报' => '日报',
                    '周报' => '周报',
                    '月报' => '月报',
                    '季报' => '季报',
                    '年报' => '年报',
                    '专项报告' => '专项报告'
                ])
                ->setRequired(true),
            DateTimeField::new('reportPeriodStart', '报告期开始')->setRequired(true),
            DateTimeField::new('reportPeriodEnd', '报告期结束')->setRequired(true),
            TextField::new('reporter', '报告人')->setRequired(true),
            ChoiceField::new('reportStatus', '报告状态')
                ->setChoices([
                    '草稿' => '草稿',
                    '待审核' => '待审核',
                    '已发布' => '已发布',
                    '已归档' => '已归档'
                ])
                ->setRequired(true),
            TextareaField::new('executiveSummary', '执行摘要')->hideOnIndex(),
            TextareaField::new('inspectionSummary', '检查总结')->hideOnIndex(),
            TextareaField::new('problemSummary', '问题总结')->hideOnIndex(),
            TextareaField::new('statisticalData', '统计数据')->hideOnIndex(),
            TextareaField::new('trendAnalysis', '趋势分析')->hideOnIndex(),
            TextareaField::new('recommendations', '建议措施')->hideOnIndex(),
            TextareaField::new('nextPeriodPlan', '下期计划')->hideOnIndex(),
            TextareaField::new('remarks', '备注')->hideOnIndex(),
            DateTimeField::new('createTime', '创建时间')->hideOnForm(),
            DateTimeField::new('updateTime', '更新时间')->hideOnForm(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('reportType', '报告类型')
                ->setChoices([
                    '日报' => '日报',
                    '周报' => '周报',
                    '月报' => '月报',
                    '季报' => '季报',
                    '年报' => '年报',
                    '专项报告' => '专项报告'
                ]))
            ->add(ChoiceFilter::new('reportStatus', '报告状态')
                ->setChoices([
                    '草稿' => '草稿',
                    '待审核' => '待审核',
                    '已发布' => '已发布',
                    '已归档' => '已归档'
                ]))
            ->add(DateTimeFilter::new('reportPeriodStart', '报告期开始'))
            ->add(DateTimeFilter::new('reportPeriodEnd', '报告期结束'));
    }
} 
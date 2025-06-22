<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;

/**
 * 监督检查CRUD控制器
 */
class SupervisionInspectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SupervisionInspection::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('监督检查')
            ->setEntityLabelInPlural('监督检查')
            ->setPageTitle('index', '监督检查管理')
            ->setPageTitle('new', '创建监督检查')
            ->setPageTitle('edit', '编辑监督检查')
            ->setPageTitle('detail', '监督检查详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            AssociationField::new('supervisionPlan', '监督计划'),
            TextField::new('inspectionTitle', '检查标题')->setRequired(true),
            ChoiceField::new('inspectionType', '检查类型')
                ->setChoices([
                    '现场检查' => '现场检查',
                    '在线检查' => '在线检查',
                    '文档检查' => '文档检查',
                    '综合检查' => '综合检查'
                ])
                ->setRequired(true),
            DateTimeField::new('inspectionDate', '检查日期')->setRequired(true),
            TextField::new('inspectionLocation', '检查地点'),
            TextField::new('inspector', '检查员')->setRequired(true),
            ChoiceField::new('inspectionStatus', '检查状态')
                ->setChoices([
                    '计划中' => '计划中',
                    '进行中' => '进行中',
                    '已完成' => '已完成',
                    '已取消' => '已取消'
                ])
                ->setRequired(true),
            NumberField::new('score', '检查得分')->setNumDecimals(2),
            TextareaField::new('inspectionContent', '检查内容')->hideOnIndex(),
            TextareaField::new('inspectionResult', '检查结果')->hideOnIndex(),
            TextareaField::new('problemsFound', '发现问题')->hideOnIndex(),
            TextareaField::new('suggestions', '改进建议')->hideOnIndex(),
            TextareaField::new('remarks', '备注')->hideOnIndex(),
            DateTimeField::new('createTime', '创建时间')->hideOnForm(),
            DateTimeField::new('updateTime', '更新时间')->hideOnForm(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('inspectionType', '检查类型')
                ->setChoices([
                    '现场检查' => '现场检查',
                    '在线检查' => '在线检查',
                    '文档检查' => '文档检查',
                    '综合检查' => '综合检查'
                ]))
            ->add(ChoiceFilter::new('inspectionStatus', '检查状态')
                ->setChoices([
                    '计划中' => '计划中',
                    '进行中' => '进行中',
                    '已完成' => '已完成',
                    '已取消' => '已取消'
                ]))
            ->add(DateTimeFilter::new('inspectionDate', '检查日期'));
    }
} 
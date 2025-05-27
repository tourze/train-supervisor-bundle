<?php

declare(strict_types=1);

namespace TrainSupervisorBundle\Controller\Admin;

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
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;

/**
 * 质量评估CRUD控制器
 */
class QualityAssessmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QualityAssessment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('质量评估')
            ->setEntityLabelInPlural('质量评估')
            ->setPageTitle('index', '质量评估管理')
            ->setPageTitle('new', '创建质量评估')
            ->setPageTitle('edit', '编辑质量评估')
            ->setPageTitle('detail', '质量评估详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            AssociationField::new('supervisionInspection', '关联检查'),
            TextField::new('assessmentTitle', '评估标题')->setRequired(true),
            ChoiceField::new('assessmentType', '评估类型')
                ->setChoices([
                    '机构评估' => '机构评估',
                    '课程评估' => '课程评估',
                    '教师评估' => '教师评估',
                    '效果评估' => '效果评估'
                ])
                ->setRequired(true),
            DateTimeField::new('assessmentDate', '评估日期')->setRequired(true),
            TextField::new('assessor', '评估员')->setRequired(true),
            NumberField::new('totalScore', '总分')->setNumDecimals(2),
            NumberField::new('passScore', '及格分')->setNumDecimals(2),
            ChoiceField::new('assessmentResult', '评估结果')
                ->setChoices([
                    '优秀' => '优秀',
                    '良好' => '良好',
                    '合格' => '合格',
                    '不合格' => '不合格'
                ])
                ->setRequired(true),
            ChoiceField::new('assessmentStatus', '评估状态')
                ->setChoices([
                    '待评估' => '待评估',
                    '评估中' => '评估中',
                    '已完成' => '已完成',
                    '已取消' => '已取消'
                ])
                ->setRequired(true),
            TextareaField::new('assessmentCriteria', '评估标准')->hideOnIndex(),
            TextareaField::new('assessmentContent', '评估内容')->hideOnIndex(),
            TextareaField::new('strengthsAnalysis', '优势分析')->hideOnIndex(),
            TextareaField::new('weaknessesAnalysis', '不足分析')->hideOnIndex(),
            TextareaField::new('improvementSuggestions', '改进建议')->hideOnIndex(),
            TextareaField::new('remarks', '备注')->hideOnIndex(),
            DateTimeField::new('createTime', '创建时间')->hideOnForm(),
            DateTimeField::new('updateTime', '更新时间')->hideOnForm(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('assessmentType', '评估类型')
                ->setChoices([
                    '机构评估' => '机构评估',
                    '课程评估' => '课程评估',
                    '教师评估' => '教师评估',
                    '效果评估' => '效果评估'
                ]))
            ->add(ChoiceFilter::new('assessmentResult', '评估结果')
                ->setChoices([
                    '优秀' => '优秀',
                    '良好' => '良好',
                    '合格' => '合格',
                    '不合格' => '不合格'
                ]))
            ->add(ChoiceFilter::new('assessmentStatus', '评估状态')
                ->setChoices([
                    '待评估' => '待评估',
                    '评估中' => '评估中',
                    '已完成' => '已完成',
                    '已取消' => '已取消'
                ]))
            ->add(DateTimeFilter::new('assessmentDate', '评估日期'));
    }
} 
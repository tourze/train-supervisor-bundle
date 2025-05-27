<?php

declare(strict_types=1);

namespace TrainSupervisorBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;

/**
 * 监督员CRUD控制器
 */
class SupervisorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Supervisor::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('监督员')
            ->setEntityLabelInPlural('监督员')
            ->setPageTitle('index', '监督员管理')
            ->setPageTitle('new', '创建监督员')
            ->setPageTitle('edit', '编辑监督员')
            ->setPageTitle('detail', '监督员详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            TextField::new('supervisorName', '监督员姓名')->setRequired(true),
            TextField::new('supervisorCode', '监督员编号')->setRequired(true),
            TextField::new('department', '所属部门'),
            TextField::new('position', '职位'),
            TextField::new('contactPhone', '联系电话'),
            TextField::new('contactEmail', '联系邮箱'),
            ChoiceField::new('supervisorLevel', '监督员级别')
                ->setChoices([
                    '初级' => '初级',
                    '中级' => '中级',
                    '高级' => '高级',
                    '专家' => '专家'
                ])
                ->setRequired(true),
            ChoiceField::new('supervisorStatus', '状态')
                ->setChoices([
                    '在职' => '在职',
                    '离职' => '离职',
                    '停职' => '停职',
                    '退休' => '退休'
                ])
                ->setRequired(true),
            TextareaField::new('specialties', '专业领域')->hideOnIndex(),
            TextareaField::new('qualifications', '资质证书')->hideOnIndex(),
            TextareaField::new('workExperience', '工作经历')->hideOnIndex(),
            TextareaField::new('remarks', '备注')->hideOnIndex(),
            DateTimeField::new('createTime', '创建时间')->hideOnForm(),
            DateTimeField::new('updateTime', '更新时间')->hideOnForm(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('supervisorLevel', '监督员级别')
                ->setChoices([
                    '初级' => '初级',
                    '中级' => '中级',
                    '高级' => '高级',
                    '专家' => '专家'
                ]))
            ->add(ChoiceFilter::new('supervisorStatus', '状态')
                ->setChoices([
                    '在职' => '在职',
                    '离职' => '离职',
                    '停职' => '停职',
                    '退休' => '退休'
                ]));
    }
} 
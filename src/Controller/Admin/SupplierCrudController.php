<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Tourze\TrainSupervisorBundle\Entity\Supplier;

/**
 * 供应商CRUD控制器.
 *
 * @extends AbstractCrudController<Supplier>
 */
#[AdminCrud(routePath: '/train-supervisor/supplier', routeName: 'train_supervisor_supplier')]
final class SupplierCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Supplier::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('供应商')
            ->setEntityLabelInPlural('供应商')
            ->setPageTitle('index', '供应商管理')
            ->setPageTitle('new', '创建供应商')
            ->setPageTitle('edit', '编辑供应商')
            ->setPageTitle('detail', '供应商详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            TextField::new('name', '供应商名称')->setRequired(true),
            TextField::new('contact', '联系人'),
            TelephoneField::new('phone', '联系电话'),
            EmailField::new('email', '联系邮箱'),
            TextareaField::new('address', '地址')->hideOnIndex(),
            ChoiceField::new('status', '状态')
                ->setChoices([
                    '激活' => 'active',
                    '停用' => 'inactive',
                    '暂停' => 'suspended',
                ])
                ->setRequired(true),
            DateTimeField::new('createTime', '创建时间')->hideOnForm(),
            DateTimeField::new('updateTime', '更新时间')->hideOnForm(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('contact')
            ->add(ChoiceFilter::new('status')
                ->setChoices([
                    '激活' => 'active',
                    '停用' => 'inactive',
                    '暂停' => 'suspended',
                ]))
        ;
    }
}

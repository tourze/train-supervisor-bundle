<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;

/**
 * 监督员数据CRUD控制器.
 *
 * @extends AbstractCrudController<SupervisorData>
 */
#[AdminCrud(routePath: '/train-supervisor/supervisor-data', routeName: 'train_supervisor_supervisor_data')]
final class SupervisorDataCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SupervisorData::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('监督员数据')
            ->setEntityLabelInPlural('监督员数据')
            ->setPageTitle('index', '监督员数据管理')
            ->setPageTitle('new', '创建监督员数据')
            ->setPageTitle('edit', '编辑监督员数据')
            ->setPageTitle('detail', '监督员数据详情')
            ->setDefaultSort(['date' => 'DESC'])
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            DateField::new('date', '日期')->setRequired(true),
            AssociationField::new('supplier', '供应商')->setRequired(true),
            IntegerField::new('dailyLoginCount', '日登录人数'),
            IntegerField::new('dailyLearnCount', '日学习人数'),
            IntegerField::new('totalClassroomCount', '总班级数'),
            IntegerField::new('newClassroomCount', '新增班级数'),
            TextField::new('region', '地区')->hideOnIndex(),
            TextField::new('province', '省份')->hideOnIndex(),
            TextField::new('city', '城市')->hideOnIndex(),
            ChoiceField::new('ageGroup', '年龄段')
                ->setChoices([
                    '18-25岁' => '18-25岁',
                    '26-35岁' => '26-35岁',
                    '36-45岁' => '36-45岁',
                    '46-55岁' => '46-55岁',
                    '56岁以上' => '56岁以上',
                ])
                ->hideOnIndex(),
            IntegerField::new('dailyCheatCount', '日作弊人数')->hideOnIndex(),
            IntegerField::new('faceDetectSuccessCount', '人脸检测成功次数')->hideOnIndex(),
            IntegerField::new('faceDetectFailCount', '人脸检测失败次数')->hideOnIndex(),
            DateTimeField::new('createTime', '创建时间')->hideOnForm(),
            DateTimeField::new('updateTime', '更新时间')->hideOnForm(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('date')
            ->add('supplier')
            ->add('region')
            ->add('province')
            ->add('city')
            ->add(ChoiceFilter::new('ageGroup')
                ->setChoices([
                    '18-25岁' => '18-25岁',
                    '26-35岁' => '26-35岁',
                    '36-45岁' => '36-45岁',
                    '46-55岁' => '46-55岁',
                    '56岁以上' => '56岁以上',
                ]))
        ;
    }
}

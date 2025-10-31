<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin\Statistics;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;

/**
 * 数据导出控制器.
 *
 * @extends AbstractCrudController<SupervisionReport>
 */
#[AdminCrud(routePath: '/train-supervisor/export', routeName: 'train_supervisor_export')]
final class ExportController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SupervisionReport::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return Filters::new();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return Crud::new();
    }

    #[Route(path: '/admin/supervision/export', name: 'admin_supervision_export', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('@TrainSupervisor/admin/export.html.twig');
    }
}

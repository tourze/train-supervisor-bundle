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
 * 异常检测控制器.
 *
 * @extends AbstractCrudController<SupervisionReport>
 */
#[AdminCrud(routePath: '/train-supervisor/anomalies', routeName: 'train_supervisor_anomalies')]
final class AnomaliesController extends AbstractCrudController
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

    #[Route(path: '/admin/supervision/anomalies', name: 'admin_supervision_anomalies', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('@TrainSupervisor/admin/anomalies.html.twig');
    }
}

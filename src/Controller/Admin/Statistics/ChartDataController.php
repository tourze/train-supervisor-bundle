<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin\Statistics;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;

/**
 * 图表数据API控制器.
 *
 * @extends AbstractCrudController<SupervisionReport>
 */
#[AdminCrud(routePath: '/train-supervisor/chart-data', routeName: 'train_supervisor_chart_data')]
final class ChartDataController extends AbstractCrudController
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

    #[Route(path: '/admin/supervision/api/chart-data', name: 'admin_supervision_chart_data', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $type = $request->query->get('type');

        // 模拟数据，实际应该从服务层获取
        $data = match ($type) {
            'inspection_trends' => [
                'labels' => ['1月', '2月', '3月', '4月', '5月', '6月'],
                'data' => [12, 19, 15, 25, 22, 30],
            ],
            'quality_distribution' => [
                'labels' => ['优秀', '良好', '合格', '不合格'],
                'data' => [30, 45, 20, 5],
            ],
            'problem_status' => [
                'labels' => ['待处理', '处理中', '已解决', '已关闭'],
                'data' => [8, 15, 25, 12],
            ],
            default => [],
        };

        return new JsonResponse($data);
    }
}

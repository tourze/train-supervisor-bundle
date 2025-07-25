<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\LearningStatistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsService;

/**
 * 学习统计主页控制器
 */
class IndexController extends AbstractController
{
    public function __construct(
        private readonly LearningStatisticsService $statisticsService,
    ) {}

    #[Route(path: '/admin/learning-statistics', name: 'admin_learning_statistics_index', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $filters = $this->extractFilters($request);

        try {
            $overview = $this->statisticsService->getLearningOverview($filters);
            $byInstitution = $this->statisticsService->getStatisticsByInstitution($filters);
            $byRegion = $this->statisticsService->getStatisticsByRegion($filters);
            $trends = $this->statisticsService->getLearningTrends($filters, 'daily');

            return $this->render('@TrainSupervisor/learning_statistics/index.html.twig', [
                'overview' => $overview,
                'by_institution' => array_slice($byInstitution, 0, 10),
                'by_region' => $byRegion,
                'trends' => array_slice($trends, -30), // 最近30天
                'filters' => $filters,
                'total_institutions' => count($byInstitution),
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('danger', '获取统计数据失败：' . $e->getMessage());

            return $this->render('@TrainSupervisor/learning_statistics/index.html.twig', [
                'overview' => null,
                'by_institution' => [],
                'by_region' => [],
                'trends' => [],
                'filters' => $filters,
                'total_institutions' => 0,
            ]);
        }
    }

    /**
     * 从请求中提取过滤条件
     */
    private function extractFilters(Request $request): array
    {
        $filters = [];

        // 时间条件
        if ($request->query->has('start_date')) {
            $filters['start_date'] = $request->query->get('start_date');
        }
        if ($request->query->has('end_date')) {
            $filters['end_date'] = $request->query->get('end_date');
        }

        // 默认时间范围：最近30天
        if ((bool) empty($filters['start_date']) && empty($filters['end_date'])) {
            $endDate = new \DateTime();
            $startDate = (clone $endDate)->modify('-30 days');
            $filters['start_date'] = $startDate->format('Y-m-d');
            $filters['end_date'] = $endDate->format('Y-m-d');
        }

        // 机构条件
        if ($request->query->has('institution_id')) {
            $filters['institution_id'] = $request->query->get('institution_id');
        }
        if ($request->query->has('institution_ids')) {
            $filters['institution_ids'] = explode(',', $request->query->get('institution_ids'));
        }

        // 区域条件
        if ($request->query->has('region')) {
            $filters['region'] = $request->query->get('region');
        }
        if ($request->query->has('province')) {
            $filters['province'] = $request->query->get('province');
        }
        if ($request->query->has('city')) {
            $filters['city'] = $request->query->get('city');
        }

        // 年龄条件
        if ($request->query->has('age_group')) {
            $filters['age_group'] = $request->query->get('age_group');
        }

        return $filters;
    }
}
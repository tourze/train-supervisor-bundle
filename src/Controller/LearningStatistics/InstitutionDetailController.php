<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\LearningStatistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsService;

/**
 * 机构详细统计控制器.
 */
final class InstitutionDetailController extends AbstractController
{
    public function __construct(
        private readonly LearningStatisticsService $statisticsService,
    ) {
    }

    #[Route(path: '/admin/learning-statistics/institution/{id}', name: 'admin_learning_statistics_institution', methods: ['GET'])]
    public function __invoke(string $id, Request $request): Response
    {
        $filters = array_merge($this->extractFilters($request), ['institution_id' => $id]);

        try {
            $statistics = $this->statisticsService->getLearningStatistics($filters);
            $trends = $this->statisticsService->getLearningTrends($filters, 'daily');

            return $this->render('@TrainSupervisor/learning_statistics/institution_detail.html.twig', [
                'institution_id' => $id,
                'statistics' => $statistics,
                'trends' => $trends,
                'filters' => $filters,
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('danger', '获取机构统计数据失败：' . $e->getMessage());

            return $this->redirectToRoute('admin_learning_statistics_index');
        }
    }

    /**
     * 从请求中提取过滤条件.
     *
     * @return array<string, mixed>
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
        if ((!isset($filters['start_date']) || '' === $filters['start_date']) && (!isset($filters['end_date']) || '' === $filters['end_date'])) {
            $endDate = new \DateTime();
            $startDate = (clone $endDate)->modify('-30 days');
            $filters['start_date'] = $startDate->format('Y-m-d');
            $filters['end_date'] = $endDate->format('Y-m-d');
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

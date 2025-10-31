<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\LearningStatistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsService;

/**
 * 趋势分析页面控制器.
 */
final class TrendsController extends AbstractController
{
    public function __construct(
        private readonly LearningStatisticsService $statisticsService,
    ) {
    }

    #[Route(path: '/admin/learning-statistics/trends', name: 'admin_learning_statistics_trends', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $periodType = (string) $request->query->get('period_type', 'daily');

        try {
            $trends = $this->statisticsService->getLearningTrends($filters, $periodType);

            return $this->render('@TrainSupervisor/learning_statistics/trends.html.twig', [
                'trends' => $trends,
                'period_type' => $periodType,
                'filters' => $filters,
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('danger', '获取趋势数据失败：' . $e->getMessage());

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

        $filters = array_merge($filters, $this->extractTimeFilters($request));
        $filters = array_merge($filters, $this->extractInstitutionFilters($request));
        $filters = array_merge($filters, $this->extractLocationFilters($request));

        return array_merge($filters, $this->extractDemographicFilters($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractTimeFilters(Request $request): array
    {
        $filters = [];

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

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractInstitutionFilters(Request $request): array
    {
        $filters = [];

        if ($request->query->has('institution_id')) {
            $filters['institution_id'] = $request->query->get('institution_id');
        }
        if ($request->query->has('institution_ids')) {
            $institutionIds = $request->query->get('institution_ids');
            if (is_string($institutionIds) && '' !== $institutionIds) {
                $filters['institution_ids'] = explode(',', $institutionIds);
            }
        }

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractLocationFilters(Request $request): array
    {
        $filters = [];

        if ($request->query->has('region')) {
            $filters['region'] = $request->query->get('region');
        }
        if ($request->query->has('province')) {
            $filters['province'] = $request->query->get('province');
        }
        if ($request->query->has('city')) {
            $filters['city'] = $request->query->get('city');
        }

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractDemographicFilters(Request $request): array
    {
        $filters = [];

        if ($request->query->has('age_group')) {
            $filters['age_group'] = $request->query->get('age_group');
        }

        return $filters;
    }
}

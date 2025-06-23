<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\LearningStatistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsService;

/**
 * 实时数据控制器
 */
class RealtimeController extends AbstractController
{
    public function __construct(
        private readonly LearningStatisticsService $statisticsService,
    ) {}

    #[Route('/admin/learning-statistics/realtime', name: 'admin_learning_statistics_realtime', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $filters = $this->extractFilters($request);

        try {
            $realtime = $this->statisticsService->getRealtimeStatistics($filters);

            return $this->render('@TrainSupervisor/learning_statistics/realtime.html.twig', [
                'realtime' => $realtime,
                'filters' => $filters,
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('danger', '获取实时数据失败：' . $e->getMessage());
            return $this->redirectToRoute('admin_learning_statistics_index');
        }
    }

    /**
     * 从请求中提取过滤条件
     */
    private function extractFilters(Request $request): array
    {
        $filters = [];

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
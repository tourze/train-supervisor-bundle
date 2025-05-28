<?php

namespace Tourze\TrainSupervisorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsService;

/**
 * 学习统计Web控制器
 * 提供学习统计的网页界面
 */
#[Route('/admin/learning-statistics')]
class LearningStatisticsWebController extends AbstractController
{
    public function __construct(
        private readonly LearningStatisticsService $statisticsService,
    ) {
    }

    /**
     * 学习统计主页
     */
    #[Route('', name: 'admin_learning_statistics_index', methods: ['GET'])]
    public function index(Request $request): Response
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
            
        } catch (\Exception $e) {
            $this->addFlash('error', '获取统计数据失败：' . $e->getMessage());
            
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
     * 机构详细统计
     */
    #[Route('/institution/{id}', name: 'admin_learning_statistics_institution', methods: ['GET'])]
    public function institutionDetail(string $id, Request $request): Response
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
            
        } catch (\Exception $e) {
            $this->addFlash('error', '获取机构统计数据失败：' . $e->getMessage());
            return $this->redirectToRoute('admin_learning_statistics_index');
        }
    }

    /**
     * 实时数据页面
     */
    #[Route('/realtime', name: 'admin_learning_statistics_realtime', methods: ['GET'])]
    public function realtime(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        
        try {
            $realtime = $this->statisticsService->getRealtimeStatistics($filters);
            
            return $this->render('@TrainSupervisor/learning_statistics/realtime.html.twig', [
                'realtime' => $realtime,
                'filters' => $filters,
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', '获取实时数据失败：' . $e->getMessage());
            return $this->redirectToRoute('admin_learning_statistics_index');
        }
    }

    /**
     * 趋势分析页面
     */
    #[Route('/trends', name: 'admin_learning_statistics_trends', methods: ['GET'])]
    public function trends(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        $periodType = $request->query->get('period_type', 'daily');
        
        try {
            $trends = $this->statisticsService->getLearningTrends($filters, $periodType);
            
            return $this->render('@TrainSupervisor/learning_statistics/trends.html.twig', [
                'trends' => $trends,
                'period_type' => $periodType,
                'filters' => $filters,
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', '获取趋势数据失败：' . $e->getMessage());
            return $this->redirectToRoute('admin_learning_statistics_index');
        }
    }

    /**
     * 报告页面
     */
    #[Route('/reports', name: 'admin_learning_statistics_reports', methods: ['GET'])]
    public function reports(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        
        try {
            $overview = $this->statisticsService->getLearningOverview($filters);
            $byInstitution = $this->statisticsService->getStatisticsByInstitution($filters);
            $byRegion = $this->statisticsService->getStatisticsByRegion($filters);
            $byAgeGroup = $this->statisticsService->getStatisticsByAgeGroup($filters);
            
            return $this->render('@TrainSupervisor/learning_statistics/reports.html.twig', [
                'overview' => $overview,
                'by_institution' => $byInstitution,
                'by_region' => $byRegion,
                'by_age_group' => $byAgeGroup,
                'filters' => $filters,
                'generated_at' => new \DateTime(),
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', '生成报告失败：' . $e->getMessage());
            return $this->redirectToRoute('admin_learning_statistics_index');
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
        if (empty($filters['start_date']) && empty($filters['end_date'])) {
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
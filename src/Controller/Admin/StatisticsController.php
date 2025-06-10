<?php

declare(strict_types=1);

namespace TrainSupervisorBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 统计分析控制器
 */
#[Route('/admin/supervision')]
class StatisticsController extends AbstractController
{
    /**
     * 统计分析页面
     */
    #[Route('/statistics', name: 'admin_supervision_statistics')]
    public function statistics(): Response
    {
        return $this->render('@TrainSupervisor/admin/statistics.html.twig');
    }

    /**
     * 趋势分析页面
     */
    #[Route('/trends', name: 'admin_supervision_trends')]
    public function trends(): Response
    {
        return $this->render('@TrainSupervisor/admin/trends.html.twig');
    }

    /**
     * 异常检测页面
     */
    #[Route('/anomalies', name: 'admin_supervision_anomalies')]
    public function anomalies(): Response
    {
        return $this->render('@TrainSupervisor/admin/anomalies.html.twig');
    }

    /**
     * 数据导出页面
     */
    #[Route('/export', name: 'admin_supervision_export')]
    public function export(): Response
    {
        return $this->render('@TrainSupervisor/admin/export.html.twig');
    }

    /**
     * 获取图表数据API
     */
    #[Route('/api/chart-data', name: 'admin_supervision_chart_data', methods: ['GET'])]
    public function getChartData(Request $request): JsonResponse
    {
        $type = $request->query->get('type');
        
        // 模拟数据，实际应该从服务层获取
        $data = match ($type) {
            'inspection_trends' => [
                'labels' => ['1月', '2月', '3月', '4月', '5月', '6月'],
                'data' => [12, 19, 15, 25, 22, 30]
            ],
            'quality_distribution' => [
                'labels' => ['优秀', '良好', '合格', '不合格'],
                'data' => [30, 45, 20, 5]
            ],
            'problem_status' => [
                'labels' => ['待处理', '处理中', '已解决', '已关闭'],
                'data' => [8, 15, 25, 12]
            ],
            default => []
        };

        return new JsonResponse($data);
    }
} 
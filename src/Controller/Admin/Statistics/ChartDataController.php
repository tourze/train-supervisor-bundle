<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 图表数据API控制器
 */
class ChartDataController extends AbstractController
{
    #[Route(path: '/admin/supervision/api/chart-data', name: 'admin_supervision_chart_data', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
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
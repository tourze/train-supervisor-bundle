<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 统计分析概览控制器
 */
class StatisticsOverviewController extends AbstractController
{
    #[Route('/admin/supervision/statistics', name: 'admin_supervision_statistics')]
    public function __invoke(): Response
    {
        return $this->render('@TrainSupervisor/admin/statistics.html.twig');
    }
}
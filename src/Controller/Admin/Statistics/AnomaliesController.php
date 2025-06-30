<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 异常检测控制器
 */
class AnomaliesController extends AbstractController
{
    #[Route(path: '/admin/supervision/anomalies', name: 'admin_supervision_anomalies')]
    public function __invoke(): Response
    {
        return $this->render('@TrainSupervisor/admin/anomalies.html.twig');
    }
}
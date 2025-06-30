<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 趋势分析控制器
 */
class TrendsController extends AbstractController
{
    #[Route(path: '/admin/supervision/trends', name: 'admin_supervision_trends')]
    public function __invoke(): Response
    {
        return $this->render('@TrainSupervisor/admin/trends.html.twig');
    }
}
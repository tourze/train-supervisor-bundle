<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin\Statistics;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 数据导出控制器
 */
class ExportController extends AbstractController
{
    #[Route('/admin/supervision/export', name: 'admin_supervision_export')]
    public function __invoke(): Response
    {
        return $this->render('@TrainSupervisor/admin/export.html.twig');
    }
}
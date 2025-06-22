<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;

/**
 * 培训监督管理仪表板控制器
 */
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    #[Route('/admin/supervision', name: 'admin_supervision')]
    public function index(): Response
    {
        return $this->redirect($this->adminUrlGenerator->setController(\Tourze\TrainSupervisorBundle\Controller\Admin\SupervisionPlanCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('培训监督管理系统')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin')
            ->setTextDirection('ltr')
            ->renderContentMaximized()
            ->renderSidebarMinimized()
            ->disableUrlSignatures()
            ->generateRelativeUrls();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('仪表板', 'fa fa-home');

        yield MenuItem::section('监督计划管理');
        yield MenuItem::linkToCrud('监督计划', 'fa fa-calendar-check', SupervisionPlan::class);
        yield MenuItem::linkToCrud('监督检查', 'fa fa-search', SupervisionInspection::class);

        yield MenuItem::section('质量管理');
        yield MenuItem::linkToCrud('质量评估', 'fa fa-star', QualityAssessment::class);
        yield MenuItem::linkToCrud('问题跟踪', 'fa fa-exclamation-triangle', ProblemTracking::class);

        yield MenuItem::section('报告管理');
        yield MenuItem::linkToCrud('监督报告', 'fa fa-file-text', SupervisionReport::class);

        yield MenuItem::section('人员管理');
        yield MenuItem::linkToCrud('监督员', 'fa fa-users', Supervisor::class);

        yield MenuItem::section('学习统计');
        yield MenuItem::linkToRoute('学习概览', 'fa fa-tachometer-alt', 'admin_learning_statistics_index');
        yield MenuItem::linkToRoute('实时统计', 'fa fa-pulse', 'admin_learning_statistics_realtime');
        yield MenuItem::linkToRoute('趋势分析', 'fa fa-chart-line', 'admin_learning_statistics_trends');
        yield MenuItem::linkToRoute('统计报告', 'fa fa-file-chart-line', 'admin_learning_statistics_reports');

        yield MenuItem::section('数据分析');
        yield MenuItem::linkToRoute('统计分析', 'fa fa-chart-bar', 'admin_supervision_statistics');
        yield MenuItem::linkToRoute('趋势分析', 'fa fa-line-chart', 'admin_supervision_trends');
        yield MenuItem::linkToRoute('异常检测', 'fa fa-warning', 'admin_supervision_anomalies');

        yield MenuItem::section('数据导出');
        yield MenuItem::linkToRoute('导出数据', 'fa fa-download', 'admin_supervision_export');
    }
} 
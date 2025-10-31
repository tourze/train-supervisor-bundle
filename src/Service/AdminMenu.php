<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('培训监管')) {
            $item->addChild('培训监管');
        }

        $parentMenu = $item->getChild('培训监管');

        if (null === $parentMenu) {
            return;
        }

        // 基础管理
        $parentMenu
            ->addChild('供应商管理')
            ->setUri($this->linkGenerator->getCurdListPage(Supplier::class))
            ->setAttribute('icon', 'fas fa-building')
        ;

        $parentMenu
            ->addChild('监督员管理')
            ->setUri($this->linkGenerator->getCurdListPage(Supervisor::class))
            ->setAttribute('icon', 'fas fa-user-tie')
        ;

        // 监督计划与巡检
        $parentMenu
            ->addChild('监督计划')
            ->setUri($this->linkGenerator->getCurdListPage(SupervisionPlan::class))
            ->setAttribute('icon', 'fas fa-calendar-alt')
        ;

        $parentMenu
            ->addChild('监督巡检')
            ->setUri($this->linkGenerator->getCurdListPage(SupervisionInspection::class))
            ->setAttribute('icon', 'fas fa-search')
        ;

        // 质量评估与问题跟踪
        $parentMenu
            ->addChild('质量评估')
            ->setUri($this->linkGenerator->getCurdListPage(QualityAssessment::class))
            ->setAttribute('icon', 'fas fa-star')
        ;

        $parentMenu
            ->addChild('问题跟踪')
            ->setUri($this->linkGenerator->getCurdListPage(ProblemTracking::class))
            ->setAttribute('icon', 'fas fa-exclamation-triangle')
        ;

        // 报告与数据
        $parentMenu
            ->addChild('监督报告')
            ->setUri($this->linkGenerator->getCurdListPage(SupervisionReport::class))
            ->setAttribute('icon', 'fas fa-file-alt')
        ;

        $parentMenu
            ->addChild('监督员数据')
            ->setUri($this->linkGenerator->getCurdListPage(SupervisorData::class))
            ->setAttribute('icon', 'fas fa-chart-bar')
        ;
    }
}

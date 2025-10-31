<?php

namespace Tourze\TrainSupervisorBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;

#[When(env: 'dev')]
class SupervisionReportFixtures extends Fixture implements FixtureGroupInterface
{
    public const SUPERVISION_REPORT_REFERENCE = 'supervision-report';

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createSupervisionReportData($manager);
        $manager->flush();
    }

    private function createSupervisionReportData(ObjectManager $manager): void
    {
        $report = new SupervisionReport();
        $report->setReportType('月度监督报告');
        $report->setReportTitle('2024年1月培训监督工作总结报告');
        $report->setReportPeriodStart(new \DateTimeImmutable('2024-01-01'));
        $report->setReportPeriodEnd(new \DateTimeImmutable('2024-01-31'));
        $report->setSupervisionData([
            '检查机构数量' => 15,
            '发现问题数量' => 8,
            '整改完成率' => '75%',
            '平均评分' => 85.2,
        ]);
        $report->setProblemSummary([
            '教学设备老化' => 3,
            '师资资质不足' => 2,
            '安全管理缺失' => 2,
            '课程设置不合理' => 1,
        ]);
        $report->setRecommendations([
            'equipment' => '加强对培训机构的设备更新指导',
            'teachers' => '建立师资培训和认证体系',
            'safety' => '完善安全管理制度和应急预案',
            'curriculum' => '优化课程设置评估标准',
        ]);
        $report->setStatisticsData([
            '总监督时长' => '120小时',
            '优秀机构比例' => '40%',
            '合格机构比例' => '93%',
            '问题整改率' => '87%',
        ]);
        $report->setReportStatus('已发布');
        $report->setReporter('监督管理部门');
        $report->setReportDate(new \DateTimeImmutable('2024-02-05'));
        $report->setReportContent('本月监督工作总体进展顺利，发现的问题已督促相关机构进行整改');

        $manager->persist($report);
        $this->addReference(self::SUPERVISION_REPORT_REFERENCE, $report);
    }
}

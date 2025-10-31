<?php

namespace Tourze\TrainSupervisorBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;

#[When(env: 'dev')]
class ProblemTrackingFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const PROBLEM_TRACKING_REFERENCE = 'problem-tracking';

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function getDependencies(): array
    {
        return [
            SupervisionInspectionFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createProblemTrackingData($manager);
        $manager->flush();
    }

    private function createProblemTrackingData(ObjectManager $manager): void
    {
        $inspection = $this->getReference(SupervisionInspectionFixtures::SUPERVISION_INSPECTION_REFERENCE, SupervisionInspection::class);

        $problemTracking = new ProblemTracking();
        $problemTracking->setInspection($inspection);
        $problemTracking->setProblemTitle('培训资料更新不及时');
        $problemTracking->setProblemType('教学质量');
        $problemTracking->setProblemDescription('部分培训课程使用的资料版本过旧，未及时更新到最新版本');
        $problemTracking->setProblemSeverity('中等');
        $problemTracking->setProblemStatus('待处理');
        $problemTracking->setDiscoveryDate(new \DateTimeImmutable('2024-01-15'));
        $problemTracking->setExpectedResolutionDate(new \DateTimeImmutable('2024-02-15'));
        $problemTracking->setCorrectionMeasures([
            '建立资料更新机制',
            '指定专人负责资料管理',
            '定期检查资料版本',
        ]);
        $problemTracking->setCorrectionDeadline(new \DateTimeImmutable('2024-02-01'));
        $problemTracking->setCorrectionStatus('待整改');
        $problemTracking->setResponsiblePerson('培训部门负责人');

        $manager->persist($problemTracking);
        $this->addReference(self::PROBLEM_TRACKING_REFERENCE, $problemTracking);
    }
}

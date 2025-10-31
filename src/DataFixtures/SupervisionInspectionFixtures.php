<?php

namespace Tourze\TrainSupervisorBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;

#[When(env: 'dev')]
class SupervisionInspectionFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const SUPERVISION_INSPECTION_REFERENCE = 'supervision-inspection';

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function getDependencies(): array
    {
        return [
            SupervisionPlanFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createSupervisionInspectionData($manager);
        $manager->flush();
    }

    private function createSupervisionInspectionData(ObjectManager $manager): void
    {
        $plan = $this->getReference(SupervisionPlanFixtures::SUPERVISION_PLAN_REFERENCE, SupervisionPlan::class);

        $supervision = new SupervisionInspection();
        $supervision->setPlan($plan);
        $supervision->setInstitutionName('示例培训机构');
        $supervision->setInspectionType('现场检查');
        $supervision->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $supervision->setInspector('监督检查员');
        $supervision->setInspectionItems([
            '教学环境检查',
            '师资资质审核',
            '课程内容评估',
            '学员满意度调查',
            '安全设施检查',
        ]);
        $supervision->setInspectionResults([
            '教学环境检查' => '合格',
            '师资资质审核' => '良好',
            '课程内容评估' => '优秀',
            '学员满意度调查' => '良好',
            '安全设施检查' => '合格',
        ]);
        $supervision->setFoundProblems([
            'equipment' => '部分教学设备需要更新',
            'safety' => '消防器材检查记录不完整',
        ]);
        $supervision->setInspectionStatus('completed');
        $supervision->setOverallScore(85.5);
        $supervision->setInspectionReport('整体情况良好，建议加强设备维护和安全管理');

        $manager->persist($supervision);
        $this->addReference(self::SUPERVISION_INSPECTION_REFERENCE, $supervision);
    }
}

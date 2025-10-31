<?php

namespace Tourze\TrainSupervisorBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;

#[When(env: 'dev')]
class SupervisionPlanFixtures extends Fixture implements FixtureGroupInterface
{
    public const SUPERVISION_PLAN_REFERENCE = 'supervision-plan';

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createSupervisionPlanData($manager);
        $manager->flush();
    }

    private function createSupervisionPlanData(ObjectManager $manager): void
    {
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('2024年第一季度培训监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-03-31'));
        $supervisionPlan->setSupervisionScope([
            '职业技能培训机构',
            '企业内训部门',
            '在线培训平台',
        ]);
        $supervisionPlan->setSupervisionItems([
            '教学质量监督',
            '师资资质检查',
            '培训效果评估',
            '安全管理检查',
            '财务状况审核',
        ]);
        $supervisionPlan->setSupervisor('监督管理部门');
        $supervisionPlan->setPlanStatus('执行中');
        $supervisionPlan->setRemarks('重点关注新注册机构的教学质量');

        $manager->persist($supervisionPlan);
        $this->addReference(self::SUPERVISION_PLAN_REFERENCE, $supervisionPlan);
    }
}

<?php

namespace Tourze\TrainSupervisorBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;

/**
 * 监督数据填充.
 *
 * 用于创建监督记录的测试数据，包括各类培训监督统计信息
 */
#[When(env: 'dev')]
class SupervisorFixtures extends Fixture implements FixtureGroupInterface
{
    public const SUPERVISOR_REFERENCE = 'supervisor';

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createSupervisorData($manager);
        $manager->flush();
    }

    private function createSupervisorData(ObjectManager $manager): void
    {
        $supervisor = new Supervisor();
        $supervisor->setSupervisorName('测试监督员');
        $supervisor->setSupervisorCode('TEST001');
        $supervisor->setDepartment('测试部门');
        $supervisor->setPosition('高级监督员');
        $supervisor->setContactPhone('13800138000');
        $supervisor->setContactEmail('test@local.test');
        $supervisor->setSupervisorLevel('高级');
        $supervisor->setSupervisorStatus('在职');
        $supervisor->setSpecialties('培训监督、质量管理');
        $supervisor->setQualifications('国家级监督员证书');
        $supervisor->setWorkExperience('10年培训监督经验');
        $supervisor->setRemarks('测试数据');

        $manager->persist($supervisor);
        $this->addReference(self::SUPERVISOR_REFERENCE, $supervisor);
    }
}

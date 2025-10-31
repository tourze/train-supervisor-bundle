<?php

namespace Tourze\TrainSupervisorBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;

#[When(env: 'dev')]
class QualityAssessmentFixtures extends Fixture implements FixtureGroupInterface
{
    public const QUALITY_ASSESSMENT_REFERENCE = 'quality-assessment';

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createQualityAssessmentData($manager);
        $manager->flush();
    }

    private function createQualityAssessmentData(ObjectManager $manager): void
    {
        $qualityAssessment = new QualityAssessment();
        $qualityAssessment->setAssessmentType('机构评估');
        $qualityAssessment->setTargetId('test-institution-001');
        $qualityAssessment->setTargetName('示例培训机构');
        $qualityAssessment->setAssessmentCriteria('ISO9001质量管理体系');
        $qualityAssessment->setAssessmentItems([
            'environment' => '教学环境',
            'teachers' => '师资力量',
            'curriculum' => '课程设置',
            'management' => '管理制度',
            'facilities' => '设施设备',
        ]);
        $qualityAssessment->setAssessmentScores([
            '教学环境' => 85,
            '师资力量' => 92,
            '课程设置' => 88,
            '管理制度' => 90,
            '设施设备' => 83,
        ]);
        $qualityAssessment->setTotalScore(87.6);
        $qualityAssessment->setAssessmentLevel('良好');
        $qualityAssessment->setAssessmentComments([
            'overall' => '整体教学质量较高',
            'teachers' => '师资力量雄厚',
            'recommendation' => '建议进一步完善教学设施',
        ]);
        $qualityAssessment->setAssessor('质量评估专员');
        $qualityAssessment->setAssessmentDate(new \DateTimeImmutable('2024-01-20'));
        $qualityAssessment->setAssessmentStatus('已完成');

        $manager->persist($qualityAssessment);
        $this->addReference(self::QUALITY_ASSESSMENT_REFERENCE, $qualityAssessment);
    }
}

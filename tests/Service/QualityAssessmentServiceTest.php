<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;
use Tourze\TrainSupervisorBundle\Exception\QualityAssessmentNotFoundException;
use Tourze\TrainSupervisorBundle\Service\QualityAssessmentService;

/**
 * @internal
 */
#[CoversClass(QualityAssessmentService::class)]
#[RunTestsInSeparateProcesses]
final class QualityAssessmentServiceTest extends AbstractIntegrationTestCase
{
    private QualityAssessmentService $qualityAssessmentService;

    protected function onSetUp(): void
    {
        $this->qualityAssessmentService = self::getService(QualityAssessmentService::class);
    }

    public function testCreateAssessment(): void
    {
        $assessmentData = [
            'assessmentType' => '课程质量评估',
            'institutionId' => 'inst123',
            'targetName' => '测试机构',
            'criteria' => '测试标准',
            'assessor' => '评估员张三',
            'assessmentDate' => new \DateTime('2024-06-15'),
            'items' => ['项目1', '项目2'],
            'scores' => [
                ['value' => 85, 'weight' => 0.5],
                ['value' => 90, 'weight' => 0.5],
            ],
            'comments' => ['评价1', '评价2'],
            'status' => '进行中',
            'remarks' => '测试备注',
        ];

        $result = $this->qualityAssessmentService->createAssessment($assessmentData);

        $this->assertInstanceOf(QualityAssessment::class, $result);
        $this->assertEquals('课程质量评估', $result->getAssessmentType());
        $this->assertEquals('inst123', $result->getTargetId());
        $this->assertEquals('测试机构', $result->getTargetName());
        $this->assertEquals('进行中', $result->getAssessmentStatus());
    }

    public function testUpdateAssessment(): void
    {
        // 先创建一个评估
        $assessmentData = [
            'assessmentType' => '机构评估',
            'institutionId' => 'inst456',
            'targetName' => '更新测试机构',
            'criteria' => '更新标准',
            'assessor' => '评估员李四',
        ];

        $assessment = $this->qualityAssessmentService->createAssessment($assessmentData);

        $updateData = [
            'assessmentStatus' => '已完成',
            'score' => 85.5,
            'assessmentItems' => ['更新项目1', '更新项目2'],
            'assessmentScores' => [
                ['value' => 85, 'weight' => 0.6],
                ['value' => 86, 'weight' => 0.4],
            ],
            'assessmentComments' => ['更新评价1'],
            'remarks' => '更新备注',
        ];

        $assessmentId = $assessment->getId();
        $this->assertNotNull($assessmentId);
        $result = $this->qualityAssessmentService->updateAssessment($assessmentId, $updateData);

        $this->assertInstanceOf(QualityAssessment::class, $result);
        $this->assertEquals('已完成', $result->getAssessmentStatus());
        $this->assertEquals(85.5, $result->getTotalScore());
        $this->assertEquals('良好', $result->getAssessmentLevel());
    }

    public function testUpdateAssessmentNotFound(): void
    {
        $this->expectException(QualityAssessmentNotFoundException::class);
        $this->expectExceptionMessage('质量评估不存在: nonexistent123');

        $this->qualityAssessmentService->updateAssessment('nonexistent123', [
            'assessmentStatus' => '已完成',
        ]);
    }

    public function testCalculateAssessmentScore(): void
    {
        $assessmentData = [
            'assessmentType' => '分数计算测试',
            'institutionId' => 'inst789',
            'targetName' => '计算测试机构',
            'criteria' => '计算标准',
            'assessor' => '评估员王五',
            'assessmentScores' => [
                ['value' => 80, 'weight' => 0.3],
                ['value' => 90, 'weight' => 0.4],
                ['value' => 85, 'weight' => 0.3],
            ],
        ];

        $assessment = $this->qualityAssessmentService->createAssessment($assessmentData);

        $assessmentId = $assessment->getId();
        $this->assertNotNull($assessmentId);
        $result = $this->qualityAssessmentService->calculateAssessmentScore($assessmentId);

        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
        $this->assertEquals($result, $assessment->getTotalScore());
    }

    public function testCalculateAssessmentScoreNotFound(): void
    {
        $this->expectException(QualityAssessmentNotFoundException::class);
        $this->expectExceptionMessage('质量评估不存在: nonexistent123');

        $this->qualityAssessmentService->calculateAssessmentScore('nonexistent123');
    }

    public function testGetAssessmentsByInstitution(): void
    {
        $institutionId = 'inst999';

        // 创建多个评估记录
        $this->qualityAssessmentService->createAssessment([
            'assessmentType' => '机构评估1',
            'institutionId' => $institutionId,
            'targetName' => '查询测试机构1',
            'criteria' => '查询标准1',
            'assessor' => '评估员1',
        ]);

        $this->qualityAssessmentService->createAssessment([
            'assessmentType' => '机构评估2',
            'institutionId' => $institutionId,
            'targetName' => '查询测试机构2',
            'criteria' => '查询标准2',
            'assessor' => '评估员2',
        ]);

        $result = $this->qualityAssessmentService->getAssessmentsByInstitution($institutionId);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(QualityAssessment::class, $result);
    }

    public function testGenerateAssessmentReport(): void
    {
        $assessmentData = [
            'assessmentType' => '报告测试评估',
            'institutionId' => 'inst_report',
            'targetName' => '报告测试机构',
            'criteria' => '报告标准',
            'assessor' => '报告评估员',
            'assessmentItems' => ['报告项目1', '报告项目2'],
            'assessmentScores' => [
                ['value' => 88, 'weight' => 0.5],
                ['value' => 92, 'weight' => 0.5],
            ],
            'assessmentComments' => ['报告评价1', '报告评价2'],
            'remarks' => '报告备注',
        ];

        $assessment = $this->qualityAssessmentService->createAssessment($assessmentData);

        $assessmentId = $assessment->getId();
        $this->assertNotNull($assessmentId);
        $result = $this->qualityAssessmentService->generateAssessmentReport($assessmentId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('assessmentInfo', $result);
        $this->assertArrayHasKey('assessmentItems', $result);
        $this->assertArrayHasKey('assessmentScores', $result);
        $this->assertArrayHasKey('assessmentComments', $result);
        $this->assertArrayHasKey('remarks', $result);
        $this->assertArrayHasKey('generatedAt', $result);
    }

    public function testGenerateAssessmentReportNotFound(): void
    {
        $this->expectException(QualityAssessmentNotFoundException::class);
        $this->expectExceptionMessage('质量评估不存在: nonexistent123');

        $this->qualityAssessmentService->generateAssessmentReport('nonexistent123');
    }

    public function testGetAssessmentStatistics(): void
    {
        // 创建一些测试评估数据
        $this->qualityAssessmentService->createAssessment([
            'assessmentType' => '统计测试1',
            'institutionId' => 'inst_stat1',
            'targetName' => '统计机构1',
            'criteria' => '统计标准',
            'assessor' => '统计评估员',
            'totalScore' => 95,
        ]);

        $this->qualityAssessmentService->createAssessment([
            'assessmentType' => '统计测试2',
            'institutionId' => 'inst_stat2',
            'targetName' => '统计机构2',
            'criteria' => '统计标准',
            'assessor' => '统计评估员',
            'totalScore' => 75,
        ]);

        $result = $this->qualityAssessmentService->getAssessmentStatistics();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_assessments', $result);
        $this->assertArrayHasKey('average_score', $result);
        $this->assertArrayHasKey('max_score', $result);
        $this->assertArrayHasKey('min_score', $result);
        $this->assertArrayHasKey('excellent_rate', $result);
        $this->assertArrayHasKey('good_rate', $result);
        $this->assertArrayHasKey('pass_rate', $result);
        $this->assertArrayHasKey('by_type', $result);
        $this->assertArrayHasKey('by_institution', $result);
    }

    public function testAssessInstitution(): void
    {
        $institutionId = 'inst_assess';
        $criteria = [
            'targetName' => '评估机构测试',
            'criteria' => '机构评估标准',
            'items' => ['师资力量', '教学质量', '设施设备'],
            'scores' => [
                ['value' => 85, 'weight' => 0.4],
                ['value' => 90, 'weight' => 0.4],
                ['value' => 80, 'weight' => 0.2],
            ],
            'comments' => ['师资优秀', '教学质量高', '设施待改善'],
            'assessor' => '机构评估员',
            'assessmentDate' => new \DateTime(),
            'status' => '进行中',
            'remarks' => '综合评估结果',
        ];

        $result = $this->qualityAssessmentService->assessInstitution($institutionId, $criteria);

        $this->assertInstanceOf(QualityAssessment::class, $result);
        $this->assertEquals('机构评估', $result->getAssessmentType());
        $this->assertEquals($institutionId, $result->getTargetId());
        $this->assertEquals('评估机构测试', $result->getTargetName());
    }

    public function testAssessCourse(): void
    {
        $courseId = 'course_assess';
        $criteria = [
            'targetName' => '评估课程测试',
            'criteria' => '课程评估标准',
            'items' => ['课程内容', '教学方法', '学习效果'],
            'scores' => [
                ['value' => 88, 'weight' => 0.5],
                ['value' => 92, 'weight' => 0.3],
                ['value' => 85, 'weight' => 0.2],
            ],
            'comments' => ['内容丰富', '方法灵活', '效果显著'],
            'assessor' => '课程评估员',
            'assessmentDate' => new \DateTime(),
            'status' => '进行中',
            'remarks' => '课程质量优秀',
        ];

        $result = $this->qualityAssessmentService->assessCourse($courseId, $criteria);

        $this->assertInstanceOf(QualityAssessment::class, $result);
        $this->assertEquals('课程评估', $result->getAssessmentType());
        $this->assertEquals($courseId, $result->getTargetId());
        $this->assertEquals('评估课程测试', $result->getTargetName());
    }

    public function testCalculateAssessmentLevel(): void
    {
        $this->assertEquals('优秀', $this->qualityAssessmentService->calculateAssessmentLevel(95));
        $this->assertEquals('良好', $this->qualityAssessmentService->calculateAssessmentLevel(85));
        $this->assertEquals('合格', $this->qualityAssessmentService->calculateAssessmentLevel(75));
        $this->assertEquals('不合格', $this->qualityAssessmentService->calculateAssessmentLevel(65));
    }

    public function testCompleteAssessment(): void
    {
        $assessment = $this->qualityAssessmentService->createAssessment([
            'assessmentType' => '完成测试评估',
            'institutionId' => 'inst_complete',
            'targetName' => '完成测试机构',
            'criteria' => '完成标准',
            'assessor' => '完成评估员',
        ]);

        $assessmentId = $assessment->getId();
        $this->assertNotNull($assessmentId);
        $result = $this->qualityAssessmentService->completeAssessment($assessmentId);

        $this->assertInstanceOf(QualityAssessment::class, $result);
        $this->assertEquals('已完成', $result->getAssessmentStatus());
    }

    public function testCompleteAssessmentNotFound(): void
    {
        $this->expectException(QualityAssessmentNotFoundException::class);
        $this->expectExceptionMessage('质量评估不存在: nonexistent123');

        $this->qualityAssessmentService->completeAssessment('nonexistent123');
    }

    public function testGetCompletedAssessments(): void
    {
        $result = $this->qualityAssessmentService->getCompletedAssessments();

        $this->assertIsArray($result);
    }

    public function testGetAssessmentsByType(): void
    {
        $type = '类型测试评估';

        $this->qualityAssessmentService->createAssessment([
            'assessmentType' => $type,
            'institutionId' => 'inst_type1',
            'targetName' => '类型测试机构1',
            'criteria' => '类型标准',
            'assessor' => '类型评估员',
        ]);

        $result = $this->qualityAssessmentService->getAssessmentsByType($type);

        $this->assertIsArray($result);
    }

    public function testGetAssessmentsByTarget(): void
    {
        $targetId = 'target_test';

        $this->qualityAssessmentService->createAssessment([
            'assessmentType' => '目标测试评估',
            'institutionId' => $targetId,
            'targetName' => '目标测试机构',
            'criteria' => '目标标准',
            'assessor' => '目标评估员',
        ]);

        $result = $this->qualityAssessmentService->getAssessmentsByTarget($targetId);

        $this->assertIsArray($result);
    }

    public function testGetStatisticsByLevel(): void
    {
        $result = $this->qualityAssessmentService->getStatisticsByLevel();

        $this->assertIsArray($result);
    }

    public function testGetFailedAssessments(): void
    {
        $result = $this->qualityAssessmentService->getFailedAssessments();

        $this->assertIsArray($result);
    }

    public function testGetAverageScore(): void
    {
        $result = $this->qualityAssessmentService->getAverageScore();

        $this->assertIsFloat($result);
    }

    public function testExportAssessments(): void
    {
        $result = $this->qualityAssessmentService->exportAssessments();

        $this->assertIsArray($result);
    }
}

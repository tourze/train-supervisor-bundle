<?php

namespace Tourze\TrainSupervisorBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;
use Tourze\TrainSupervisorBundle\Repository\QualityAssessmentRepository;

/**
 * @internal
 */
#[CoversClass(QualityAssessmentRepository::class)]
#[RunTestsInSeparateProcesses]
final class QualityAssessmentRepositoryTest extends AbstractRepositoryTestCase
{
    private QualityAssessmentRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(QualityAssessmentRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(QualityAssessmentRepository::class, $this->repository);
    }

    public function testSaveAndFindQualityAssessment(): void
    {
        $assessment = new QualityAssessment();
        $assessment->setAssessmentType('机构评估');
        $assessment->setTargetId('test-target-123');
        $assessment->setTargetName('测试培训机构');
        $assessment->setAssessmentCriteria('基础评估标准');
        $assessment->setTotalScore(85.5);
        $assessment->setAssessmentLevel('良好');
        $assessment->setAssessor('测试评估员');
        $assessment->setAssessmentDate(new \DateTimeImmutable('2024-01-15'));
        $assessment->setAssessmentStatus('已完成');

        $this->repository->save($assessment);
        $this->assertNotNull($assessment->getId());

        $found = $this->repository->find($assessment->getId());
        $this->assertSame($assessment, $found);
        $this->assertEquals('机构评估', $found->getAssessmentType());
        $this->assertEquals('test-target-123', $found->getTargetId());
    }

    public function testFindCompletedAssessments(): void
    {
        $completedAssessment = $this->createQualityAssessment('已完成');
        $pendingAssessment = $this->createQualityAssessment('进行中');

        $this->repository->save($completedAssessment, false);
        $this->repository->save($pendingAssessment, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findCompletedAssessments();
        $this->assertContainsOnlyInstancesOf(QualityAssessment::class, $results);

        $statuses = array_map(fn ($assessment) => $assessment->getAssessmentStatus(), $results);
        $this->assertContains('已完成', $statuses);
        $this->assertNotContains('进行中', $statuses);
    }

    public function testFindByType(): void
    {
        $institutionAssessment = $this->createQualityAssessment('已完成', '机构评估');
        $courseAssessment = $this->createQualityAssessment('已完成', '课程评估');

        $this->repository->save($institutionAssessment, false);
        $this->repository->save($courseAssessment, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByType('机构评估');
        $this->assertContainsOnlyInstancesOf(QualityAssessment::class, $results);

        $types = array_map(fn ($assessment) => $assessment->getAssessmentType(), $results);
        $this->assertContains('机构评估', $types);
        $this->assertNotContains('课程评估', $types);
    }

    public function testFindByTarget(): void
    {
        $targetId = 'test-target-123';
        $assessment1 = $this->createQualityAssessment('已完成', '机构评估', $targetId);
        $assessment2 = $this->createQualityAssessment('已完成', '课程评估', 'other-target-456');

        $this->repository->save($assessment1, false);
        $this->repository->save($assessment2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByTarget($targetId);
        $this->assertContainsOnlyInstancesOf(QualityAssessment::class, $results);

        $targetIds = array_map(fn ($assessment) => $assessment->getTargetId(), $results);
        $this->assertContains($targetId, $targetIds);
        $this->assertNotContains('other-target-456', $targetIds);
    }

    public function testCountByLevel(): void
    {
        $excellentAssessment = $this->createQualityAssessment('已完成', '机构评估', 'target1', 95.0, '优秀');
        $goodAssessment = $this->createQualityAssessment('已完成', '课程评估', 'target2', 85.0, '良好');
        $failedAssessment = $this->createQualityAssessment('已完成', '机构评估', 'target3', 60.0, '不合格');

        $this->repository->save($excellentAssessment, false);
        $this->repository->save($goodAssessment, false);
        $this->repository->save($failedAssessment, false);
        self::getEntityManager()->flush();

        $results = $this->repository->countByLevel();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);

        // 验证返回的数据结构
        foreach ($results as $result) {
            $this->assertArrayHasKey('assessmentLevel', $result);
            $this->assertArrayHasKey('count', $result);
            $this->assertIsString($result['assessmentLevel']);
            $this->assertIsInt($result['count']);
        }

        $levels = array_column($results, 'assessmentLevel');
        $this->assertContains('优秀', $levels);
        $this->assertContains('良好', $levels);
        $this->assertContains('不合格', $levels);
    }

    public function testFindFailedAssessments(): void
    {
        $passedAssessment = $this->createQualityAssessment('已完成', '机构评估', 'target1', 85.0, '良好');
        $failedAssessment = $this->createQualityAssessment('已完成', '课程评估', 'target2', 60.0, '不合格');

        $this->repository->save($passedAssessment, false);
        $this->repository->save($failedAssessment, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findFailedAssessments();
        $this->assertContainsOnlyInstancesOf(QualityAssessment::class, $results);

        $levels = array_map(fn ($assessment) => $assessment->getAssessmentLevel(), $results);
        $this->assertContains('不合格', $levels);
        $this->assertNotContains('良好', $levels);
    }

    public function testGetAverageScore(): void
    {
        $assessment1 = $this->createQualityAssessment('已完成', '机构评估', 'target1', 80.0);
        $assessment2 = $this->createQualityAssessment('已完成', '课程评估', 'target2', 90.0);
        $assessment3 = $this->createQualityAssessment('进行中', '机构评估', 'target3', 70.0);

        $this->repository->save($assessment1, false);
        $this->repository->save($assessment2, false);
        $this->repository->save($assessment3, false);
        self::getEntityManager()->flush();

        $averageScore = $this->repository->getAverageScore();
        $this->assertIsFloat($averageScore);

        // 应该只计算已完成的评估：(80.0 + 90.0) / 2 = 85.0
        // 由于可能存在其他测试数据，我们只验证基本逻辑
        $this->assertGreaterThanOrEqual(80.0, $averageScore);
        $this->assertLessThanOrEqual(90.0, $averageScore);
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $assessment1 = $this->createQualityAssessment('已完成', '机构评估', 'target1');
        $assessment1->setAssessmentDate(new \DateTimeImmutable('2024-01-15'));

        $assessment2 = $this->createQualityAssessment('已完成', '课程评估', 'target2');
        $assessment2->setAssessmentDate(new \DateTimeImmutable('2024-02-15'));

        $this->repository->save($assessment1, false);
        $this->repository->save($assessment2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByDateRange($startDate, $endDate);
        $this->assertContainsOnlyInstancesOf(QualityAssessment::class, $results);

        $dates = array_map(fn ($assessment) => $assessment->getAssessmentDate(), $results);
        $this->assertContains($assessment1->getAssessmentDate(), $dates);
        $this->assertNotContains($assessment2->getAssessmentDate(), $dates);
    }

    public function testRemoveQualityAssessment(): void
    {
        $assessment = $this->createQualityAssessment('已完成');
        $this->repository->save($assessment);
        $assessmentId = $assessment->getId();

        $this->repository->remove($assessment);

        $found = $this->repository->find($assessmentId);
        $this->assertNull($found);
    }

    protected function createNewEntity(): QualityAssessment
    {
        return $this->createQualityAssessment();
    }

    protected function getRepository(): QualityAssessmentRepository
    {
        return $this->repository;
    }

    private function createQualityAssessment(
        string $status = '已完成',
        string $type = '机构评估',
        ?string $targetId = null,
        float $score = 85.0,
        string $level = '良好',
    ): QualityAssessment {
        $assessment = new QualityAssessment();
        $assessment->setAssessmentType($type);
        $assessment->setTargetId($targetId ?? 'target-' . uniqid());
        $assessment->setTargetName('测试目标');
        $assessment->setAssessmentCriteria('测试标准');
        $assessment->setTotalScore($score);
        $assessment->setAssessmentLevel($level);
        $assessment->setAssessor('测试评估员');
        $assessment->setAssessmentDate(new \DateTimeImmutable());
        $assessment->setAssessmentStatus($status);

        return $assessment;
    }
}

<?php

namespace Tourze\TrainSupervisorBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;

/**
 * @internal
 */
#[CoversClass(ProblemTrackingRepository::class)]
#[RunTestsInSeparateProcesses]
final class ProblemTrackingRepositoryTest extends AbstractRepositoryTestCase
{
    private ProblemTrackingRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ProblemTrackingRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(ProblemTrackingRepository::class, $this->repository);
    }

    public function testSaveAndFindProblemTracking(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = new ProblemTracking();
        $problem->setProblemTitle('测试问题');
        $problem->setProblemType('教学质量问题');
        $problem->setProblemDescription('课程内容不符合要求');
        $problem->setProblemSeverity('高');
        $problem->setCorrectionStatus('待整改');
        $problem->setDiscoveryDate(new \DateTimeImmutable('2024-01-15'));
        $problem->setCorrectionDeadline(new \DateTimeImmutable('2024-02-15'));
        $problem->setResponsiblePerson('测试负责人');
        $problem->setInspection($inspection);

        $this->repository->save($problem);
        $this->assertNotNull($problem->getId());

        $found = $this->repository->find($problem->getId());
        $this->assertSame($problem, $found);
        $this->assertEquals('测试问题', $found->getProblemTitle());
        $this->assertEquals('教学质量问题', $found->getProblemType());
        $this->assertEquals('课程内容不符合要求', $found->getProblemDescription());
    }

    public function testFindOverdueProblems(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $overdueProblem = $this->createProblemTracking('待整改');
        $overdueProblem->setCorrectionDeadline(new \DateTimeImmutable('-1 day'));
        $overdueProblem->setInspection($inspection);

        $currentProblem = $this->createProblemTracking('待整改');
        $currentProblem->setCorrectionDeadline(new \DateTimeImmutable('+7 days'));
        $currentProblem->setInspection($inspection);

        $this->repository->save($overdueProblem, false);
        $this->repository->save($currentProblem, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findOverdueProblems();
        $this->assertContainsOnlyInstancesOf(ProblemTracking::class, $results);

        $deadlines = array_map(fn ($problem) => $problem->getCorrectionDeadline(), $results);
        $this->assertContains($overdueProblem->getCorrectionDeadline(), $deadlines);
        $this->assertNotContains($currentProblem->getCorrectionDeadline(), $deadlines);
    }

    public function testFindPendingProblems(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $pendingProblem = $this->createProblemTracking('待整改');
        $pendingProblem->setInspection($inspection);
        $completedProblem = $this->createProblemTracking('已整改');
        $completedProblem->setInspection($inspection);

        $this->repository->save($pendingProblem, false);
        $this->repository->save($completedProblem, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findPendingProblems();
        $this->assertContainsOnlyInstancesOf(ProblemTracking::class, $results);

        $statuses = array_map(fn ($problem) => $problem->getCorrectionStatus(), $results);
        $this->assertContains('待整改', $statuses);
        $this->assertNotContains('已整改', $statuses);
    }

    public function testCountByType(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $qualityProblem = $this->createProblemTracking('待整改', '教学质量问题');
        $qualityProblem->setInspection($inspection);
        $managementProblem = $this->createProblemTracking('待整改', '管理问题');
        $managementProblem->setInspection($inspection);
        $facilityProblem = $this->createProblemTracking('待整改', '设施问题');
        $facilityProblem->setInspection($inspection);

        $this->repository->save($qualityProblem, false);
        $this->repository->save($managementProblem, false);
        $this->repository->save($facilityProblem, false);
        self::getEntityManager()->flush();

        $results = $this->repository->countByType();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);

        $types = array_column($results, 'problemType');
        $this->assertContains('教学质量问题', $types);
        $this->assertContains('管理问题', $types);
        $this->assertContains('设施问题', $types);
    }

    public function testCountBySeverity(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $highProblem = $this->createProblemTracking('待整改', '教学质量问题', '高');
        $highProblem->setInspection($inspection);
        $mediumProblem = $this->createProblemTracking('待整改', '管理问题', '中');
        $mediumProblem->setInspection($inspection);
        $lowProblem = $this->createProblemTracking('待整改', '设施问题', '低');
        $lowProblem->setInspection($inspection);

        $this->repository->save($highProblem, false);
        $this->repository->save($mediumProblem, false);
        $this->repository->save($lowProblem, false);
        self::getEntityManager()->flush();

        $results = $this->repository->countBySeverity();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);

        $severities = array_column($results, 'problemSeverity');
        $this->assertContains('高', $severities);
        $this->assertContains('中', $severities);
        $this->assertContains('低', $severities);
    }

    public function testFindByInspection(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection1 = new SupervisionInspection();
        $inspection1->setPlan($plan);
        $inspection1->setInstitutionName('测试机构1');
        $inspection1->setInspectionType('现场检查');
        $inspection1->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection1->setInspector('测试检查员');
        $inspection1->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection1);

        $inspection2 = new SupervisionInspection();
        $inspection2->setPlan($plan);
        $inspection2->setInstitutionName('测试机构2');
        $inspection2->setInspectionType('现场检查');
        $inspection2->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection2->setInspector('测试检查员');
        $inspection2->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection2);
        self::getEntityManager()->flush();

        $problem1 = $this->createProblemTracking('待整改');
        $problem1->setInspection($inspection1);

        $problem2 = $this->createProblemTracking('待整改');
        $problem2->setInspection($inspection2);

        $this->repository->save($problem1, false);
        $this->repository->save($problem2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByInspection((string) $inspection1->getId());
        $this->assertContainsOnlyInstancesOf(ProblemTracking::class, $results);

        $inspectionIds = array_map(fn ($problem) => $problem->getInspection()->getId(), $results);
        $this->assertContains($inspection1->getId(), $inspectionIds);
        $this->assertNotContains($inspection2->getId(), $inspectionIds);
    }

    public function testFindVerifiedProblems(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $verifiedProblem = $this->createProblemTracking('已整改');
        $verifiedProblem->setInspection($inspection);
        $verifiedProblem->setVerificationResult('通过');
        $unverifiedProblem = $this->createProblemTracking('待整改');
        $unverifiedProblem->setInspection($inspection);
        $unverifiedProblem->setVerificationResult('不通过');

        $this->repository->save($verifiedProblem, false);
        $this->repository->save($unverifiedProblem, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findVerifiedProblems();
        $this->assertContainsOnlyInstancesOf(ProblemTracking::class, $results);

        $verificationResults = array_map(fn ($problem) => $problem->getVerificationResult(), $results);
        $this->assertContains('通过', $verificationResults);
        $this->assertNotContains('不通过', $verificationResults);
    }

    public function testFindByResponsiblePerson(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $responsiblePerson = '张三';

        $problem1 = $this->createProblemTracking('待整改');
        $problem1->setResponsiblePerson($responsiblePerson);
        $problem1->setInspection($inspection);

        $problem2 = $this->createProblemTracking('待整改');
        $problem2->setResponsiblePerson('李四');
        $problem2->setInspection($inspection);

        $this->repository->save($problem1, false);
        $this->repository->save($problem2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByResponsiblePerson($responsiblePerson);
        $this->assertContainsOnlyInstancesOf(ProblemTracking::class, $results);

        $persons = array_map(fn ($problem) => $problem->getResponsiblePerson(), $results);
        $this->assertContains($responsiblePerson, $persons);
        $this->assertNotContains('李四', $persons);
    }

    public function testGetCorrectionRate(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $correctedProblem1 = $this->createProblemTracking('已整改');
        $correctedProblem1->setInspection($inspection);
        $correctedProblem2 = $this->createProblemTracking('已整改');
        $correctedProblem2->setInspection($inspection);
        $pendingProblem = $this->createProblemTracking('待整改');
        $pendingProblem->setInspection($inspection);

        $this->repository->save($correctedProblem1, false);
        $this->repository->save($correctedProblem2, false);
        $this->repository->save($pendingProblem, false);
        self::getEntityManager()->flush();

        $correctionRate = $this->repository->getCorrectionRate();
        $this->assertGreaterThanOrEqual(0.0, $correctionRate);
        $this->assertLessThanOrEqual(100.0, $correctionRate);

        // 应该大于0且小于等于100，并且有合理值
        $this->assertGreaterThan(0, $correctionRate);
        $this->assertLessThanOrEqual(100.0, $correctionRate);
    }

    public function testFindByDateRange(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $problem1 = $this->createProblemTracking('待整改');
        $problem1->setDiscoveryDate(new \DateTimeImmutable('2024-01-15'));
        $problem1->setInspection($inspection);

        $problem2 = $this->createProblemTracking('待整改');
        $problem2->setDiscoveryDate(new \DateTimeImmutable('2024-02-15'));
        $problem2->setInspection($inspection);

        $this->repository->save($problem1, false);
        $this->repository->save($problem2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByDateRange($startDate, $endDate);
        $this->assertContainsOnlyInstancesOf(ProblemTracking::class, $results);

        $discoveryDates = array_map(fn ($problem) => $problem->getDiscoveryDate(), $results);
        $this->assertContains($problem1->getDiscoveryDate(), $discoveryDates);
        $this->assertNotContains($problem2->getDiscoveryDate(), $discoveryDates);
    }

    public function testRemoveProblemTracking(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->createProblemTracking('待整改');
        $problem->setInspection($inspection);
        $this->repository->save($problem);
        $problemId = $problem->getId();

        $this->repository->remove($problem);

        $found = $this->repository->find($problemId);
        $this->assertNull($found);
    }

    protected function createNewEntity(): ProblemTracking
    {
        return $this->createProblemTracking();
    }

    protected function getRepository(): ProblemTrackingRepository
    {
        return $this->repository;
    }

    private function createProblemTracking(
        string $status = '待整改',
        string $type = '教学质量问题',
        string $severity = '中',
        ?SupervisionInspection $inspection = null,
    ): ProblemTracking {
        // 如果没有提供 inspection，创建一个默认的
        if (null === $inspection) {
            $plan = new SupervisionPlan();
            $plan->setPlanName('默认测试计划');
            $plan->setPlanType('定期');
            $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
            $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
            $plan->setPlanStatus('active');
            $plan->setSupervisor('默认监督员');
            self::getEntityManager()->persist($plan);

            $inspection = new SupervisionInspection();
            $inspection->setPlan($plan);
            $inspection->setInstitutionName('默认测试机构');
            $inspection->setInspectionType('现场检查');
            $inspection->setInspectionDate(new \DateTimeImmutable());
            $inspection->setInspector('默认检查员');
            $inspection->setInspectionStatus('completed');
            self::getEntityManager()->persist($inspection);
            self::getEntityManager()->flush();
        }

        $problem = new ProblemTracking();
        $problem->setProblemTitle('测试问题标题');
        $problem->setProblemType($type);
        $problem->setProblemDescription('测试问题描述');
        $problem->setProblemSeverity($severity);
        $problem->setCorrectionStatus($status);
        $problem->setDiscoveryDate(new \DateTimeImmutable());
        $problem->setCorrectionDeadline(new \DateTimeImmutable('+7 days'));
        $problem->setResponsiblePerson('测试负责人');
        $problem->setInspection($inspection);

        return $problem;
    }
}

<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Exception\InvalidProblemStatusException;
use Tourze\TrainSupervisorBundle\Service\ProblemTrackingService;

/**
 * @internal
 */
#[CoversClass(ProblemTrackingService::class)]
#[RunTestsInSeparateProcesses]
final class ProblemTrackingServiceTest extends AbstractIntegrationTestCase
{
    private ProblemTrackingService $problemTrackingService;

    protected function onSetUp(): void
    {
        $this->problemTrackingService = self::getService(ProblemTrackingService::class);
    }

    public function testCreateProblem(): void
    {
        // 创建监督计划
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('常规监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('常规检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problemType = '质量问题';
        $description = '测试问题描述';
        $severity = '高';
        $deadline = new \DateTimeImmutable('+7 days');
        $responsiblePerson = '张三';
        $measures = ['整改措施1', '整改措施2'];

        $result = $this->problemTrackingService->createProblem(
            $inspection,
            $problemType,
            $description,
            $severity,
            $deadline,
            $responsiblePerson,
            $measures
        );

        $this->assertInstanceOf(ProblemTracking::class, $result);
        $this->assertEquals($problemType, $result->getProblemType());
        $this->assertEquals($description, $result->getProblemDescription());
        $this->assertEquals($severity, $result->getProblemSeverity());
        $this->assertEquals($responsiblePerson, $result->getResponsiblePerson());
        $this->assertEquals('待整改', $result->getCorrectionStatus());
    }

    public function testCreateProblemsFromInspection(): void
    {
        // 创建监督计划
        $plan = new SupervisionPlan();
        $plan->setPlanName('批量测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('批量测试机构');
        $inspection->setInspectionType('专项检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problemsData = [
            [
                'type' => '设备问题',
                'description' => '设备老旧',
                'severity' => '中',
                'deadline' => new \DateTimeImmutable('+10 days'),
                'responsible_person' => '李四',
                'measures' => ['更换设备'],
            ],
            [
                'type' => '人员问题',
                'description' => '人员不足',
                'severity' => '高',
                'deadline' => new \DateTimeImmutable('+5 days'),
                'responsible_person' => '王五',
                'measures' => ['增加人员'],
            ],
        ];

        $result = $this->problemTrackingService->createProblemsFromInspection($inspection, $problemsData);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ProblemTracking::class, $result);
        $this->assertEquals('设备问题', $result[0]->getProblemType());
        $this->assertEquals('人员问题', $result[1]->getProblemType());
    }

    public function testUpdateProblemStatus(): void
    {
        // 创建监督计划
        $plan = new SupervisionPlan();
        $plan->setPlanName('状态测试计划');
        $plan->setPlanType('常规监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        // 先创建一个问题
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('状态测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '测试人员'
        );

        $newStatus = '已解决';
        $problemId = $problem->getId();
        $this->assertNotNull($problemId);
        $result = $this->problemTrackingService->updateProblemStatus($problemId, $newStatus);

        $this->assertInstanceOf(ProblemTracking::class, $result);
        $this->assertEquals($newStatus, $result->getCorrectionStatus());
    }

    public function testUpdateProblemStatusNotFound(): void
    {
        $this->expectException(InvalidProblemStatusException::class);
        $this->expectExceptionMessage('问题不存在: nonexistent123');

        $this->problemTrackingService->updateProblemStatus('nonexistent123', '已解决');
    }

    public function testGetOverdueProblems(): void
    {
        $result = $this->problemTrackingService->getOverdueProblems();

        $this->assertIsArray($result);
    }

    public function testGetProblemsByInspection(): void
    {
        // 创建监督计划
        $plan = new SupervisionPlan();
        $plan->setPlanName('查询测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        // 创建测试检查和问题
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('查询测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题1',
            '测试描述1',
            '高',
            new \DateTimeImmutable('+7 days'),
            '测试人员1'
        );

        $inspectionId = $inspection->getId();
        $this->assertNotNull($inspectionId);
        $result = $this->problemTrackingService->getProblemsByInspection($inspectionId);

        $this->assertIsArray($result);
    }

    public function testAssignProblem(): void
    {
        // 创建监督计划
        $plan = new SupervisionPlan();
        $plan->setPlanName('分配测试计划');
        $plan->setPlanType('常规监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        // 先创建一个问题
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('分配测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '原始负责人'
        );

        $newAssignee = '新负责人';
        $problemId = $problem->getId();
        $this->assertNotNull($problemId);
        $result = $this->problemTrackingService->assignProblem($problemId, $newAssignee);

        $this->assertInstanceOf(ProblemTracking::class, $result);
        $this->assertEquals($newAssignee, $result->getResponsiblePerson());
    }

    public function testAssignProblemNotFound(): void
    {
        $this->expectException(InvalidProblemStatusException::class);
        $this->expectExceptionMessage('问题不存在: nonexistent123');

        $this->problemTrackingService->assignProblem('nonexistent123', '新负责人');
    }

    public function testAssignProblemToSamePerson(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('重复分配测试计划');
        $plan->setPlanType('常规监督');
        $plan->setPlanStartDate(new \DateTimeImmutable());
        $plan->setPlanEndDate(new \DateTimeImmutable('+30 days'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('进行中');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('重复分配测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $assignee = '原负责人';
        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            $assignee
        );

        $this->expectException(InvalidProblemStatusException::class);
        $this->expectExceptionMessage('问题已分配给该负责人，无需重复分配');

        $problemId = $problem->getId();
        $this->assertNotNull($problemId);
        $this->problemTrackingService->assignProblem($problemId, $assignee);
    }

    public function testAssignProblemInvalidStatus(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('状态检查测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable());
        $plan->setPlanEndDate(new \DateTimeImmutable('+30 days'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('进行中');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('状态检查测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '原负责人'
        );

        // 将问题状态设置为不允许重新分配的状态
        $this->problemTrackingService->submitCorrectionEvidence($problem, ['evidence' => '测试证据']);
        $this->problemTrackingService->verifyCorrection($problem, '通过', '验证人');
        $this->problemTrackingService->closeProblem($problem);

        $this->expectException(InvalidProblemStatusException::class);
        $this->expectExceptionMessage("问题状态为'已关闭'，不允许重新分配负责人");

        $problemId = $problem->getId();
        $this->assertNotNull($problemId);
        $this->problemTrackingService->assignProblem($problemId, '新负责人');
    }

    public function testGetProblemStatistics(): void
    {
        $result = $this->problemTrackingService->getProblemStatistics();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('by_status', $result);
        $this->assertArrayHasKey('by_severity', $result);
        $this->assertArrayHasKey('by_type', $result);
        $this->assertArrayHasKey('overdue', $result);
        $this->assertArrayHasKey('upcoming_overdue', $result);
        $this->assertArrayHasKey('resolution_rate', $result);
    }

    public function testStartCorrection(): void
    {
        // 创建监督计划
        $plan = new SupervisionPlan();
        $plan->setPlanName('整改测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('整改测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '测试人员'
        );

        $measures = ['整改措施1', '整改措施2'];
        $this->problemTrackingService->startCorrection($problem, $measures);

        $this->assertEquals('整改中', $problem->getCorrectionStatus());
        $this->assertEquals($measures, $problem->getCorrectionMeasures());
    }

    public function testSubmitCorrectionEvidence(): void
    {
        // 创建监督计划
        $plan = new SupervisionPlan();
        $plan->setPlanName('证据测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('证据测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '测试人员'
        );

        $evidence = ['documents' => ['证据1', '证据2'], 'type' => '整改证据'];
        $correctionDate = new \DateTimeImmutable();
        $this->problemTrackingService->submitCorrectionEvidence($problem, $evidence, $correctionDate);

        $this->assertEquals('已整改', $problem->getCorrectionStatus());
        $this->assertEquals($evidence, $problem->getCorrectionEvidence());
        $this->assertEquals($correctionDate, $problem->getCorrectionDate());
    }

    public function testVerifyCorrection(): void
    {
        // 创建监督计划
        $plan = new SupervisionPlan();
        $plan->setPlanName('验证测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('验证测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '测试人员'
        );

        $verificationResult = '通过';
        $verifier = '验证人员';
        $verificationDate = new \DateTimeImmutable();
        $this->problemTrackingService->verifyCorrection($problem, $verificationResult, $verifier, $verificationDate);

        $this->assertEquals('已验证', $problem->getCorrectionStatus());
        $this->assertEquals($verificationResult, $problem->getVerificationResult());
        $this->assertEquals($verifier, $problem->getVerifier());
        $this->assertEquals($verificationDate, $problem->getVerificationDate());
    }

    public function testBatchAssignResponsiblePerson(): void
    {
        // 创建监督计划和检查
        $plan = new SupervisionPlan();
        $plan->setPlanName('批量分配测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('批量分配测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        // 创建多个问题
        $problem1 = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题1',
            '测试描述1',
            '高',
            new \DateTimeImmutable('+7 days'),
            '原负责人'
        );

        $problem2 = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题2',
            '测试描述2',
            '中',
            new \DateTimeImmutable('+7 days'),
            '原负责人'
        );

        $problem1Id = $problem1->getId();
        $problem2Id = $problem2->getId();
        $this->assertNotNull($problem1Id);
        $this->assertNotNull($problem2Id);

        $problemIds = [$problem1Id, $problem2Id];
        $newResponsiblePerson = '新负责人';

        $result = $this->problemTrackingService->batchAssignResponsiblePerson($problemIds, $newResponsiblePerson);

        $this->assertIsInt($result);
        $this->assertEquals(2, $result);
    }

    public function testBatchUpdateStatus(): void
    {
        // 创建监督计划和检查
        $plan = new SupervisionPlan();
        $plan->setPlanName('批量状态测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('批量状态测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        // 创建多个问题
        $problem1 = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题1',
            '测试描述1',
            '高',
            new \DateTimeImmutable('+7 days'),
            '负责人'
        );

        $problem2 = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题2',
            '测试描述2',
            '中',
            new \DateTimeImmutable('+7 days'),
            '负责人'
        );

        $problem1Id = $problem1->getId();
        $problem2Id = $problem2->getId();
        $this->assertNotNull($problem1Id);
        $this->assertNotNull($problem2Id);

        $problemIds = [$problem1Id, $problem2Id];
        $newStatus = '已解决';

        $result = $this->problemTrackingService->batchUpdateStatus($problemIds, $newStatus);

        $this->assertIsInt($result);
        $this->assertEquals(2, $result);
    }

    public function testCloseProblem(): void
    {
        // 创建监督计划和检查
        $plan = new SupervisionPlan();
        $plan->setPlanName('关闭测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('关闭测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '测试人员'
        );

        // 先提交整改证据
        $this->problemTrackingService->submitCorrectionEvidence($problem, ['measure' => '整改措施'], new \DateTimeImmutable());

        // 然后验证通过
        $this->problemTrackingService->verifyCorrection($problem, '通过', '验证人员');

        $remarks = '问题已关闭';
        $this->problemTrackingService->closeProblem($problem, $remarks);

        $this->assertEquals('已关闭', $problem->getCorrectionStatus());
    }

    public function testExportProblems(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-12-31');
        $filters = [];

        $result = $this->problemTrackingService->exportProblems($startDate, $endDate, $filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('mime_type', $result);
    }

    public function testExtendDeadline(): void
    {
        // 创建监督计划和检查
        $plan = new SupervisionPlan();
        $plan->setPlanName('延期测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('延期测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '测试人员'
        );

        $newDeadline = new \DateTimeImmutable('+14 days');
        $reason = '需要更多时间处理';
        $this->problemTrackingService->extendDeadline($problem, $newDeadline, $reason);

        $this->assertEquals($newDeadline, $problem->getCorrectionDeadline());
    }

    public function testGenerateTrackingReport(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-12-31');

        $result = $this->problemTrackingService->generateTrackingReport($startDate, $endDate);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('problems', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('generatedAt', $result);
    }

    public function testReopenProblem(): void
    {
        // 创建监督计划和检查
        $plan = new SupervisionPlan();
        $plan->setPlanName('重开测试计划');
        $plan->setPlanType('专项监督');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        self::getEntityManager()->persist($plan);

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('重开测试机构');
        $inspection->setInspectionType('测试检查');
        $inspection->setInspector('测试人员');
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspectionItems(['测试项目']);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $problem = $this->problemTrackingService->createProblem(
            $inspection,
            '测试问题',
            '测试描述',
            '中',
            new \DateTimeImmutable('+7 days'),
            '测试人员'
        );

        // 完成问题整改流程
        $this->problemTrackingService->startCorrection($problem);
        $this->problemTrackingService->submitCorrectionEvidence($problem, ['evidence' => '证据']);
        $this->problemTrackingService->verifyCorrection($problem, '通过', '验证员');

        // 然后关闭问题
        $this->problemTrackingService->closeProblem($problem);

        // 然后重新打开
        $reason = '问题未完全解决';
        $this->problemTrackingService->reopenProblem($problem, $reason);

        $this->assertEquals('整改中', $problem->getCorrectionStatus());
    }

    public function testSendOverdueReminders(): void
    {
        $result = $this->problemTrackingService->sendOverdueReminders();

        $this->assertIsArray($result);
    }
}

<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Exception\InspectionNotFoundException;
use Tourze\TrainSupervisorBundle\Service\InspectionService;

/**
 * 检查服务测试.
 *
 * @internal
 */
#[CoversClass(InspectionService::class)]
#[RunTestsInSeparateProcesses]
final class InspectionServiceTest extends AbstractIntegrationTestCase
{
    private InspectionService $inspectionService;

    protected function onSetUp(): void
    {
        $this->inspectionService = self::getService(InspectionService::class);
    }

    public function testCreateInspection(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');

        self::getEntityManager()->persist($plan);
        self::getEntityManager()->flush();

        $inspectionData = [
            'plan' => $plan,
            'inspectionType' => '现场检查',
            'inspectionDate' => new \DateTimeImmutable('2024-06-15'),
            'inspector' => '张三',
            'institutionName' => '测试机构',
        ];

        $result = $this->inspectionService->createInspection($inspectionData);

        $this->assertInstanceOf(SupervisionInspection::class, $result);
        $this->assertEquals($plan, $result->getPlan());
        $this->assertEquals('现场检查', $result->getInspectionType());
    }

    public function testUpdateInspection(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');

        self::getEntityManager()->persist($plan);
        self::getEntityManager()->flush();

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-06-15'));
        $inspection->setInspector('张三');
        $inspection->setInspectionStatus('进行中');
        $inspection->setInstitutionName('测试机构');
        $inspection->setSupplierId(123);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $updateData = [
            'inspectionStatus' => '已完成',
            'overallScore' => 85.5,
            'inspectionReport' => '检查报告内容',
        ];

        $inspectionId = $inspection->getId();
        $this->assertNotNull($inspectionId);
        $result = $this->inspectionService->updateInspection($inspectionId, $updateData);

        $this->assertInstanceOf(SupervisionInspection::class, $result);
        $this->assertEquals('已完成', $result->getInspectionStatus());
        $this->assertEquals(85.5, $result->getOverallScore());
    }

    public function testUpdateInspectionResults(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');

        self::getEntityManager()->persist($plan);
        self::getEntityManager()->flush();

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-06-15'));
        $inspection->setInspector('张三');
        $inspection->setInspectionStatus('进行中');
        $inspection->setInstitutionName('测试机构');
        $inspection->setSupplierId(123);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $results = [
            'inspectionResults' => [
                ['item' => '消防安全', 'score' => 90, 'weight' => 0.3],
                ['item' => '教学质量', 'score' => 85, 'weight' => 0.7],
            ],
            'foundProblems' => [
                ['type' => '安全隐患', 'description' => '灭火器过期'],
            ],
            'overallScore' => 86.5,
            'inspectionReport' => '整体情况良好，存在少量问题',
            'remarks' => '建议及时更换消防设备',
        ];

        $inspectionId = $inspection->getId();
        $this->assertNotNull($inspectionId);
        $result = $this->inspectionService->updateInspectionResults($inspectionId, $results);

        $this->assertInstanceOf(SupervisionInspection::class, $result);
        $this->assertEquals(86.5, $result->getOverallScore());
        $this->assertNotEmpty($result->getInspectionResults());
        $this->assertNotEmpty($result->getFoundProblems());
        $this->assertEquals('整体情况良好，存在少量问题', $result->getInspectionReport());
    }

    public function testUpdateNonExistentInspection(): void
    {
        $this->expectException(InspectionNotFoundException::class);

        $updateData = [
            'inspectionStatus' => '已完成',
            'overallScore' => 85.5,
        ];

        $this->inspectionService->updateInspection('nonexistent-id', $updateData);
    }

    public function testCreateInspectionsFromPlan(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $date = new \DateTime('2024-06-15');

        $result = $this->inspectionService->createInspectionsFromPlan($plan, $date);

        $this->assertIsArray($result);
        $this->assertEmpty($result); // 当前实现返回空数组
    }

    public function testGetInspectionsWithProblems(): void
    {
        $result = $this->inspectionService->getInspectionsWithProblems();

        $this->assertIsArray($result);
    }

    public function testGetStatisticsByType(): void
    {
        $result = $this->inspectionService->getStatisticsByType();

        $this->assertIsArray($result);
    }

    public function testAnalyzeInspectionTrends(): void
    {
        $result = $this->inspectionService->analyzeInspectionTrends(30);

        $this->assertArrayHasKey('period', $result);
        $this->assertArrayHasKey('inspectionTrends', $result);
        $this->assertArrayHasKey('problemTrends', $result);
        $this->assertArrayHasKey('scoreTrends', $result);
        $this->assertArrayHasKey('summary', $result);

        $this->assertIsArray($result['period']);
        $this->assertArrayHasKey('days', $result['period']);
        $this->assertEquals(30, $result['period']['days']);
    }

    public function testCalculateInspectionScore(): void
    {
        // 创建测试数据
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');

        self::getEntityManager()->persist($plan);
        self::getEntityManager()->flush();

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-06-15'));
        $inspection->setInspector('张三');
        $inspection->setInspectionStatus('已完成');
        $inspection->setInstitutionName('测试机构');
        $inspection->setSupplierId(123);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $inspectionId = $inspection->getId();
        $this->assertNotNull($inspectionId);

        $result = $this->inspectionService->calculateInspectionScore($inspectionId);

        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testCompleteInspection(): void
    {
        // 创建测试数据
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');

        self::getEntityManager()->persist($plan);
        self::getEntityManager()->flush();

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-06-15'));
        $inspection->setInspector('张三');
        $inspection->setInspectionStatus('进行中');
        $inspection->setInstitutionName('测试机构');
        $inspection->setSupplierId(123);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $inspectionId = $inspection->getId();
        $this->assertNotNull($inspectionId);

        $result = $this->inspectionService->completeInspection($inspectionId);

        $this->assertInstanceOf(SupervisionInspection::class, $result);
        $this->assertEquals('已完成', $result->getInspectionStatus());
    }

    public function testConductInspection(): void
    {
        // 创建测试数据
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');

        self::getEntityManager()->persist($plan);
        self::getEntityManager()->flush();

        $planId = $plan->getId();
        $this->assertNotNull($planId);

        $inspectionData = [
            'inspectionType' => '现场检查',
            'inspectionDate' => new \DateTimeImmutable('2024-06-15'),
            'inspector' => '张三',
            'institutionName' => '测试机构',
        ];

        $result = $this->inspectionService->conductInspection($planId, 'institution-123', $inspectionData);

        $this->assertInstanceOf(SupervisionInspection::class, $result);
        $this->assertEquals('进行中', $result->getInspectionStatus());
    }

    public function testGenerateInspectionReport(): void
    {
        // 创建测试数据
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');

        self::getEntityManager()->persist($plan);
        self::getEntityManager()->flush();

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-06-15'));
        $inspection->setInspector('张三');
        $inspection->setInspectionStatus('已完成');
        $inspection->setInstitutionName('测试机构');
        $inspection->setSupplierId(123);
        $inspection->setOverallScore(85.5);

        self::getEntityManager()->persist($inspection);
        self::getEntityManager()->flush();

        $inspectionId = $inspection->getId();
        $this->assertNotNull($inspectionId);

        $result = $this->inspectionService->generateInspectionReport($inspectionId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('inspectionInfo', $result);
        $this->assertArrayHasKey('overallScore', $result);
        $this->assertArrayHasKey('generatedAt', $result);
    }
}

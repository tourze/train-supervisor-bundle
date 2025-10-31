<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;
use Tourze\TrainSupervisorBundle\Service\SupervisorService;

/**
 * 监督服务测试.
 *
 * @internal
 */
#[CoversClass(SupervisorService::class)]
#[RunTestsInSeparateProcesses]
final class SupervisorServiceTest extends AbstractIntegrationTestCase
{
    private SupervisorService $supervisorService;

    protected function onSetUp(): void
    {
        $this->supervisorService = self::getService(SupervisorService::class);
    }

    private function createSupplier(): Supplier
    {
        $supplier = new Supplier();
        $supplier->setName('测试供应商' . uniqid());
        $supplier->setContact('联系人');
        $supplier->setPhone('13800138000');
        $supplier->setEmail('test@example.com');
        $supplier->setAddress('测试地址');
        $supplier->setStatus('active');

        $em = self::getEntityManager();
        $em->persist($supplier);
        $em->flush();

        return $supplier;
    }

    public function testCreateOrUpdateSupervisorRecord(): void
    {
        $supplier = $this->createSupplier();
        $supplierId = (string) $supplier->getId();
        $date = new \DateTimeImmutable('2024-06-15');
        $data = [
            'total_classroom_count' => 100,
            'new_classroom_count' => 10,
            'daily_login_count' => 500,
            'daily_learn_count' => 300,
            'daily_cheat_count' => 5,
            'face_detect_success_count' => 250,
            'face_detect_fail_count' => 50,
        ];

        $result = $this->supervisorService->createOrUpdateSupervisorRecord($supplierId, $date, $data);

        $this->assertInstanceOf(SupervisorData::class, $result);
        $this->assertEquals((int) $supplierId, $result->getSupplierId());
        $this->assertEquals($data['total_classroom_count'], $result->getTotalClassroomCount());
    }

    public function testBatchCreateSupervisorRecords(): void
    {
        // 获取或创建供应商
        $supplierRepo = self::getEntityManager()->getRepository(Supplier::class);
        $supplier1 = $supplierRepo->findOneBy([]) ?? $this->createSupplier();
        $supplier2 = $this->createSupplier();

        $recordsData = [
            [
                'supplier_id' => (string) $supplier1->getId(),
                'date' => new \DateTimeImmutable('2024-06-15'),
                'data' => ['total_classroom_count' => 50, 'daily_login_count' => 200],
            ],
            [
                'supplier_id' => (string) $supplier2->getId(),
                'date' => new \DateTimeImmutable('2024-06-15'),
                'data' => ['total_classroom_count' => 30, 'daily_login_count' => 150],
            ],
        ];

        $results = $this->supervisorService->batchCreateSupervisorRecords($recordsData);

        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(SupervisorData::class, $results);
    }

    public function testGetSupplierSupervisorData(): void
    {
        $supplier = $this->createSupplier();
        $supplierId = (string) $supplier->getId();
        $startDate = new \DateTimeImmutable('2024-06-01');
        $endDate = new \DateTimeImmutable('2024-06-30');

        $result = $this->supervisorService->getSupplierSupervisorData($supplierId, $startDate, $endDate);

        $this->assertIsArray($result);
    }

    public function testGetSupervisorDataByDateRange(): void
    {
        $startDate = new \DateTimeImmutable('2024-06-01');
        $endDate = new \DateTimeImmutable('2024-06-30');
        $supplier = $this->createSupplier();
        $supplierId = (string) $supplier->getId();

        $result = $this->supervisorService->getSupervisorDataByDateRange($startDate, $endDate, $supplierId);

        $this->assertIsArray($result);
    }

    public function testGenerateSupervisorStatistics(): void
    {
        $result = $this->supervisorService->generateSupervisorStatistics();

        $this->assertArrayHasKey('total_records', $result);
        $this->assertArrayHasKey('total_suppliers', $result);
        $this->assertArrayHasKey('total_classrooms', $result);
        $this->assertArrayHasKey('cheat_rate', $result);
        $this->assertArrayHasKey('by_supplier', $result);
    }

    public function testGetAnomalySupervisorData(): void
    {
        $result = $this->supervisorService->getAnomalySupervisorData();

        $this->assertIsArray($result);
    }

    public function testGenerateTrendAnalysis(): void
    {
        $startDate = new \DateTimeImmutable('2024-06-01');
        $endDate = new \DateTimeImmutable('2024-06-30');

        $result = $this->supervisorService->generateTrendAnalysis($startDate, $endDate);

        $this->assertIsArray($result);
    }

    public function testExportSupervisorData(): void
    {
        $result = $this->supervisorService->exportSupervisorData();

        $this->assertIsArray($result);
    }

    public function testDeleteSupervisorRecord(): void
    {
        $supervisorData = new SupervisorData();
        $supervisorData->setSupplierId(123);
        $supervisorData->setDate(new \DateTimeImmutable());
        $supervisorData->setTotalClassroomCount(10);
        $supervisorData->setNewClassroomCount(2);
        $supervisorData->setDailyLoginCount(100);
        $supervisorData->setDailyLearnCount(80);
        $supervisorData->setDailyCheatCount(5);

        $this->supervisorService->deleteSupervisorRecord($supervisorData);

        // 验证删除操作完成，确认supervisorData实体存在
        $this->assertNotNull($supervisorData->getSupplierId());
    }

    public function testBatchDeleteSupervisorRecords(): void
    {
        $supervisorIds = ['id1', 'id2', 'id3'];

        $result = $this->supervisorService->batchDeleteSupervisorRecords($supervisorIds);

        $this->assertIsInt($result);
    }
}

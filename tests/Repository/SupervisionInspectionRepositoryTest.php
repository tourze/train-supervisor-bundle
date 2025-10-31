<?php

namespace Tourze\TrainSupervisorBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Repository\SupervisionInspectionRepository;

/**
 * @internal
 */
#[CoversClass(SupervisionInspectionRepository::class)]
#[RunTestsInSeparateProcesses]
final class SupervisionInspectionRepositoryTest extends AbstractRepositoryTestCase
{
    private SupervisionInspectionRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SupervisionInspectionRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(SupervisionInspectionRepository::class, $this->repository);
    }

    public function testSaveAndFindSupervisionInspection(): void
    {
        $plan = $this->createSupervisionPlan();
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试培训机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-15'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('已完成');
        $inspection->setOverallScore(85.5);
        $inspection->setSupplierId(123);

        $this->repository->save($inspection);
        $this->assertNotNull($inspection->getId());

        $found = $this->repository->find($inspection->getId());
        $this->assertSame($inspection, $found);
        $this->assertEquals('测试培训机构', $found->getInstitutionName());
        $this->assertEquals('现场检查', $found->getInspectionType());
    }

    public function testFindCompletedInspections(): void
    {
        $plan = $this->createSupervisionPlan();

        $completedInspection = $this->createSupervisionInspection($plan, '已完成');
        $pendingInspection = $this->createSupervisionInspection($plan, '进行中');

        $this->repository->save($completedInspection, false);
        $this->repository->save($pendingInspection, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findCompletedInspections();
        $this->assertContainsOnlyInstancesOf(SupervisionInspection::class, $results);

        $statuses = array_map(fn ($inspection) => $inspection->getInspectionStatus(), $results);
        $this->assertContains('已完成', $statuses);
        $this->assertNotContains('进行中', $statuses);
    }

    public function testFindByInstitution(): void
    {
        $plan = $this->createSupervisionPlan();
        $institutionName = '测试培训机构A';

        $inspection1 = $this->createSupervisionInspection($plan, '已完成');
        $inspection1->setInstitutionName($institutionName);
        $inspection2 = $this->createSupervisionInspection($plan, '已完成');
        $inspection2->setInstitutionName('测试培训机构B');

        $this->repository->save($inspection1, false);
        $this->repository->save($inspection2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByInstitution($institutionName);
        $this->assertContainsOnlyInstancesOf(SupervisionInspection::class, $results);

        $institutionNames = array_map(fn ($inspection) => $inspection->getInstitutionName(), $results);
        $this->assertContains($institutionName, $institutionNames);
        $this->assertNotContains('测试培训机构B', $institutionNames);
    }

    public function testFindInspectionsWithProblems(): void
    {
        $plan = $this->createSupervisionPlan();

        $problemInspection = $this->createSupervisionInspection($plan, '已完成');
        $problemInspection->setFoundProblems(['p1' => '问题1', 'p2' => '问题2']);

        $noProblemInspection = $this->createSupervisionInspection($plan, '已完成');
        $noProblemInspection->setFoundProblems([]);

        $this->repository->save($problemInspection, false);
        $this->repository->save($noProblemInspection, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findInspectionsWithProblems();
        $this->assertContainsOnlyInstancesOf(SupervisionInspection::class, $results);

        $foundInspections = array_filter($results, fn ($inspection) => [] !== $inspection->getFoundProblems());
        $this->assertNotEmpty($foundInspections);

        foreach ($foundInspections as $inspection) {
            $this->assertNotEmpty($inspection->getFoundProblems());
        }
    }

    public function testCountByType(): void
    {
        $plan = $this->createSupervisionPlan();

        $onSiteInspection = $this->createSupervisionInspection($plan, '已完成', null, '现场检查');
        $onlineInspection = $this->createSupervisionInspection($plan, '已完成', null, '在线检查');
        $specialInspection = $this->createSupervisionInspection($plan, '已完成', null, '专项检查');

        $this->repository->save($onSiteInspection, false);
        $this->repository->save($onlineInspection, false);
        $this->repository->save($specialInspection, false);
        self::getEntityManager()->flush();

        $results = $this->repository->countByType();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);

        // 验证返回的数据结构
        foreach ($results as $result) {
            $this->assertArrayHasKey('inspectionType', $result);
            $this->assertArrayHasKey('count', $result);
            $this->assertIsString($result['inspectionType']);
            $this->assertIsInt($result['count']);
        }

        $types = array_column($results, 'inspectionType');
        $this->assertContains('现场检查', $types);
        $this->assertContains('在线检查', $types);
        $this->assertContains('专项检查', $types);
    }

    public function testFindByDateRange(): void
    {
        $plan = $this->createSupervisionPlan();
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $inspection1 = $this->createSupervisionInspection($plan, '已完成');
        $inspection1->setInspectionDate(new \DateTimeImmutable('2024-01-15'));

        $inspection2 = $this->createSupervisionInspection($plan, '已完成');
        $inspection2->setInspectionDate(new \DateTimeImmutable('2024-02-15'));

        $this->repository->save($inspection1, false);
        $this->repository->save($inspection2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByDateRange($startDate, $endDate);
        $this->assertContainsOnlyInstancesOf(SupervisionInspection::class, $results);

        $dates = array_map(fn ($inspection) => $inspection->getInspectionDate(), $results);
        $this->assertContains($inspection1->getInspectionDate(), $dates);
        $this->assertNotContains($inspection2->getInspectionDate(), $dates);
    }

    public function testRemoveSupervisionInspection(): void
    {
        $plan = $this->createSupervisionPlan();
        $inspection = $this->createSupervisionInspection($plan, '已完成');
        $this->repository->save($inspection);
        $inspectionId = $inspection->getId();

        $this->repository->remove($inspection);

        $found = $this->repository->find($inspectionId);
        $this->assertNull($found);
    }

    protected function createNewEntity(): SupervisionInspection
    {
        $plan = $this->createSupervisionPlan();

        return $this->createSupervisionInspection($plan);
    }

    protected function getRepository(): SupervisionInspectionRepository
    {
        return $this->repository;
    }

    private function createSupervisionPlan(): SupervisionPlan
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('active');
        $plan->setSupervisor('测试监督员');

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        return $plan;
    }

    private function createSupervisionInspection(
        SupervisionPlan $plan,
        string $status = '已完成',
        ?int $supplierId = null,
        string $type = '现场检查',
    ): SupervisionInspection {
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试培训机构');
        $inspection->setInspectionType($type);
        $inspection->setInspectionDate(new \DateTimeImmutable());
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus($status);
        $inspection->setOverallScore(85.0);
        $inspection->setSupplierId($supplierId ?? random_int(1000, 9999));

        return $inspection;
    }
}

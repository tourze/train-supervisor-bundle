<?php

namespace Tourze\TrainSupervisorBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Repository\SupervisionPlanRepository;

/**
 * @internal
 */
#[CoversClass(SupervisionPlanRepository::class)]
#[RunTestsInSeparateProcesses]
final class SupervisionPlanRepositoryTest extends AbstractRepositoryTestCase
{
    private SupervisionPlanRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SupervisionPlanRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(SupervisionPlanRepository::class, $this->repository);
    }

    public function testSaveAndFindSupervisionPlan(): void
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('待执行');
        $plan->setSupervisor('测试监督员');

        $this->repository->save($plan);
        $this->assertNotNull($plan->getId());

        $found = $this->repository->find($plan->getId());
        $this->assertSame($plan, $found);
        $this->assertEquals('测试监督计划', $found->getPlanName());
        $this->assertEquals('定期', $found->getPlanType());
    }

    public function testFindActivePlans(): void
    {
        $activePlan = $this->createSupervisionPlan('执行中');
        $inactivePlan = $this->createSupervisionPlan('已完成');

        $this->repository->save($activePlan, false);
        $this->repository->save($inactivePlan, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findActivePlans();
        $this->assertContainsOnlyInstancesOf(SupervisionPlan::class, $results);

        $statuses = array_map(fn ($plan) => $plan->getPlanStatus(), $results);
        $this->assertContains('执行中', $statuses);
        $this->assertNotContains('已完成', $statuses);
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-12-31');

        $plan1 = $this->createSupervisionPlan('执行中');
        $plan1->setPlanStartDate(new \DateTimeImmutable('2024-06-01'));
        $plan1->setPlanEndDate(new \DateTimeImmutable('2024-09-30'));

        $plan2 = $this->createSupervisionPlan('执行中');
        $plan2->setPlanStartDate(new \DateTimeImmutable('2025-01-01'));
        $plan2->setPlanEndDate(new \DateTimeImmutable('2025-12-31'));

        $this->repository->save($plan1, false);
        $this->repository->save($plan2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByDateRange($startDate, $endDate);
        $this->assertContainsOnlyInstancesOf(SupervisionPlan::class, $results);

        $startDates = array_map(fn ($plan) => $plan->getPlanStartDate(), $results);
        $this->assertContains($plan1->getPlanStartDate(), $startDates);
        $this->assertNotContains($plan2->getPlanStartDate(), $startDates);
    }

    public function testFindExpiredPlans(): void
    {
        $expiredPlan = $this->createSupervisionPlan('执行中');
        $expiredPlan->setPlanEndDate(new \DateTimeImmutable('2023-12-31'));

        $currentPlan = $this->createSupervisionPlan('执行中');
        $currentPlan->setPlanEndDate(new \DateTimeImmutable('2025-12-31'));

        $this->repository->save($expiredPlan, false);
        $this->repository->save($currentPlan, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findExpiredPlans();
        $this->assertContainsOnlyInstancesOf(SupervisionPlan::class, $results);

        $endDates = array_map(fn ($plan) => $plan->getPlanEndDate(), $results);
        $this->assertContains($expiredPlan->getPlanEndDate(), $endDates);
        $this->assertNotContains($currentPlan->getPlanEndDate(), $endDates);
    }

    public function testCountByType(): void
    {
        $regularPlan = $this->createSupervisionPlan('执行中', '定期');
        $specialPlan = $this->createSupervisionPlan('执行中', '专项');
        $randomPlan = $this->createSupervisionPlan('执行中', '随机');

        $this->repository->save($regularPlan, false);
        $this->repository->save($specialPlan, false);
        $this->repository->save($randomPlan, false);
        self::getEntityManager()->flush();

        $results = $this->repository->countByType();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);

        // 验证返回的数据结构
        foreach ($results as $result) {
            $this->assertArrayHasKey('planType', $result);
            $this->assertArrayHasKey('count', $result);
            $this->assertIsString($result['planType']);
            $this->assertIsInt($result['count']);
        }

        $types = array_column($results, 'planType');
        $this->assertContains('定期', $types);
        $this->assertContains('专项', $types);
        $this->assertContains('随机', $types);
    }

    public function testFindPlansToExecuteOnDate(): void
    {
        $targetDate = new \DateTimeImmutable('2024-06-15');

        $activePlan = $this->createSupervisionPlan('执行中');
        $activePlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $activePlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));

        $futurePlan = $this->createSupervisionPlan('执行中');
        $futurePlan->setPlanStartDate(new \DateTimeImmutable('2024-07-01'));
        $futurePlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));

        $this->repository->save($activePlan, false);
        $this->repository->save($futurePlan, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findPlansToExecuteOnDate($targetDate);
        $this->assertContainsOnlyInstancesOf(SupervisionPlan::class, $results);

        $planIds = array_map(fn ($plan) => $plan->getId(), $results);
        $this->assertContains($activePlan->getId(), $planIds);
        $this->assertNotContains($futurePlan->getId(), $planIds);
    }

    public function testFindByType(): void
    {
        $regularPlan = $this->createSupervisionPlan('执行中', '定期');
        $specialPlan = $this->createSupervisionPlan('执行中', '专项');

        $this->repository->save($regularPlan, false);
        $this->repository->save($specialPlan, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByType('定期');
        $this->assertContainsOnlyInstancesOf(SupervisionPlan::class, $results);

        $types = array_map(fn ($plan) => $plan->getPlanType(), $results);
        $this->assertContains('定期', $types);
        $this->assertNotContains('专项', $types);
    }

    public function testFindCompletedPlans(): void
    {
        $completedPlan = $this->createSupervisionPlan('已完成');
        $activePlan = $this->createSupervisionPlan('执行中');

        $this->repository->save($completedPlan, false);
        $this->repository->save($activePlan, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findCompletedPlans();
        $this->assertContainsOnlyInstancesOf(SupervisionPlan::class, $results);

        $statuses = array_map(fn ($plan) => $plan->getPlanStatus(), $results);
        $this->assertContains('已完成', $statuses);
        $this->assertNotContains('执行中', $statuses);
    }

    public function testFindIncompletePlans(): void
    {
        $incompletePlan = $this->createSupervisionPlan('执行中');
        $completedPlan = $this->createSupervisionPlan('已完成');

        $this->repository->save($incompletePlan, false);
        $this->repository->save($completedPlan, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findIncompletePlans();
        $this->assertContainsOnlyInstancesOf(SupervisionPlan::class, $results);

        $statuses = array_map(fn ($plan) => $plan->getPlanStatus(), $results);
        $this->assertContains('执行中', $statuses);
        $this->assertNotContains('已完成', $statuses);
    }

    public function testRemoveSupervisionPlan(): void
    {
        $plan = $this->createSupervisionPlan('待执行');
        $this->repository->save($plan);
        $planId = $plan->getId();

        $this->repository->remove($plan);

        $found = $this->repository->find($planId);
        $this->assertNull($found);
    }

    protected function createNewEntity(): SupervisionPlan
    {
        return $this->createSupervisionPlan();
    }

    protected function getRepository(): SupervisionPlanRepository
    {
        return $this->repository;
    }

    private function createSupervisionPlan(
        string $status = '待执行',
        string $type = '定期',
    ): SupervisionPlan {
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType($type);
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus($status);
        $plan->setSupervisor('测试监督员');

        return $plan;
    }
}

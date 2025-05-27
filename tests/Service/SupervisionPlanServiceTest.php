<?php

declare(strict_types=1);

namespace Aqacms\TrainSupervisorBundle\Tests\Service;

use Aqacms\TrainSupervisorBundle\Entity\SupervisionPlan;
use Aqacms\TrainSupervisorBundle\Repository\SupervisionPlanRepository;
use Aqacms\TrainSupervisorBundle\Service\SupervisionPlanService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * 监督计划服务测试
 */
class SupervisionPlanServiceTest extends TestCase
{
    private SupervisionPlanService $service;
    private MockObject $entityManager;
    private MockObject $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(SupervisionPlanRepository::class);
        
        $this->entityManager
            ->method('getRepository')
            ->with(SupervisionPlan::class)
            ->willReturn($this->repository);

        $this->service = new SupervisionPlanService($this->entityManager);
    }

    public function testCreatePlan(): void
    {
        // 准备测试数据
        $planData = [
            'title' => '2024年度培训监督计划',
            'description' => '年度培训监督计划',
            'type' => 'annual',
            'priority' => 'high',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'target_institutions' => 100,
            'objectives' => ['提高培训质量', '规范培训流程'],
            'scope' => ['机构A', '机构B'],
            'methods' => ['现场检查', '在线监控'],
            'resources' => ['人员' => 10, '预算' => 100000],
            'criteria' => ['质量' => '>=80分']
        ];

        // 模拟实体管理器行为
        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行测试
        $plan = $this->service->createPlan($planData);

        // 验证结果
        $this->assertInstanceOf(SupervisionPlan::class, $plan);
        $this->assertEquals('2024年度培训监督计划', $plan->getTitle());
        $this->assertEquals('annual', $plan->getType());
        $this->assertEquals('high', $plan->getPriority());
        $this->assertEquals(100, $plan->getTargetInstitutions());
        $this->assertEquals('draft', $plan->getStatus()); // 默认状态
    }

    public function testUpdatePlan(): void
    {
        // 准备现有计划
        $plan = new SupervisionPlan();
        $plan->setTitle('原标题');
        $plan->setDescription('原描述');

        // 更新数据
        $updateData = [
            'title' => '更新后的标题',
            'description' => '更新后的描述',
            'priority' => 'medium'
        ];

        // 模拟实体管理器行为
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行测试
        $updatedPlan = $this->service->updatePlan($plan, $updateData);

        // 验证结果
        $this->assertEquals('更新后的标题', $updatedPlan->getTitle());
        $this->assertEquals('更新后的描述', $updatedPlan->getDescription());
        $this->assertEquals('medium', $updatedPlan->getPriority());
    }

    public function testActivatePlan(): void
    {
        // 准备草稿状态的计划
        $plan = new SupervisionPlan();
        $plan->setStatus('draft');
        $plan->setTitle('测试计划');

        // 模拟实体管理器行为
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行测试
        $activatedPlan = $this->service->activatePlan($plan);

        // 验证结果
        $this->assertEquals('active', $activatedPlan->getStatus());
    }

    public function testCompletePlan(): void
    {
        // 准备活跃状态的计划
        $plan = new SupervisionPlan();
        $plan->setStatus('active');
        $plan->setTargetInstitutions(100);
        $plan->setCompletedInstitutions(100);

        // 模拟实体管理器行为
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行测试
        $completedPlan = $this->service->completePlan($plan);

        // 验证结果
        $this->assertEquals('completed', $completedPlan->getStatus());
        $this->assertEquals(100.0, $completedPlan->getProgress());
    }

    public function testCancelPlan(): void
    {
        // 准备活跃状态的计划
        $plan = new SupervisionPlan();
        $plan->setStatus('active');

        // 模拟实体管理器行为
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行测试
        $cancelledPlan = $this->service->cancelPlan($plan, '计划变更');

        // 验证结果
        $this->assertEquals('cancelled', $cancelledPlan->getStatus());
        $this->assertEquals('计划变更', $cancelledPlan->getRemarks());
    }

    public function testUpdateProgress(): void
    {
        // 准备计划
        $plan = new SupervisionPlan();
        $plan->setTargetInstitutions(100);
        $plan->setCompletedInstitutions(0);

        // 模拟实体管理器行为
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行测试
        $updatedPlan = $this->service->updateProgress($plan, 25);

        // 验证结果
        $this->assertEquals(25, $updatedPlan->getCompletedInstitutions());
        $this->assertEquals(25.0, $updatedPlan->getProgress());
    }

    public function testGetActivePlans(): void
    {
        // 准备模拟数据
        $activePlans = [
            new SupervisionPlan(),
            new SupervisionPlan()
        ];

        // 模拟仓库行为
        $this->repository
            ->expects($this->once())
            ->method('findBy')
            ->with(['status' => 'active'])
            ->willReturn($activePlans);

        // 执行测试
        $result = $this->service->getActivePlans();

        // 验证结果
        $this->assertCount(2, $result);
        $this->assertEquals($activePlans, $result);
    }

    public function testGetPlansByType(): void
    {
        // 准备模拟数据
        $annualPlans = [new SupervisionPlan()];

        // 模拟仓库行为
        $this->repository
            ->expects($this->once())
            ->method('findBy')
            ->with(['type' => 'annual'])
            ->willReturn($annualPlans);

        // 执行测试
        $result = $this->service->getPlansByType('annual');

        // 验证结果
        $this->assertCount(1, $result);
        $this->assertEquals($annualPlans, $result);
    }

    public function testGetPlansByDateRange(): void
    {
        // 准备日期范围
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        $plans = [new SupervisionPlan()];

        // 模拟仓库行为
        $this->repository
            ->expects($this->once())
            ->method('findByDateRange')
            ->with($startDate, $endDate)
            ->willReturn($plans);

        // 执行测试
        $result = $this->service->getPlansByDateRange($startDate, $endDate);

        // 验证结果
        $this->assertCount(1, $result);
        $this->assertEquals($plans, $result);
    }

    public function testGetOverduePlans(): void
    {
        // 准备模拟数据
        $overduePlans = [new SupervisionPlan()];

        // 模拟仓库行为
        $this->repository
            ->expects($this->once())
            ->method('findOverduePlans')
            ->willReturn($overduePlans);

        // 执行测试
        $result = $this->service->getOverduePlans();

        // 验证结果
        $this->assertCount(1, $result);
        $this->assertEquals($overduePlans, $result);
    }

    public function testCalculateStatistics(): void
    {
        // 准备模拟数据
        $mockStats = [
            'total' => 10,
            'active' => 5,
            'completed' => 3,
            'cancelled' => 2,
            'overdue' => 1
        ];

        // 模拟仓库行为
        $this->repository
            ->expects($this->once())
            ->method('getStatistics')
            ->willReturn($mockStats);

        // 执行测试
        $result = $this->service->calculateStatistics();

        // 验证结果
        $this->assertEquals($mockStats, $result);
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(5, $result['active']);
    }

    public function testDeletePlan(): void
    {
        // 准备计划
        $plan = new SupervisionPlan();
        $plan->setStatus('draft');

        // 模拟实体管理器行为
        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($plan);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行测试
        $result = $this->service->deletePlan($plan);

        // 验证结果
        $this->assertTrue($result);
    }

    public function testDeletePlanWithActiveStatus(): void
    {
        // 准备活跃状态的计划
        $plan = new SupervisionPlan();
        $plan->setStatus('active');

        // 执行测试并期望异常
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('无法删除活跃状态的监督计划');

        $this->service->deletePlan($plan);
    }

    public function testValidatePlanData(): void
    {
        // 测试有效数据
        $validData = [
            'title' => '测试计划',
            'type' => 'annual',
            'priority' => 'high',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31'
        ];

        // 这个方法应该不抛出异常
        $this->service->validatePlanData($validData);
        $this->assertTrue(true); // 如果没有异常，测试通过
    }

    public function testValidatePlanDataWithInvalidData(): void
    {
        // 测试无效数据（缺少必填字段）
        $invalidData = [
            'description' => '只有描述'
        ];

        // 期望异常
        $this->expectException(\InvalidArgumentException::class);
        $this->service->validatePlanData($invalidData);
    }
} 
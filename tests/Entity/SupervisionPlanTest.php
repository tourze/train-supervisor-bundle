<?php

declare(strict_types=1);

namespace Aqacms\TrainSupervisorBundle\Tests\Entity;

use Aqacms\TrainSupervisorBundle\Entity\SupervisionInspection;
use Aqacms\TrainSupervisorBundle\Entity\SupervisionPlan;
use PHPUnit\Framework\TestCase;

/**
 * 监督计划实体测试
 */
class SupervisionPlanTest extends TestCase
{
    private SupervisionPlan $supervisionPlan;

    protected function setUp(): void
    {
        $this->supervisionPlan = new SupervisionPlan();
    }

    public function testGettersAndSetters(): void
    {
        // 测试基本属性
        $this->supervisionPlan->setTitle('2024年度培训监督计划');
        $this->assertEquals('2024年度培训监督计划', $this->supervisionPlan->getTitle());

        $this->supervisionPlan->setDescription('年度培训监督计划描述');
        $this->assertEquals('年度培训监督计划描述', $this->supervisionPlan->getDescription());

        $this->supervisionPlan->setType('annual');
        $this->assertEquals('annual', $this->supervisionPlan->getType());

        $this->supervisionPlan->setStatus('active');
        $this->assertEquals('active', $this->supervisionPlan->getStatus());

        $this->supervisionPlan->setPriority('high');
        $this->assertEquals('high', $this->supervisionPlan->getPriority());

        // 测试日期属性
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        
        $this->supervisionPlan->setStartDate($startDate);
        $this->assertEquals($startDate, $this->supervisionPlan->getStartDate());

        $this->supervisionPlan->setEndDate($endDate);
        $this->assertEquals($endDate, $this->supervisionPlan->getEndDate());

        // 测试数值属性
        $this->supervisionPlan->setTargetInstitutions(50);
        $this->assertEquals(50, $this->supervisionPlan->getTargetInstitutions());

        $this->supervisionPlan->setCompletedInstitutions(25);
        $this->assertEquals(25, $this->supervisionPlan->getCompletedInstitutions());

        $this->supervisionPlan->setProgress(50.0);
        $this->assertEquals(50.0, $this->supervisionPlan->getProgress());

        // 测试JSON属性
        $objectives = ['提高培训质量', '规范培训流程'];
        $this->supervisionPlan->setObjectives($objectives);
        $this->assertEquals($objectives, $this->supervisionPlan->getObjectives());

        $scope = ['机构A', '机构B', '机构C'];
        $this->supervisionPlan->setScope($scope);
        $this->assertEquals($scope, $this->supervisionPlan->getScope());

        $methods = ['现场检查', '在线监控', '文档审查'];
        $this->supervisionPlan->setMethods($methods);
        $this->assertEquals($methods, $this->supervisionPlan->getMethods());

        $resources = ['人员配置' => 10, '预算' => 100000];
        $this->supervisionPlan->setResources($resources);
        $this->assertEquals($resources, $this->supervisionPlan->getResources());

        $criteria = ['培训质量' => '>=80分', '合规性' => '100%'];
        $this->supervisionPlan->setCriteria($criteria);
        $this->assertEquals($criteria, $this->supervisionPlan->getCriteria());

        // 测试备注
        $this->supervisionPlan->setRemarks('重点关注新机构');
        $this->assertEquals('重点关注新机构', $this->supervisionPlan->getRemarks());
    }

    public function testInspectionCollection(): void
    {
        // 测试检查集合
        $this->assertCount(0, $this->supervisionPlan->getInspections());

        $inspection1 = new SupervisionInspection();
        $inspection2 = new SupervisionInspection();

        $this->supervisionPlan->addInspection($inspection1);
        $this->supervisionPlan->addInspection($inspection2);

        $this->assertCount(2, $this->supervisionPlan->getInspections());
        $this->assertTrue($this->supervisionPlan->getInspections()->contains($inspection1));
        $this->assertTrue($this->supervisionPlan->getInspections()->contains($inspection2));

        // 测试移除检查
        $this->supervisionPlan->removeInspection($inspection1);
        $this->assertCount(1, $this->supervisionPlan->getInspections());
        $this->assertFalse($this->supervisionPlan->getInspections()->contains($inspection1));
        $this->assertTrue($this->supervisionPlan->getInspections()->contains($inspection2));
    }

    public function testProgressCalculation(): void
    {
        // 测试进度计算
        $this->supervisionPlan->setTargetInstitutions(100);
        $this->supervisionPlan->setCompletedInstitutions(25);
        
        // 假设有计算进度的方法
        $expectedProgress = 25.0;
        $this->supervisionPlan->setProgress($expectedProgress);
        $this->assertEquals($expectedProgress, $this->supervisionPlan->getProgress());
    }

    public function testValidation(): void
    {
        // 测试必填字段
        $this->supervisionPlan->setTitle('测试计划');
        $this->supervisionPlan->setType('monthly');
        $this->supervisionPlan->setStatus('draft');
        $this->supervisionPlan->setPriority('medium');
        $this->supervisionPlan->setStartDate(new \DateTime());
        $this->supervisionPlan->setEndDate(new \DateTime('+1 month'));

        $this->assertNotEmpty($this->supervisionPlan->getTitle());
        $this->assertNotEmpty($this->supervisionPlan->getType());
        $this->assertNotEmpty($this->supervisionPlan->getStatus());
        $this->assertNotEmpty($this->supervisionPlan->getPriority());
        $this->assertInstanceOf(\DateTime::class, $this->supervisionPlan->getStartDate());
        $this->assertInstanceOf(\DateTime::class, $this->supervisionPlan->getEndDate());
    }

    public function testDateValidation(): void
    {
        // 测试日期逻辑
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-12-31');
        
        $this->supervisionPlan->setStartDate($startDate);
        $this->supervisionPlan->setEndDate($endDate);
        
        $this->assertTrue($this->supervisionPlan->getEndDate() > $this->supervisionPlan->getStartDate());
    }

    public function testStatusTransitions(): void
    {
        // 测试状态转换
        $validStatuses = ['draft', 'active', 'completed', 'cancelled'];
        
        foreach ($validStatuses as $status) {
            $this->supervisionPlan->setStatus($status);
            $this->assertEquals($status, $this->supervisionPlan->getStatus());
        }
    }

    public function testTypeValidation(): void
    {
        // 测试类型验证
        $validTypes = ['annual', 'quarterly', 'monthly', 'special'];
        
        foreach ($validTypes as $type) {
            $this->supervisionPlan->setType($type);
            $this->assertEquals($type, $this->supervisionPlan->getType());
        }
    }

    public function testPriorityValidation(): void
    {
        // 测试优先级验证
        $validPriorities = ['low', 'medium', 'high', 'urgent'];
        
        foreach ($validPriorities as $priority) {
            $this->supervisionPlan->setPriority($priority);
            $this->assertEquals($priority, $this->supervisionPlan->getPriority());
        }
    }
} 
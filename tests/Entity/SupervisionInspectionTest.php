<?php

declare(strict_types=1);

namespace Aqacms\TrainSupervisorBundle\Tests\Entity;

use Aqacms\TrainSupervisorBundle\Entity\ProblemTracking;
use Aqacms\TrainSupervisorBundle\Entity\QualityAssessment;
use Aqacms\TrainSupervisorBundle\Entity\SupervisionInspection;
use Aqacms\TrainSupervisorBundle\Entity\SupervisionPlan;
use PHPUnit\Framework\TestCase;

/**
 * 监督检查实体测试
 */
class SupervisionInspectionTest extends TestCase
{
    private SupervisionInspection $inspection;

    protected function setUp(): void
    {
        $this->inspection = new SupervisionInspection();
    }

    public function testGettersAndSetters(): void
    {
        // 测试基本属性
        $this->inspection->setTitle('机构A现场检查');
        $this->assertEquals('机构A现场检查', $this->inspection->getTitle());

        $this->inspection->setDescription('对机构A进行现场检查');
        $this->assertEquals('对机构A进行现场检查', $this->inspection->getDescription());

        $this->inspection->setType('onsite');
        $this->assertEquals('onsite', $this->inspection->getType());

        $this->inspection->setStatus('completed');
        $this->assertEquals('completed', $this->inspection->getStatus());

        $this->inspection->setInstitutionId(123);
        $this->assertEquals(123, $this->inspection->getInstitutionId());

        $this->inspection->setInstitutionName('培训机构A');
        $this->assertEquals('培训机构A', $this->inspection->getInstitutionName());

        // 测试日期属性
        $scheduledDate = new \DateTime('2024-06-01');
        $actualDate = new \DateTime('2024-06-01');
        
        $this->inspection->setScheduledDate($scheduledDate);
        $this->assertEquals($scheduledDate, $this->inspection->getScheduledDate());

        $this->inspection->setActualDate($actualDate);
        $this->assertEquals($actualDate, $this->inspection->getActualDate());

        // 测试检查人员
        $inspectors = ['张三', '李四'];
        $this->inspection->setInspectors($inspectors);
        $this->assertEquals($inspectors, $this->inspection->getInspectors());

        // 测试检查项目
        $checkItems = ['师资力量', '教学设施', '课程设置'];
        $this->inspection->setCheckItems($checkItems);
        $this->assertEquals($checkItems, $this->inspection->getCheckItems());

        // 测试检查结果
        $results = ['师资力量' => '良好', '教学设施' => '优秀'];
        $this->inspection->setResults($results);
        $this->assertEquals($results, $this->inspection->getResults());

        // 测试发现问题
        $issues = ['缺少消防设施', '教师资质不全'];
        $this->inspection->setIssues($issues);
        $this->assertEquals($issues, $this->inspection->getIssues());

        // 测试建议
        $recommendations = ['完善消防设施', '补充教师资质'];
        $this->inspection->setRecommendations($recommendations);
        $this->assertEquals($recommendations, $this->inspection->getRecommendations());

        // 测试评分
        $this->inspection->setScore(85.5);
        $this->assertEquals(85.5, $this->inspection->getScore());

        $this->inspection->setGrade('B');
        $this->assertEquals('B', $this->inspection->getGrade());

        // 测试备注
        $this->inspection->setRemarks('需要跟进整改');
        $this->assertEquals('需要跟进整改', $this->inspection->getRemarks());
    }

    public function testPlanRelation(): void
    {
        // 测试监督计划关联
        $plan = new SupervisionPlan();
        $plan->setTitle('2024年度监督计划');

        $this->inspection->setPlan($plan);
        $this->assertEquals($plan, $this->inspection->getPlan());
    }

    public function testAssessmentCollection(): void
    {
        // 测试质量评估集合
        $this->assertCount(0, $this->inspection->getAssessments());

        $assessment1 = new QualityAssessment();
        $assessment2 = new QualityAssessment();

        $this->inspection->addAssessment($assessment1);
        $this->inspection->addAssessment($assessment2);

        $this->assertCount(2, $this->inspection->getAssessments());
        $this->assertTrue($this->inspection->getAssessments()->contains($assessment1));
        $this->assertTrue($this->inspection->getAssessments()->contains($assessment2));

        // 测试移除评估
        $this->inspection->removeAssessment($assessment1);
        $this->assertCount(1, $this->inspection->getAssessments());
        $this->assertFalse($this->inspection->getAssessments()->contains($assessment1));
        $this->assertTrue($this->inspection->getAssessments()->contains($assessment2));
    }

    public function testProblemTrackingCollection(): void
    {
        // 测试问题跟踪集合
        $this->assertCount(0, $this->inspection->getProblemTrackings());

        $problem1 = new ProblemTracking();
        $problem2 = new ProblemTracking();

        $this->inspection->addProblemTracking($problem1);
        $this->inspection->addProblemTracking($problem2);

        $this->assertCount(2, $this->inspection->getProblemTrackings());
        $this->assertTrue($this->inspection->getProblemTrackings()->contains($problem1));
        $this->assertTrue($this->inspection->getProblemTrackings()->contains($problem2));

        // 测试移除问题跟踪
        $this->inspection->removeProblemTracking($problem1);
        $this->assertCount(1, $this->inspection->getProblemTrackings());
        $this->assertFalse($this->inspection->getProblemTrackings()->contains($problem1));
        $this->assertTrue($this->inspection->getProblemTrackings()->contains($problem2));
    }

    public function testStatusValidation(): void
    {
        // 测试状态验证
        $validStatuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
        
        foreach ($validStatuses as $status) {
            $this->inspection->setStatus($status);
            $this->assertEquals($status, $this->inspection->getStatus());
        }
    }

    public function testTypeValidation(): void
    {
        // 测试类型验证
        $validTypes = ['onsite', 'online', 'document', 'follow_up'];
        
        foreach ($validTypes as $type) {
            $this->inspection->setType($type);
            $this->assertEquals($type, $this->inspection->getType());
        }
    }

    public function testGradeValidation(): void
    {
        // 测试等级验证
        $validGrades = ['A', 'B', 'C', 'D'];
        
        foreach ($validGrades as $grade) {
            $this->inspection->setGrade($grade);
            $this->assertEquals($grade, $this->inspection->getGrade());
        }
    }

    public function testScoreValidation(): void
    {
        // 测试评分范围
        $this->inspection->setScore(0.0);
        $this->assertEquals(0.0, $this->inspection->getScore());

        $this->inspection->setScore(100.0);
        $this->assertEquals(100.0, $this->inspection->getScore());

        $this->inspection->setScore(85.5);
        $this->assertEquals(85.5, $this->inspection->getScore());
    }

    public function testDateLogic(): void
    {
        // 测试日期逻辑
        $scheduledDate = new \DateTime('2024-06-01');
        $actualDate = new \DateTime('2024-06-02');
        
        $this->inspection->setScheduledDate($scheduledDate);
        $this->inspection->setActualDate($actualDate);
        
        // 实际日期可以晚于计划日期
        $this->assertTrue($this->inspection->getActualDate() >= $this->inspection->getScheduledDate());
    }

    public function testRequiredFields(): void
    {
        // 测试必填字段
        $this->inspection->setTitle('测试检查');
        $this->inspection->setType('onsite');
        $this->inspection->setStatus('scheduled');
        $this->inspection->setInstitutionId(1);
        $this->inspection->setInstitutionName('测试机构');
        $this->inspection->setScheduledDate(new \DateTime());

        $this->assertNotEmpty($this->inspection->getTitle());
        $this->assertNotEmpty($this->inspection->getType());
        $this->assertNotEmpty($this->inspection->getStatus());
        $this->assertNotNull($this->inspection->getInstitutionId());
        $this->assertNotEmpty($this->inspection->getInstitutionName());
        $this->assertInstanceOf(\DateTime::class, $this->inspection->getScheduledDate());
    }

    public function testInspectorManagement(): void
    {
        // 测试检查人员管理
        $inspectors = ['张三', '李四', '王五'];
        $this->inspection->setInspectors($inspectors);
        
        $this->assertCount(3, $this->inspection->getInspectors());
        $this->assertContains('张三', $this->inspection->getInspectors());
        $this->assertContains('李四', $this->inspection->getInspectors());
        $this->assertContains('王五', $this->inspection->getInspectors());
    }

    public function testIssueManagement(): void
    {
        // 测试问题管理
        $issues = [
            '消防设施不完善',
            '教师资质证书过期',
            '教学设备老化'
        ];
        
        $this->inspection->setIssues($issues);
        $this->assertCount(3, $this->inspection->getIssues());
        
        $recommendations = [
            '立即整改消防设施',
            '更新教师资质证书',
            '更换教学设备'
        ];
        
        $this->inspection->setRecommendations($recommendations);
        $this->assertCount(3, $this->inspection->getRecommendations());
    }
} 
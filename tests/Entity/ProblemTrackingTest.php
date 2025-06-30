<?php

namespace Tourze\TrainSupervisorBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;

class ProblemTrackingTest extends TestCase
{
    private ProblemTracking $problemTracking;

    protected function setUp(): void
    {
        $this->problemTracking = new ProblemTracking();
    }

    public function testSetAndGetInspection(): void
    {
        $plan = new SupervisionPlan();
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        
        $result = $this->problemTracking->setInspection($inspection);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($inspection, $this->problemTracking->getInspection());
    }

    public function testSetAndGetSupervisionInspectionAlias(): void
    {
        $plan = new SupervisionPlan();
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        
        $result = $this->problemTracking->setSupervisionInspection($inspection);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($inspection, $this->problemTracking->getSupervisionInspection());
    }

    public function testSetAndGetProblemTitle(): void
    {
        $title = '培训质量问题';
        
        $result = $this->problemTracking->setProblemTitle($title);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($title, $this->problemTracking->getProblemTitle());
    }

    public function testSetAndGetProblemType(): void
    {
        $type = '教学质量';
        
        $result = $this->problemTracking->setProblemType($type);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($type, $this->problemTracking->getProblemType());
    }

    public function testSetAndGetProblemDescription(): void
    {
        $description = '课程内容安排不合理，缺乏实践环节';
        
        $result = $this->problemTracking->setProblemDescription($description);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($description, $this->problemTracking->getProblemDescription());
    }

    public function testSetAndGetProblemSeverity(): void
    {
        $severity = '严重';
        
        $result = $this->problemTracking->setProblemSeverity($severity);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($severity, $this->problemTracking->getProblemSeverity());
    }

    public function testSetAndGetProblemStatus(): void
    {
        $status = '处理中';
        
        $result = $this->problemTracking->setProblemStatus($status);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($status, $this->problemTracking->getProblemStatus());
    }

    public function testSetAndGetDiscoveryDate(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');
        
        $result = $this->problemTracking->setDiscoveryDate($date);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($date, $this->problemTracking->getDiscoveryDate());
    }

    public function testSetAndGetExpectedResolutionDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-01');
        
        $result = $this->problemTracking->setExpectedResolutionDate($date);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($date, $this->problemTracking->getExpectedResolutionDate());
    }

    public function testSetAndGetActualResolutionDate(): void
    {
        $date = new \DateTimeImmutable('2024-01-31');
        
        $result = $this->problemTracking->setActualResolutionDate($date);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($date, $this->problemTracking->getActualResolutionDate());
    }

    public function testSetAndGetRootCauseAnalysis(): void
    {
        $analysis = '师资配备不足，缺乏专业知识';
        
        $result = $this->problemTracking->setRootCauseAnalysis($analysis);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($analysis, $this->problemTracking->getRootCauseAnalysis());
    }

    public function testSetAndGetPreventiveMeasures(): void
    {
        $measures = '加强师资培训，建立质量监控体系';
        
        $result = $this->problemTracking->setPreventiveMeasures($measures);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($measures, $this->problemTracking->getPreventiveMeasures());
    }

    public function testSetAndGetCorrectionMeasures(): void
    {
        $measures = ['增加实践课程', '更新教学大纲'];
        
        $result = $this->problemTracking->setCorrectionMeasures($measures);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($measures, $this->problemTracking->getCorrectionMeasures());
    }

    public function testSetAndGetCorrectionDeadline(): void
    {
        $deadline = new \DateTimeImmutable('2024-03-01');
        
        $result = $this->problemTracking->setCorrectionDeadline($deadline);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($deadline, $this->problemTracking->getCorrectionDeadline());
    }

    public function testSetAndGetCorrectionStatus(): void
    {
        $status = '已整改';
        
        $result = $this->problemTracking->setCorrectionStatus($status);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($status, $this->problemTracking->getCorrectionStatus());
    }

    public function testSetAndGetCorrectionEvidence(): void
    {
        $evidence = ['课程表更新文件', '师资培训证书'];
        
        $result = $this->problemTracking->setCorrectionEvidence($evidence);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($evidence, $this->problemTracking->getCorrectionEvidence());
    }

    public function testSetAndGetCorrectionDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-15');
        
        $result = $this->problemTracking->setCorrectionDate($date);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($date, $this->problemTracking->getCorrectionDate());
    }

    public function testSetAndGetVerificationResult(): void
    {
        $result = '通过';
        
        $setResult = $this->problemTracking->setVerificationResult($result);
        
        $this->assertSame($this->problemTracking, $setResult);
        $this->assertSame($result, $this->problemTracking->getVerificationResult());
    }

    public function testSetAndGetVerificationDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-20');
        
        $result = $this->problemTracking->setVerificationDate($date);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($date, $this->problemTracking->getVerificationDate());
    }

    public function testSetAndGetVerifier(): void
    {
        $verifier = '李四';
        
        $result = $this->problemTracking->setVerifier($verifier);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($verifier, $this->problemTracking->getVerifier());
    }

    public function testSetAndGetResponsiblePerson(): void
    {
        $person = '张三';
        
        $result = $this->problemTracking->setResponsiblePerson($person);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($person, $this->problemTracking->getResponsiblePerson());
    }

    public function testSetAndGetRemarks(): void
    {
        $remarks = '需要重点关注';
        
        $result = $this->problemTracking->setRemarks($remarks);
        
        $this->assertSame($this->problemTracking, $result);
        $this->assertSame($remarks, $this->problemTracking->getRemarks());
    }

    public function testGetFoundDateAlias(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');
        $this->problemTracking->setDiscoveryDate($date);
        
        $this->assertSame($date, $this->problemTracking->getFoundDate());
    }

    public function testGetDeadlineAlias(): void
    {
        $deadline = new \DateTimeImmutable('2024-03-01');
        $this->problemTracking->setCorrectionDeadline($deadline);
        
        $this->assertSame($deadline, $this->problemTracking->getDeadline());
    }

    public function testIsCorrectedWhenStatusIsAlreadyCorrected(): void
    {
        $this->problemTracking->setCorrectionStatus('已整改');
        
        $this->assertTrue($this->problemTracking->isCorrected());
    }

    public function testIsCorrectedWhenStatusIsVerified(): void
    {
        $this->problemTracking->setCorrectionStatus('已验证');
        
        $this->assertTrue($this->problemTracking->isCorrected());
    }

    public function testIsCorrectedWhenStatusIsClosed(): void
    {
        $this->problemTracking->setCorrectionStatus('已关闭');
        
        $this->assertTrue($this->problemTracking->isCorrected());
    }

    public function testIsNotCorrectedWhenStatusIsPending(): void
    {
        $this->problemTracking->setCorrectionStatus('待整改');
        
        $this->assertFalse($this->problemTracking->isCorrected());
    }

    public function testIsVerifiedWhenStatusIsVerified(): void
    {
        $this->problemTracking->setCorrectionStatus('已验证');
        
        $this->assertTrue($this->problemTracking->isVerified());
    }

    public function testIsVerifiedWhenStatusIsClosed(): void
    {
        $this->problemTracking->setCorrectionStatus('已关闭');
        
        $this->assertTrue($this->problemTracking->isVerified());
    }

    public function testIsNotVerifiedWhenStatusIsCorrected(): void
    {
        $this->problemTracking->setCorrectionStatus('已整改');
        
        $this->assertFalse($this->problemTracking->isVerified());
    }

    public function testIsClosedWhenStatusIsClosed(): void
    {
        $this->problemTracking->setCorrectionStatus('已关闭');
        
        $this->assertTrue($this->problemTracking->isClosed());
    }

    public function testIsNotClosedWhenStatusIsVerified(): void
    {
        $this->problemTracking->setCorrectionStatus('已验证');
        
        $this->assertFalse($this->problemTracking->isClosed());
    }

    public function testIsNotOverdueWhenCorrected(): void
    {
        $this->problemTracking->setCorrectionStatus('已整改');
        $this->problemTracking->setCorrectionDeadline(new \DateTimeImmutable('-1 day'));
        
        $this->assertFalse($this->problemTracking->isOverdue());
    }

    public function testIsOverdueWhenPastDeadline(): void
    {
        $this->problemTracking->setCorrectionStatus('待整改');
        $this->problemTracking->setCorrectionDeadline(new \DateTimeImmutable('-1 day'));
        
        $this->assertTrue($this->problemTracking->isOverdue());
    }

    public function testIsNotOverdueWhenBeforeDeadline(): void
    {
        $this->problemTracking->setCorrectionStatus('待整改');
        $this->problemTracking->setCorrectionDeadline(new \DateTimeImmutable('+1 day'));
        
        $this->assertFalse($this->problemTracking->isOverdue());
    }

    public function testGetRemainingDaysWhenCorrected(): void
    {
        $this->problemTracking->setCorrectionStatus('已整改');
        $this->problemTracking->setCorrectionDeadline(new \DateTimeImmutable('+5 days'));
        
        $this->assertSame(0, $this->problemTracking->getRemainingDays());
    }

    public function testGetRemainingDaysWhenBeforeDeadline(): void
    {
        $this->problemTracking->setCorrectionStatus('待整改');
        $this->problemTracking->setCorrectionDeadline(new \DateTimeImmutable('+5 days'));
        
        $remainingDays = $this->problemTracking->getRemainingDays();
        $this->assertTrue($remainingDays >= 4 && $remainingDays <= 5); // 允许1天的误差
    }

    public function testGetRemainingDaysWhenOverdue(): void
    {
        $this->problemTracking->setCorrectionStatus('待整改');
        $this->problemTracking->setCorrectionDeadline(new \DateTimeImmutable('-3 days'));
        
        $this->assertSame(-3, $this->problemTracking->getRemainingDays());
    }

    public function testIsVerificationPassedWhenResultIsPassed(): void
    {
        $this->problemTracking->setVerificationResult('通过');
        
        $this->assertTrue($this->problemTracking->isVerificationPassed());
    }

    public function testIsVerificationNotPassedWhenResultIsFailed(): void
    {
        $this->problemTracking->setVerificationResult('不通过');
        
        $this->assertFalse($this->problemTracking->isVerificationPassed());
    }

    public function testIsVerificationNotPassedWhenResultIsNull(): void
    {
        $this->problemTracking->setVerificationResult(null);
        
        $this->assertFalse($this->problemTracking->isVerificationPassed());
    }

    public function testGetMeasureCount(): void
    {
        $measures = ['措施1', '措施2', '措施3'];
        $this->problemTracking->setCorrectionMeasures($measures);
        
        $this->assertSame(3, $this->problemTracking->getMeasureCount());
    }

    public function testGetMeasureCountWhenEmpty(): void
    {
        $this->problemTracking->setCorrectionMeasures([]);
        
        $this->assertSame(0, $this->problemTracking->getMeasureCount());
    }

    public function testHasEvidenceWhenEvidenceExists(): void
    {
        $evidence = ['证据1', '证据2'];
        $this->problemTracking->setCorrectionEvidence($evidence);
        
        $this->assertTrue($this->problemTracking->hasEvidence());
    }

    public function testHasNoEvidenceWhenEvidenceIsEmpty(): void
    {
        $this->problemTracking->setCorrectionEvidence([]);
        
        $this->assertFalse($this->problemTracking->hasEvidence());
    }

    public function testHasNoEvidenceWhenEvidenceIsNull(): void
    {
        $this->problemTracking->setCorrectionEvidence(null);
        
        $this->assertFalse($this->problemTracking->hasEvidence());
    }

    public function testToString(): void
    {
        $this->problemTracking->setProblemType('教学质量');
        $this->problemTracking->setProblemDescription('这是一个很长的问题描述，包含了详细的问题分析和具体情况说明');
        
        $result = (string) $this->problemTracking;
        
        $this->assertStringContainsString('教学质量', $result);
        $this->assertStringContainsString('这是一个很长的问题描述', $result); // 只检查前50个字符内的内容
    }

    public function testDefaultProblemStatus(): void
    {
        $this->assertSame('待处理', $this->problemTracking->getProblemStatus());
    }

    public function testDefaultCorrectionStatus(): void
    {
        $this->assertSame('待整改', $this->problemTracking->getCorrectionStatus());
    }

    public function testDefaultCorrectionMeasures(): void
    {
        $this->assertSame([], $this->problemTracking->getCorrectionMeasures());
    }
}
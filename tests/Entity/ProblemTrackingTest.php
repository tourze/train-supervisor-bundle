<?php

namespace Tourze\TrainSupervisorBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;

/**
 * @internal
 */
#[CoversClass(ProblemTracking::class)]
final class ProblemTrackingTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new ProblemTracking();
    }

    public static function propertiesProvider(): iterable
    {
        return [
            'problemTitle' => ['problemTitle', 'test_value'],
            'problemType' => ['problemType', 'test_value'],
            'problemDescription' => ['problemDescription', 'test_value'],
            'problemSeverity' => ['problemSeverity', 'test_value'],
            'problemStatus' => ['problemStatus', 'test_value'],
            'correctionMeasures' => ['correctionMeasures', ['key' => 'value']],
            'correctionStatus' => ['correctionStatus', 'test_value'],
            'responsiblePerson' => ['responsiblePerson', 'test_value'],
        ];
    }

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

        $this->problemTracking->setInspection($inspection);
        $this->assertSame($inspection, $this->problemTracking->getInspection());
    }

    public function testSetAndGetSupervisionInspectionAlias(): void
    {
        $plan = new SupervisionPlan();
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);

        $this->problemTracking->setSupervisionInspection($inspection);
        $this->assertSame($inspection, $this->problemTracking->getSupervisionInspection());
    }

    public function testSetAndGetProblemTitle(): void
    {
        $title = '培训质量问题';

        $this->problemTracking->setProblemTitle($title);
        $this->assertSame($title, $this->problemTracking->getProblemTitle());
    }

    public function testSetAndGetProblemType(): void
    {
        $type = '教学质量';

        $this->problemTracking->setProblemType($type);
        $this->assertSame($type, $this->problemTracking->getProblemType());
    }

    public function testSetAndGetProblemDescription(): void
    {
        $description = '课程内容安排不合理，缺乏实践环节';

        $this->problemTracking->setProblemDescription($description);
        $this->assertSame($description, $this->problemTracking->getProblemDescription());
    }

    public function testSetAndGetProblemSeverity(): void
    {
        $severity = '严重';

        $this->problemTracking->setProblemSeverity($severity);
        $this->assertSame($severity, $this->problemTracking->getProblemSeverity());
    }

    public function testSetAndGetProblemStatus(): void
    {
        $status = '处理中';

        $this->problemTracking->setProblemStatus($status);
        $this->assertSame($status, $this->problemTracking->getProblemStatus());
    }

    public function testSetAndGetDiscoveryDate(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');

        $this->problemTracking->setDiscoveryDate($date);
        $this->assertSame($date, $this->problemTracking->getDiscoveryDate());
    }

    public function testSetAndGetExpectedResolutionDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-01');

        $this->problemTracking->setExpectedResolutionDate($date);
        $this->assertSame($date, $this->problemTracking->getExpectedResolutionDate());
    }

    public function testSetAndGetActualResolutionDate(): void
    {
        $date = new \DateTimeImmutable('2024-01-31');

        $this->problemTracking->setActualResolutionDate($date);
        $this->assertSame($date, $this->problemTracking->getActualResolutionDate());
    }

    public function testSetAndGetRootCauseAnalysis(): void
    {
        $analysis = '师资配备不足，缺乏专业知识';

        $this->problemTracking->setRootCauseAnalysis($analysis);
        $this->assertSame($analysis, $this->problemTracking->getRootCauseAnalysis());
    }

    public function testSetAndGetPreventiveMeasures(): void
    {
        $measures = '加强师资培训，建立质量监控体系';

        $this->problemTracking->setPreventiveMeasures($measures);
        $this->assertSame($measures, $this->problemTracking->getPreventiveMeasures());
    }

    public function testSetAndGetCorrectionMeasures(): void
    {
        $measures = ['增加实践课程', '更新教学大纲'];

        $this->problemTracking->setCorrectionMeasures($measures);
        $this->assertSame($measures, $this->problemTracking->getCorrectionMeasures());
    }

    public function testSetAndGetCorrectionDeadline(): void
    {
        $deadline = new \DateTimeImmutable('2024-03-01');

        $this->problemTracking->setCorrectionDeadline($deadline);
        $this->assertSame($deadline, $this->problemTracking->getCorrectionDeadline());
    }

    public function testSetAndGetCorrectionStatus(): void
    {
        $status = '已整改';

        $this->problemTracking->setCorrectionStatus($status);
        $this->assertSame($status, $this->problemTracking->getCorrectionStatus());
    }

    public function testSetAndGetCorrectionEvidence(): void
    {
        $evidence = ['document1' => '课程表更新文件', 'certificate' => '师资培训证书'];

        $this->problemTracking->setCorrectionEvidence($evidence);
        $this->assertSame($evidence, $this->problemTracking->getCorrectionEvidence());
    }

    public function testSetAndGetCorrectionDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-15');

        $this->problemTracking->setCorrectionDate($date);
        $this->assertSame($date, $this->problemTracking->getCorrectionDate());
    }

    public function testSetAndGetVerificationResult(): void
    {
        $result = '通过';

        $this->problemTracking->setVerificationResult($result);
        $this->assertSame($result, $this->problemTracking->getVerificationResult());
    }

    public function testSetAndGetVerificationDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-20');

        $this->problemTracking->setVerificationDate($date);
        $this->assertSame($date, $this->problemTracking->getVerificationDate());
    }

    public function testSetAndGetVerifier(): void
    {
        $verifier = '李四';

        $this->problemTracking->setVerifier($verifier);
        $this->assertSame($verifier, $this->problemTracking->getVerifier());
    }

    public function testSetAndGetResponsiblePerson(): void
    {
        $person = '张三';

        $this->problemTracking->setResponsiblePerson($person);
        $this->assertSame($person, $this->problemTracking->getResponsiblePerson());
    }

    public function testSetAndGetRemarks(): void
    {
        $remarks = '需要重点关注';

        $this->problemTracking->setRemarks($remarks);
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
        $evidence = ['items' => ['证据1', '证据2']];
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

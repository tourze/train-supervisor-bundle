<?php

namespace Tourze\TrainSupervisorBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;

class QualityAssessmentTest extends TestCase
{
    private QualityAssessment $qualityAssessment;

    protected function setUp(): void
    {
        $this->qualityAssessment = new QualityAssessment();
    }

    public function testSetAndGetAssessmentType(): void
    {
        $type = '课程评估';
        
        $result = $this->qualityAssessment->setAssessmentType($type);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($type, $this->qualityAssessment->getAssessmentType());
    }

    public function testSetAndGetTargetId(): void
    {
        $targetId = 'TARGET_001';
        
        $result = $this->qualityAssessment->setTargetId($targetId);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($targetId, $this->qualityAssessment->getTargetId());
    }

    public function testSetAndGetTargetName(): void
    {
        $targetName = '安全生产培训课程';
        
        $result = $this->qualityAssessment->setTargetName($targetName);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($targetName, $this->qualityAssessment->getTargetName());
    }

    public function testSetAndGetAssessmentCriteria(): void
    {
        $criteria = 'ISO-9001标准';
        
        $result = $this->qualityAssessment->setAssessmentCriteria($criteria);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($criteria, $this->qualityAssessment->getAssessmentCriteria());
    }

    public function testSetAndGetAssessmentItems(): void
    {
        $items = ['教学内容', '教学方法', '师资水平'];
        
        $result = $this->qualityAssessment->setAssessmentItems($items);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($items, $this->qualityAssessment->getAssessmentItems());
    }

    public function testSetAndGetAssessmentScores(): void
    {
        $scores = ['教学内容' => 85, '教学方法' => 90, '师资水平' => 88];
        
        $result = $this->qualityAssessment->setAssessmentScores($scores);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($scores, $this->qualityAssessment->getAssessmentScores());
    }

    public function testSetAndGetTotalScore(): void
    {
        $score = 87.5;
        
        $result = $this->qualityAssessment->setTotalScore($score);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($score, $this->qualityAssessment->getTotalScore());
    }

    public function testSetAndGetAssessmentLevel(): void
    {
        $level = '良好';
        
        $result = $this->qualityAssessment->setAssessmentLevel($level);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($level, $this->qualityAssessment->getAssessmentLevel());
    }

    public function testSetAndGetAssessmentComments(): void
    {
        $comments = ['课程设计合理', '需要加强实践环节'];
        
        $result = $this->qualityAssessment->setAssessmentComments($comments);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($comments, $this->qualityAssessment->getAssessmentComments());
    }

    public function testSetAndGetAssessor(): void
    {
        $assessor = '王五';
        
        $result = $this->qualityAssessment->setAssessor($assessor);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($assessor, $this->qualityAssessment->getAssessor());
    }

    public function testSetAndGetAssessmentDate(): void
    {
        $date = new \DateTimeImmutable('2024-01-15');
        
        $result = $this->qualityAssessment->setAssessmentDate($date);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($date, $this->qualityAssessment->getAssessmentDate());
    }

    public function testSetAndGetAssessmentStatus(): void
    {
        $status = '已完成';
        
        $result = $this->qualityAssessment->setAssessmentStatus($status);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($status, $this->qualityAssessment->getAssessmentStatus());
    }

    public function testSetAndGetRemarks(): void
    {
        $remarks = '整体表现良好，有待进一步提升';
        
        $result = $this->qualityAssessment->setRemarks($remarks);
        
        $this->assertSame($this->qualityAssessment, $result);
        $this->assertSame($remarks, $this->qualityAssessment->getRemarks());
    }

    public function testIsCompletedWhenStatusIsCompleted(): void
    {
        $this->qualityAssessment->setAssessmentStatus('已完成');
        
        $this->assertTrue($this->qualityAssessment->isCompleted());
    }

    public function testIsNotCompletedWhenStatusIsInProgress(): void
    {
        $this->qualityAssessment->setAssessmentStatus('进行中');
        
        $this->assertFalse($this->qualityAssessment->isCompleted());
    }

    public function testCalculateLevelForExcellentScore(): void
    {
        $this->qualityAssessment->setTotalScore(95.0);
        
        $this->assertSame('优秀', $this->qualityAssessment->calculateLevel());
    }

    public function testCalculateLevelForGoodScore(): void
    {
        $this->qualityAssessment->setTotalScore(85.0);
        
        $this->assertSame('良好', $this->qualityAssessment->calculateLevel());
    }

    public function testCalculateLevelForPassableScore(): void
    {
        $this->qualityAssessment->setTotalScore(75.0);
        
        $this->assertSame('合格', $this->qualityAssessment->calculateLevel());
    }

    public function testCalculateLevelForFailedScore(): void
    {
        $this->qualityAssessment->setTotalScore(65.0);
        
        $this->assertSame('不合格', $this->qualityAssessment->calculateLevel());
    }

    public function testCalculateLevelForBoundaryScores(): void
    {
        $this->qualityAssessment->setTotalScore(90.0);
        $this->assertSame('优秀', $this->qualityAssessment->calculateLevel());

        $this->qualityAssessment->setTotalScore(89.9);
        $this->assertSame('良好', $this->qualityAssessment->calculateLevel());

        $this->qualityAssessment->setTotalScore(80.0);
        $this->assertSame('良好', $this->qualityAssessment->calculateLevel());

        $this->qualityAssessment->setTotalScore(79.9);
        $this->assertSame('合格', $this->qualityAssessment->calculateLevel());

        $this->qualityAssessment->setTotalScore(70.0);
        $this->assertSame('合格', $this->qualityAssessment->calculateLevel());

        $this->qualityAssessment->setTotalScore(69.9);
        $this->assertSame('不合格', $this->qualityAssessment->calculateLevel());
    }

    public function testIsPassedWhenScoreIsAboveThreshold(): void
    {
        $this->qualityAssessment->setTotalScore(75.0);
        
        $this->assertTrue($this->qualityAssessment->isPassed());
    }

    public function testIsPassedWhenScoreIsExactlyThreshold(): void
    {
        $this->qualityAssessment->setTotalScore(70.0);
        
        $this->assertTrue($this->qualityAssessment->isPassed());
    }

    public function testIsNotPassedWhenScoreIsBelowThreshold(): void
    {
        $this->qualityAssessment->setTotalScore(69.9);
        
        $this->assertFalse($this->qualityAssessment->isPassed());
    }

    public function testGetItemCount(): void
    {
        $items = ['教学内容', '教学方法', '师资水平', '设施设备'];
        $this->qualityAssessment->setAssessmentItems($items);
        
        $this->assertSame(4, $this->qualityAssessment->getItemCount());
    }

    public function testGetItemCountWhenEmpty(): void
    {
        $this->qualityAssessment->setAssessmentItems([]);
        
        $this->assertSame(0, $this->qualityAssessment->getItemCount());
    }

    public function testGetAverageScore(): void
    {
        $items = ['教学内容', '教学方法', '师资水平'];
        $this->qualityAssessment->setAssessmentItems($items);
        $this->qualityAssessment->setTotalScore(270.0);
        
        $this->assertSame(90.0, $this->qualityAssessment->getAverageScore());
    }

    public function testGetAverageScoreWhenNoItems(): void
    {
        $this->qualityAssessment->setAssessmentItems([]);
        $this->qualityAssessment->setTotalScore(100.0);
        
        $this->assertSame(0.0, $this->qualityAssessment->getAverageScore());
    }

    public function testToString(): void
    {
        $this->qualityAssessment->setAssessmentType('课程评估');
        $this->qualityAssessment->setTargetName('安全生产培训');
        $this->qualityAssessment->setAssessmentLevel('良好');
        
        $result = (string) $this->qualityAssessment;
        
        $this->assertSame('课程评估 - 安全生产培训 (良好)', $result);
    }

    public function testDefaultValues(): void
    {
        $this->assertSame(0.0, $this->qualityAssessment->getTotalScore());
        $this->assertSame('进行中', $this->qualityAssessment->getAssessmentStatus());
        $this->assertSame([], $this->qualityAssessment->getAssessmentItems());
        $this->assertSame([], $this->qualityAssessment->getAssessmentScores());
        $this->assertSame([], $this->qualityAssessment->getAssessmentComments());
    }
}
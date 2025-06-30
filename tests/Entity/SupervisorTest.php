<?php

namespace Tourze\TrainSupervisorBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;

class SupervisorTest extends TestCase
{
    private Supervisor $supervisor;

    protected function setUp(): void
    {
        $this->supervisor = new Supervisor();
    }

    public function testSetAndGetDate(): void
    {
        $date = new \DateTimeImmutable('2024-01-15');
        
        $result = $this->supervisor->setDate($date);
        
        $this->assertSame($this->supervisor, $result);
        $this->assertSame($date, $this->supervisor->getDate());
    }

    public function testSetAndGetTotalClassroomCount(): void
    {
        $count = 150;
        
        $result = $this->supervisor->setTotalClassroomCount($count);
        
        $this->assertSame($this->supervisor, $result);
        $this->assertSame($count, $this->supervisor->getTotalClassroomCount());
    }

    public function testSetAndGetNewClassroomCount(): void
    {
        $count = 25;
        
        $result = $this->supervisor->setNewClassroomCount($count);
        
        $this->assertSame($this->supervisor, $result);
        $this->assertSame($count, $this->supervisor->getNewClassroomCount());
    }

    public function testSetAndGetDailyLoginCount(): void
    {
        $count = 1200;
        
        $result = $this->supervisor->setDailyLoginCount($count);
        
        $this->assertSame($this->supervisor, $result);
        $this->assertSame($count, $this->supervisor->getDailyLoginCount());
    }

    public function testSetAndGetDailyLearnCount(): void
    {
        $count = 980;
        
        $result = $this->supervisor->setDailyLearnCount($count);
        
        $this->assertSame($this->supervisor, $result);
        $this->assertSame($count, $this->supervisor->getDailyLearnCount());
    }

    public function testSetAndGetDailyCheatCount(): void
    {
        $count = 5;
        
        $result = $this->supervisor->setDailyCheatCount($count);
        
        $this->assertSame($this->supervisor, $result);
        $this->assertSame($count, $this->supervisor->getDailyCheatCount());
    }

    public function testSetAndGetFaceDetectSuccessCount(): void
    {
        $count = 950;
        
        $result = $this->supervisor->setFaceDetectSuccessCount($count);
        
        $this->assertSame($this->supervisor, $result);
        $this->assertSame($count, $this->supervisor->getFaceDetectSuccessCount());
    }

    public function testSetAndGetFaceDetectFailCount(): void
    {
        $count = 30;
        
        $result = $this->supervisor->setFaceDetectFailCount($count);
        
        $this->assertSame($this->supervisor, $result);
        $this->assertSame($count, $this->supervisor->getFaceDetectFailCount());
    }

    public function testDefaultValues(): void
    {
        $this->assertSame(0, $this->supervisor->getTotalClassroomCount());
        $this->assertSame(0, $this->supervisor->getNewClassroomCount());
        $this->assertSame(0, $this->supervisor->getDailyLoginCount());
        $this->assertSame(0, $this->supervisor->getDailyLearnCount());
        $this->assertSame(0, $this->supervisor->getDailyCheatCount());
        $this->assertSame(0, $this->supervisor->getFaceDetectSuccessCount());
        $this->assertSame(0, $this->supervisor->getFaceDetectFailCount());
    }

    public function testToStringWhenIdIsNull(): void
    {
        $result = (string) $this->supervisor;
        
        $this->assertSame('', $result);
    }

    public function testCountsCanBeZero(): void
    {
        $this->supervisor
            ->setTotalClassroomCount(0)
            ->setNewClassroomCount(0)
            ->setDailyLoginCount(0)
            ->setDailyLearnCount(0)
            ->setDailyCheatCount(0)
            ->setFaceDetectSuccessCount(0)
            ->setFaceDetectFailCount(0);

        $this->assertSame(0, $this->supervisor->getTotalClassroomCount());
        $this->assertSame(0, $this->supervisor->getNewClassroomCount());
        $this->assertSame(0, $this->supervisor->getDailyLoginCount());
        $this->assertSame(0, $this->supervisor->getDailyLearnCount());
        $this->assertSame(0, $this->supervisor->getDailyCheatCount());
        $this->assertSame(0, $this->supervisor->getFaceDetectSuccessCount());
        $this->assertSame(0, $this->supervisor->getFaceDetectFailCount());
    }

    public function testCountsCanBePositive(): void
    {
        $this->supervisor
            ->setTotalClassroomCount(100)
            ->setNewClassroomCount(20)
            ->setDailyLoginCount(500)
            ->setDailyLearnCount(450)
            ->setDailyCheatCount(3)
            ->setFaceDetectSuccessCount(440)
            ->setFaceDetectFailCount(10);

        $this->assertSame(100, $this->supervisor->getTotalClassroomCount());
        $this->assertSame(20, $this->supervisor->getNewClassroomCount());
        $this->assertSame(500, $this->supervisor->getDailyLoginCount());
        $this->assertSame(450, $this->supervisor->getDailyLearnCount());
        $this->assertSame(3, $this->supervisor->getDailyCheatCount());
        $this->assertSame(440, $this->supervisor->getFaceDetectSuccessCount());
        $this->assertSame(10, $this->supervisor->getFaceDetectFailCount());
    }

    public function testDateTimeImmutableSetAndGet(): void
    {
        $now = new \DateTimeImmutable();
        $this->supervisor->setDate($now);
        
        $this->assertInstanceOf(\DateTimeInterface::class, $this->supervisor->getDate());
        $this->assertSame($now, $this->supervisor->getDate());
    }

    public function testFaceDetectSuccessCountReturnsNullableInt(): void
    {
        $this->assertSame(0, $this->supervisor->getFaceDetectSuccessCount());
        
        $this->supervisor->setFaceDetectSuccessCount(100);
        $this->assertSame(100, $this->supervisor->getFaceDetectSuccessCount());
    }

    public function testAllSettersReturnSelfForChaining(): void
    {
        $date = new \DateTimeImmutable();
        
        $result = $this->supervisor
            ->setDate($date)
            ->setTotalClassroomCount(100)
            ->setNewClassroomCount(20)
            ->setDailyLoginCount(500)
            ->setDailyLearnCount(450)
            ->setDailyCheatCount(3)
            ->setFaceDetectSuccessCount(440)
            ->setFaceDetectFailCount(10);

        $this->assertSame($this->supervisor, $result);
    }
}
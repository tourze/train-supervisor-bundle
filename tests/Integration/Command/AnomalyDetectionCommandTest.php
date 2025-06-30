<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Command\AnomalyDetectionCommand;

class AnomalyDetectionCommandTest extends TestCase
{
    public function testCommandExists(): void
    {
        $this->assertTrue(class_exists(AnomalyDetectionCommand::class));
    }
}
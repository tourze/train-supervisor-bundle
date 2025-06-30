<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Command\DailySupervisionDataCommand;

class DailySupervisionDataCommandTest extends TestCase
{
    public function testCommandExists(): void
    {
        $this->assertTrue(class_exists(DailySupervisionDataCommand::class));
    }
}
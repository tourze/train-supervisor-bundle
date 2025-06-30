<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Command\SupervisionReportCommand;

class SupervisionReportCommandTest extends TestCase
{
    public function testCommandExists(): void
    {
        $this->assertTrue(class_exists(SupervisionReportCommand::class));
    }
}
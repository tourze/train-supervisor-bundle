<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Command\QualityAssessmentCommand;

class QualityAssessmentCommandTest extends TestCase
{
    public function testCommandExists(): void
    {
        $this->assertTrue(class_exists(QualityAssessmentCommand::class));
    }
}
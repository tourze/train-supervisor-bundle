<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Controller\Admin\SupervisionReportCrudController;

class SupervisionReportCrudControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(SupervisionReportCrudController::class));
    }
}
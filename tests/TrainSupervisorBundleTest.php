<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\TrainSupervisorBundle\TrainSupervisorBundle;

/**
 * @internal
 */
#[CoversClass(TrainSupervisorBundle::class)]
#[RunTestsInSeparateProcesses]
final class TrainSupervisorBundleTest extends AbstractBundleTestCase
{
}

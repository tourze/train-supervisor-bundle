<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * Train Supervisor Bundle 专用的测试基类.
 *
 * 继承自 AbstractEasyAdminControllerTestCase 以获得标准化的 CRUD 测试支持.
 *
 * @internal
 */
#[CoversClass(AbstractEasyAdminControllerTestCase::class)]
#[RunTestsInSeparateProcesses]
abstract class AbstractTrainSupervisorTestCase extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<object>
     */
    #[\ReturnTypeWillChange]
    abstract protected function getControllerService(): AbstractCrudController;

    /**
     * @return iterable<string, array{string}>
     */
    abstract public static function provideIndexPageHeaders(): iterable;
}

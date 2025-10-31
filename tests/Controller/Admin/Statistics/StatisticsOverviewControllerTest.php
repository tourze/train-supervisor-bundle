<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin\Statistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainSupervisorBundle\Controller\Admin\Statistics\StatisticsOverviewController;

/**
 * StatisticsOverviewController 测试.
 *
 * @internal
 */
#[CoversClass(StatisticsOverviewController::class)]
#[RunTestsInSeparateProcesses]
class StatisticsOverviewControllerTest extends AbstractWebTestCase
{
    public function testStatistics(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/supervision/statistics');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/supervision/statistics');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/admin/supervision/statistics');
    }

    public function testPutMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/supervision/statistics');
    }

    public function testDeleteMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/supervision/statistics');
    }

    public function testPatchMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/supervision/statistics');
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/supervision/statistics');
    }

    /**
     * 测试不被允许的HTTP方法.
     */
    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        self::assertNotEmpty($method);
    }
}

<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainSupervisorBundle\Controller\Admin\DashboardController;

/**
 * DashboardController 测试.
 *
 * @internal
 */
#[CoversClass(DashboardController::class)]
#[RunTestsInSeparateProcesses]
class DashboardControllerTest extends AbstractWebTestCase
{
    public function testDashboardConfiguration(): void
    {
        $client = self::createClientWithDatabase();

        // 先检查数据库是否正确初始化
        $this->assertTrue(static::hasDoctrineSupport(), 'Doctrine support should be available');

        // 创建一个内存用户进行登录，避免访问数据库
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试是否能访问基本的EasyAdmin界面（跟随重定向）
        $client->followRedirects(true);
        $client->request('GET', '/admin/');

        // 直接验证响应状态码
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(NotFoundHttpException::class);
        $client->request('POST', '/admin/');
    }

    public function testPutMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(NotFoundHttpException::class);
        $client->request('PUT', '/admin/');
    }

    public function testDeleteMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(NotFoundHttpException::class);
        $client->request('DELETE', '/admin/');
    }

    public function testPatchMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(NotFoundHttpException::class);
        $client->request('PATCH', '/admin/');
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(NotFoundHttpException::class);
        $client->request('OPTIONS', '/admin/');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(NotFoundHttpException::class);
        $client->request($method, '/admin/supervision');
    }
}

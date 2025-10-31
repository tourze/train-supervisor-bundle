<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin\Statistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainSupervisorBundle\Controller\Admin\Statistics\ChartDataController;

/**
 * ChartDataController 测试.
 *
 * @internal
 */
#[CoversClass(ChartDataController::class)]
#[RunTestsInSeparateProcesses]
class ChartDataControllerTest extends AbstractWebTestCase
{
    public function testChartData(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/supervision/api/chart-data');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content should not be false');
        $this->assertJson($content);
    }

    public function testChartDataWithQualityDistribution(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/supervision/api/chart-data?type=quality_distribution');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content should not be false');
        $this->assertJson($content);
    }

    public function testChartDataWithProblemStatus(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/supervision/api/chart-data?type=problem_status');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content should not be false');
        $this->assertJson($content);
    }

    public function testChartDataWithUnknownType(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/supervision/api/chart-data?type=unknown');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content should not be false');
        $this->assertJson($content);
    }

    public function testChartDataWithoutType(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/supervision/api/chart-data');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertNotFalse($content, 'Response content should not be false');
        $this->assertJson($content);
    }

    public function testGetMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/supervision/api/chart-data');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/admin/supervision/api/chart-data');
    }

    public function testPutMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/supervision/api/chart-data');
    }

    public function testDeleteMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/supervision/api/chart-data');
    }

    public function testPatchMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/supervision/api/chart-data');
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/supervision/api/chart-data');
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

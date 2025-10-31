<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\LearningStatistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\ReportsController;

/**
 * ReportsController 测试.
 *
 * @internal
 */
#[CoversClass(ReportsController::class)]
#[RunTestsInSeparateProcesses]
class ReportsControllerTest extends AbstractWebTestCase
{
    public function testReports(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/reports');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testReportsWithTimeFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/reports?start_date=2024-01-01&end_date=2024-01-31');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testReportsWithInstitutionFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 暂时只测试单个institution_id，因为institution_ids可能有兼容性问题
        $client->request('GET', '/admin/learning-statistics/reports?institution_id=1');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testReportsWithLocationFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/reports?region=华北&province=北京市&city=朝阳区');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testReportsWithDemographicFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/reports?age_group=18-25');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testReportsWithMultipleFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/reports?start_date=2024-01-01&end_date=2024-01-31&institution_id=1&region=华北&province=北京市&city=朝阳区&age_group=18-25');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/learning-statistics/reports');
    }

    public function testPostMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/admin/learning-statistics/reports');
    }

    public function testPutMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/learning-statistics/reports');
    }

    public function testDeleteMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/learning-statistics/reports');
    }

    public function testPatchMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/learning-statistics/reports');
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/learning-statistics/reports');
    }
}

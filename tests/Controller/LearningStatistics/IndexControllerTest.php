<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\LearningStatistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\IndexController;

/**
 * IndexController 测试.
 *
 * @internal
 */
#[CoversClass(IndexController::class)]
#[RunTestsInSeparateProcesses]
class IndexControllerTest extends AbstractWebTestCase
{
    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexWithFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics?start_date=2024-01-01&end_date=2024-01-31&institution_id=1&region=华北&age_group=18-25');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexWithInstitutionIds(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics?institution_ids=1,2,3');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexWithLocationFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics?province=北京市&city=朝阳区');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexWithMultipleFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics?start_date=2024-01-01&end_date=2024-01-31&institution_id=1&region=华北&province=北京市&city=朝阳区&age_group=18-25');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/learning-statistics');
    }

    public function testPostMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/admin/learning-statistics');
    }

    public function testPutMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/learning-statistics');
    }

    public function testDeleteMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/learning-statistics');
    }

    public function testPatchMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/learning-statistics');
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/learning-statistics');
    }
}

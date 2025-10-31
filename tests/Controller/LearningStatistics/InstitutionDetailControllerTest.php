<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\LearningStatistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\InstitutionDetailController;

/**
 * InstitutionDetailController 测试.
 *
 * @internal
 */
#[CoversClass(InstitutionDetailController::class)]
#[RunTestsInSeparateProcesses]
class InstitutionDetailControllerTest extends AbstractWebTestCase
{
    public function testInstitutionDetail(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/institution/1');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInstitutionDetailWithFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/institution/1?start_date=2024-01-01&end_date=2024-01-31&region=华北&age_group=18-25');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInstitutionDetailWithLocationFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/institution/1?province=北京市&city=朝阳区');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInstitutionDetailWithMultipleFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/institution/1?start_date=2024-01-01&end_date=2024-01-31&region=华北&province=北京市&city=朝阳区&age_group=18-25');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInstitutionDetailWithDifferentIds(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/institution/2');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/learning-statistics/institution/1');
    }

    public function testPostMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/admin/learning-statistics/institution/1');
    }

    public function testPutMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/learning-statistics/institution/1');
    }

    public function testDeleteMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/learning-statistics/institution/1');
    }

    public function testPatchMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/learning-statistics/institution/1');
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/learning-statistics/institution/1');
    }
}

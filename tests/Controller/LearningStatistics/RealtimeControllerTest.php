<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\LearningStatistics;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\RealtimeController;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;

/**
 * RealtimeController 测试.
 *
 * @internal
 */
#[CoversClass(RealtimeController::class)]
#[RunTestsInSeparateProcesses]
class RealtimeControllerTest extends AbstractWebTestCase
{
    public function testRealtime(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试供应商
        $supplier = new Supplier();
        $supplier->setName('Test Supplier');
        self::getEntityManager()->persist($supplier);
        self::getEntityManager()->flush();

        // 创建今日的监督数据
        $today = new \DateTimeImmutable();
        $supervisorData = new SupervisorData();
        $supervisorData->setSupplier($supplier);
        $supervisorData->setDate($today);
        $supervisorData->setDailyLoginCount(100);
        $supervisorData->setDailyLearnCount(80);
        $supervisorData->setTotalClassroomCount(10);
        $supervisorData->setNewClassroomCount(2);
        self::getEntityManager()->persist($supervisorData);
        self::getEntityManager()->flush();

        // 检查是否有重定向
        $client->request('GET', '/admin/learning-statistics/realtime');

        // 如果是重定向，获取 flash 消息
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
            echo "\nFlash messages:\n";
            $crawler = $client->getCrawler();
            $alerts = $crawler->filter('.alert');
            foreach ($alerts as $alert) {
                echo '- ' . trim($alert->textContent) . "\n";
            }
        }

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRealtimeWithInstitutionFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/realtime?institution_id=1');

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRealtimeWithInstitutionIdsFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/realtime?institution_ids=1,2,3');

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRealtimeWithLocationFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/realtime?region=华北&province=北京市&city=朝阳区');

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRealtimeWithAgeGroupFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/realtime?age_group=18-25');

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRealtimeWithMultipleFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/learning-statistics/realtime?institution_id=1&region=华北&province=北京市&city=朝阳区&age_group=18-25');

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/admin/learning-statistics/realtime');
    }

    public function testPostMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/admin/learning-statistics/realtime');
    }

    public function testPutMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/admin/learning-statistics/realtime');
    }

    public function testDeleteMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/admin/learning-statistics/realtime');
    }

    public function testPatchMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/admin/learning-statistics/realtime');
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/admin/learning-statistics/realtime');
    }
}

<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\TrainSupervisorBundle\Controller\Admin\SupervisorDataCrudController;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;
use Tourze\TrainSupervisorBundle\Tests\Controller\Admin\AbstractTrainSupervisorTestCase;

/**
 * SupervisorDataCrudController 测试.
 *
 * @internal
 */
#[CoversClass(SupervisorDataCrudController::class)]
#[RunTestsInSeparateProcesses]
class SupervisorDataCrudControllerTest extends AbstractTrainSupervisorTestCase
{
    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supervisor-data');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNew(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supervisor-data/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreate(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $supplier = $this->createSupplier();
        $crawler = $client->request('GET', '/admin/train-supervisor/supervisor-data/new');
        $form = $crawler->selectButton('Create')->form();
        $form['SupervisorData[date]'] = '2024-01-01';
        $form['SupervisorData[supplier]'] = (string) $supplier->getId();
        $form['SupervisorData[dailyLoginCount]'] = '100';
        $form['SupervisorData[dailyLearnCount]'] = '80';

        $client->submit($form);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());
    }

    public function testEdit(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $supervisorData = $this->createSupervisorData();
        $client->request('GET', '/admin/train-supervisor/supervisor-data/' . $supervisorData->getId() . '/edit');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $supervisorData = $this->createSupervisorData();
        $client->request('POST', '/admin/train-supervisor/supervisor-data/' . $supervisorData->getId() . '/delete');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());
    }

    public function testDetail(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $supervisorData = $this->createSupervisorData();
        $client->request('GET', '/admin/train-supervisor/supervisor-data/' . $supervisorData->getId());
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateValidationFailure(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervisor-data/new');
        $form = $crawler->selectButton('Create')->form();
        // 不填写必填字段
        $form['SupervisorData[dailyLoginCount]'] = '100';

        $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervisor-data/new');
        $form = $crawler->selectButton('Create')->form();

        // 测试必填字段验证 - 不填写任何必填字段
        $crawler = $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('不能为空', $crawler->filter('.invalid-feedback')->text());

        // 测试只填写部分字段但仍缺少必填字段
        $form = $crawler->selectButton('Create')->form();
        $form['SupervisorData[dailyLoginCount]'] = '100';
        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('不能为空', $crawler->text());

        // 测试日期字段验证错误
        $form = $crawler->selectButton('Create')->form();
        $form['SupervisorData[date]'] = 'invalid-date';
        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    private function createSupervisorData(): SupervisorData
    {
        $supplier = $this->createSupplier();
        $supervisorData = new SupervisorData();
        $supervisorData->setDate(new \DateTimeImmutable('2024-01-01'));
        $supervisorData->setSupplier($supplier);
        $supervisorData->setDailyLoginCount(100);
        $supervisorData->setDailyLearnCount(80);
        $supervisorData->setTotalClassroomCount(50);
        $supervisorData->setNewClassroomCount(5);

        self::getEntityManager()->persist($supervisorData);
        self::getEntityManager()->flush();

        return $supervisorData;
    }

    private function createSupplier(): Supplier
    {
        $supplier = new Supplier();
        $supplier->setName('测试供应商');
        $supplier->setContact('张三');
        $supplier->setPhone('13800138000');
        $supplier->setEmail('test@example.com');

        self::getEntityManager()->persist($supplier);
        self::getEntityManager()->flush();

        return $supplier;
    }

    /**
     * @return AbstractCrudController<object>
     */
    #[\ReturnTypeWillChange]
    protected function getControllerService(): AbstractCrudController
    {
        /** @phpstan-ignore-next-line */
        return self::getService(SupervisorDataCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '日期' => ['日期'];
        yield '供应商' => ['供应商'];
        yield '日登录人数' => ['日登录人数'];
        yield '日学习人数' => ['日学习人数'];
        yield '总班级数' => ['总班级数'];
        yield '新增班级数' => ['新增班级数'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'date' => ['date'];
        yield 'supplier' => ['supplier'];
        yield 'dailyLoginCount' => ['dailyLoginCount'];
        yield 'dailyLearnCount' => ['dailyLearnCount'];
        yield 'totalClassroomCount' => ['totalClassroomCount'];
        yield 'newClassroomCount' => ['newClassroomCount'];
        yield 'region' => ['region'];
        yield 'province' => ['province'];
        yield 'city' => ['city'];
        yield 'ageGroup' => ['ageGroup'];
        yield 'dailyCheatCount' => ['dailyCheatCount'];
        yield 'faceDetectSuccessCount' => ['faceDetectSuccessCount'];
        yield 'faceDetectFailCount' => ['faceDetectFailCount'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'date' => ['date'];
        yield 'supplier' => ['supplier'];
        yield 'dailyLoginCount' => ['dailyLoginCount'];
        yield 'dailyLearnCount' => ['dailyLearnCount'];
        yield 'totalClassroomCount' => ['totalClassroomCount'];
        yield 'newClassroomCount' => ['newClassroomCount'];
        yield 'region' => ['region'];
        yield 'province' => ['province'];
        yield 'city' => ['city'];
        yield 'ageGroup' => ['ageGroup'];
        yield 'dailyCheatCount' => ['dailyCheatCount'];
        yield 'faceDetectSuccessCount' => ['faceDetectSuccessCount'];
        yield 'faceDetectFailCount' => ['faceDetectFailCount'];
    }
}

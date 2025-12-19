<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\TrainSupervisorBundle\Controller\Admin\SupervisorCrudController;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;
use Tourze\TrainSupervisorBundle\Tests\Controller\Admin\AbstractTrainSupervisorTestCase;

/**
 * SupervisorCrudController 测试.
 *
 * @internal
 */
#[CoversClass(SupervisorCrudController::class)]
#[RunTestsInSeparateProcesses]
class SupervisorCrudControllerTest extends AbstractTrainSupervisorTestCase
{
    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supervisor');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNew(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supervisor/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreate(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervisor/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // 提交表单数据
        $client->submitForm(
            'Create',
            [
                'Supervisor[supervisorName]' => '测试监督员',
                'Supervisor[supervisorCode]' => 'TEST_CREATE_' . uniqid(),
                'Supervisor[department]' => '测试部门',
                'Supervisor[position]' => '测试职位',
                'Supervisor[contactPhone]' => '13800138000',
                'Supervisor[contactEmail]' => 'test@example.com',
                'Supervisor[supervisorLevel]' => '初级',
                'Supervisor[supervisorStatus]' => '在职',
                'Supervisor[specialties]' => '测试专业领域',
                'Supervisor[qualifications]' => '测试资质证书',
                'Supervisor[workExperience]' => '测试工作经历',
                'Supervisor[remarks]' => '测试备注',
            ]
        );
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
    }

    public function testEdit(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个测试实体
        $supervisor = new Supervisor();
        $supervisor->setSupervisorName('测试监督员编辑');
        $supervisor->setSupervisorCode('TEST_EDIT_' . uniqid());
        $supervisor->setDepartment('测试部门');
        $supervisor->setPosition('测试职位');
        $supervisor->setContactPhone('13800138000');
        $supervisor->setContactEmail('test-edit@example.com');
        $supervisor->setSupervisorLevel('初级');
        $supervisor->setSupervisorStatus('在职');
        $supervisor->setSpecialties('测试专业领域');
        $supervisor->setQualifications('测试资质证书');
        $supervisor->setWorkExperience('测试工作经历');
        $supervisor->setRemarks('测试备注');

        $em = self::getEntityManager();
        $em->persist($supervisor);
        $em->flush();

        $client->request('GET', '/admin/train-supervisor/supervisor/' . $supervisor->getId() . '/edit');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个测试实体
        $supervisor = new Supervisor();
        $supervisor->setSupervisorName('测试监督员删除');
        $supervisor->setSupervisorCode('TEST_DELETE_' . uniqid());
        $supervisor->setDepartment('测试部门');
        $supervisor->setPosition('测试职位');
        $supervisor->setContactPhone('13800138000');
        $supervisor->setContactEmail('test-delete@example.com');
        $supervisor->setSupervisorLevel('初级');
        $supervisor->setSupervisorStatus('在职');

        $em = self::getEntityManager();
        $em->persist($supervisor);
        $em->flush();

        $client->request('POST', '/admin/train-supervisor/supervisor/' . $supervisor->getId() . '/delete');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
    }

    public function testDetail(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个测试实体
        $supervisor = new Supervisor();
        $supervisor->setSupervisorName('测试监督员详情');
        $supervisor->setSupervisorCode('TEST_DETAIL_' . uniqid());
        $supervisor->setDepartment('测试部门');
        $supervisor->setPosition('测试职位');
        $supervisor->setContactPhone('13800138000');
        $supervisor->setContactEmail('test-detail@example.com');
        $supervisor->setSupervisorLevel('初级');
        $supervisor->setSupervisorStatus('在职');

        $em = self::getEntityManager();
        $em->persist($supervisor);
        $em->flush();

        $client->request('GET', '/admin/train-supervisor/supervisor/' . $supervisor->getId());
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateValidationFailure(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervisor/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // 测试必填字段验证 - 提交空表单
        $form = $crawler->selectButton('Create')->form();
        $crawler = $client->submit($form);

        // 应该显示验证错误而不是重定向
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('监督员姓名不能为空', $crawler->filter('.invalid-feedback')->text());
    }

    public function testSearchWithSupervisorLevelFilter(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按监督员级别搜索
        $client->request('GET', '/admin/train-supervisor/supervisor?filters[supervisorLevel]=初级');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithSupervisorStatusFilter(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按监督员状态搜索
        $client->request('GET', '/admin/train-supervisor/supervisor?filters[supervisorStatus]=在职');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithMultipleFilters(): void
    {
        $client = self::createClientWithDatabase();

        // 创建内存用户登录
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试组合过滤器
        $client->request('GET', '/admin/train-supervisor/supervisor?filters[supervisorLevel]=初级&filters[supervisorStatus]=在职');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return AbstractCrudController<object>
     */
    #[\ReturnTypeWillChange]
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(SupervisorCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '监督员姓名' => ['监督员姓名'];
        yield '监督员编号' => ['监督员编号'];
        yield '所属部门' => ['所属部门'];
        yield '职位' => ['职位'];
        yield '联系电话' => ['联系电话'];
        yield '联系邮箱' => ['联系邮箱'];
        yield '监督员级别' => ['监督员级别'];
        yield '状态' => ['状态'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'supervisorName' => ['supervisorName'];
        yield 'supervisorCode' => ['supervisorCode'];
        yield 'department' => ['department'];
        yield 'position' => ['position'];
        yield 'contactPhone' => ['contactPhone'];
        yield 'contactEmail' => ['contactEmail'];
        yield 'supervisorLevel' => ['supervisorLevel'];
        yield 'supervisorStatus' => ['supervisorStatus'];
        yield 'specialties' => ['specialties'];
        yield 'qualifications' => ['qualifications'];
        yield 'workExperience' => ['workExperience'];
        yield 'remarks' => ['remarks'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'supervisorName' => ['supervisorName'];
        yield 'supervisorCode' => ['supervisorCode'];
        yield 'department' => ['department'];
        yield 'position' => ['position'];
        yield 'contactPhone' => ['contactPhone'];
        yield 'contactEmail' => ['contactEmail'];
        yield 'supervisorLevel' => ['supervisorLevel'];
        yield 'supervisorStatus' => ['supervisorStatus'];
        yield 'specialties' => ['specialties'];
        yield 'qualifications' => ['qualifications'];
        yield 'workExperience' => ['workExperience'];
        yield 'remarks' => ['remarks'];
    }
}

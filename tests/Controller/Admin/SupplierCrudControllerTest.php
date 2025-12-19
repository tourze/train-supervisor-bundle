<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\TrainSupervisorBundle\Controller\Admin\SupplierCrudController;
use Tourze\TrainSupervisorBundle\Entity\Supplier;
use Tourze\TrainSupervisorBundle\Tests\Controller\Admin\AbstractTrainSupervisorTestCase;

/**
 * SupplierCrudController 测试.
 *
 * @internal
 */
#[CoversClass(SupplierCrudController::class)]
#[RunTestsInSeparateProcesses]
class SupplierCrudControllerTest extends AbstractTrainSupervisorTestCase
{
    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supplier');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNew(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supplier/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreate(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supplier/new');
        $form = $crawler->selectButton('Create')->form();
        $form['Supplier[name]'] = '新测试供应商';
        $form['Supplier[contact]'] = '李四';
        $form['Supplier[phone]'] = '13900139000';
        $form['Supplier[email]'] = 'lisi@example.com';
        $form['Supplier[status]'] = 'active';

        $client->submit($form);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());
    }

    public function testEdit(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $supplier = $this->createSupplier();
        $client->request('GET', '/admin/train-supervisor/supplier/' . $supplier->getId() . '/edit');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $supplier = $this->createSupplier();
        $client->request('POST', '/admin/train-supervisor/supplier/' . $supplier->getId() . '/delete');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());
    }

    public function testDetail(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $supplier = $this->createSupplier();
        $client->request('GET', '/admin/train-supervisor/supplier/' . $supplier->getId());
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateValidationFailure(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supplier/new');
        $form = $crawler->selectButton('Create')->form();
        // 不填写必填字段 name
        $form['Supplier[contact]'] = '测试联系人';

        $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supplier/new');
        $form = $crawler->selectButton('Create')->form();

        // 测试必填字段验证 - 不填写任何必填字段
        $crawler = $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('不能为空', $crawler->filter('.invalid-feedback')->text());

        // 测试只填写部分字段但仍缺少必填字段 name
        $form = $crawler->selectButton('Create')->form();
        $form['Supplier[contact]'] = '测试联系人';
        $form['Supplier[phone]'] = '13800138000';
        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('不能为空', $crawler->text());

        // 测试邮箱格式验证错误
        $form = $crawler->selectButton('Create')->form();
        $form['Supplier[name]'] = '测试供应商';
        $form['Supplier[email]'] = 'invalid-email-format';
        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('邮箱格式不正确', $crawler->text());

        // 测试电话格式验证错误
        $form = $crawler->selectButton('Create')->form();
        $form['Supplier[name]'] = '测试供应商';
        $form['Supplier[phone]'] = '123';
        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testSearchWithNameFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $supplier = $this->createSupplier();
        $supplierName = $supplier->getName();
        if (null !== $supplierName) {
            $client->request('GET', '/admin/train-supervisor/supplier?query=' . urlencode($supplierName));
        } else {
            $client->request('GET', '/admin/train-supervisor/supplier?query=');
        }
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithStatusFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $this->createSupplier();
        $client->request('GET', '/admin/train-supervisor/supplier?filters[status]=active');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    private function createSupplier(): Supplier
    {
        $supplier = new Supplier();
        $supplier->setName('测试供应商');
        $supplier->setContact('张三');
        $supplier->setPhone('13800138000');
        $supplier->setEmail('zhangsan@example.com');
        $supplier->setAddress('北京市朝阳区测试地址');
        $supplier->setStatus('active');

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
        return self::getService(SupplierCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '供应商名称' => ['供应商名称'];
        yield '联系人' => ['联系人'];
        yield '联系电话' => ['联系电话'];
        yield '联系邮箱' => ['联系邮箱'];
        yield '状态' => ['状态'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'contact' => ['contact'];
        yield 'phone' => ['phone'];
        yield 'email' => ['email'];
        yield 'address' => ['address'];
        yield 'status' => ['status'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'name' => ['name'];
        yield 'contact' => ['contact'];
        yield 'phone' => ['phone'];
        yield 'email' => ['email'];
        yield 'address' => ['address'];
        yield 'status' => ['status'];
    }
}

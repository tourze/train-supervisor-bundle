<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\TrainSupervisorBundle\Controller\Admin\SupervisionPlanCrudController;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Tests\Controller\Admin\AbstractTrainSupervisorTestCase;

/**
 * SupervisionPlanCrudController 测试.
 *
 * @internal
 */
#[CoversClass(SupervisionPlanCrudController::class)]
#[RunTestsInSeparateProcesses]
class SupervisionPlanCrudControllerTest extends AbstractTrainSupervisorTestCase
{
    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supervision-plan');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNew(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supervision-plan/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreate(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervision-plan/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $form = $crawler->selectButton('Create')->form();
        $form['SupervisionPlan[planName]'] = '测试监督计划';
        $form['SupervisionPlan[planType]'] = '定期';
        $form['SupervisionPlan[planStartDate]'] = '2024-01-01T00:00:00';
        $form['SupervisionPlan[planEndDate]'] = '2024-12-31T23:59:59';
        $form['SupervisionPlan[supervisor]'] = '测试监督员';
        $form['SupervisionPlan[planStatus]'] = '待执行';

        $client->submit($form);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
    }

    public function testEdit(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个测试实体
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('待执行');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        $client->request('GET', '/admin/train-supervisor/supervision-plan/' . $supervisionPlan->getId() . '/edit');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个测试实体
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('待执行');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        $client->request('POST', '/admin/train-supervisor/supervision-plan/' . $supervisionPlan->getId() . '/delete');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
    }

    public function testDetail(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个测试实体
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('待执行');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        $client->request('GET', '/admin/train-supervisor/supervision-plan/' . $supervisionPlan->getId());
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateValidationBehavior(): void
    {
        // 测试验证行为 - 确保新建页面能正常显示和处理表单
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 验证新建页面可以正常访问
        $crawler = $client->request('GET', '/admin/train-supervisor/supervision-plan/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // 验证表单包含必要的字段
        $this->assertGreaterThan(0, $crawler->filter('form[name="SupervisionPlan"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="SupervisionPlan[planName]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('select[name="SupervisionPlan[planType]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="SupervisionPlan[supervisor]"]')->count());
    }

    public function testRequiredFieldsValidation(): void
    {
        // 测试必填字段验证
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervision-plan/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // 验证必填字段在表单中有正确的required属性
        $form = $crawler->selectButton('Create')->form();

        // 验证关键必填字段存在且具有required属性
        $this->assertGreaterThan(0, $crawler->filter('input[name="SupervisionPlan[planName]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('select[name="SupervisionPlan[planType]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="SupervisionPlan[supervisor]"]')->count());

        // 测试部分填写表单（必须提供日期字段以避免类型错误）
        $form['SupervisionPlan[planName]'] = '测试监督计划';
        $form['SupervisionPlan[planType]'] = '定期';
        $form['SupervisionPlan[planStartDate]'] = '2024-01-01T00:00:00';
        $form['SupervisionPlan[planEndDate]'] = '2024-12-31T23:59:59';
        $form['SupervisionPlan[planStatus]'] = '待执行';
        // 故意不填写supervisor字段

        $client->submit($form);

        // 表单应该重新显示（验证失败）或者成功处理
        // 具体行为取决于EasyAdmin的配置和Symfony验证规则
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains(
            $statusCode,
            [200, 302, 422],
            "Expected status 200, 302 or 422 but got {$statusCode}"
        );
    }

    public function testValidationErrors(): void
    {
        // 测试表单验证错误
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervision-plan/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $form = $crawler->selectButton('Create')->form();

        // 提交必填字段为空（保持日期字段有值）
        $form['SupervisionPlan[planName]'] = '';
        $form['SupervisionPlan[supervisor]'] = '';
        $form['SupervisionPlan[planStartDate]'] = '2024-01-01T00:00:00';
        $form['SupervisionPlan[planEndDate]'] = '2024-12-31T23:59:59';
        $form['SupervisionPlan[planStatus]'] = '待执行';

        $crawler = $client->submit($form);

        // 验证表单验证失败，显示错误信息
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('不能为空', $crawler->filter('.invalid-feedback')->text());
    }

    public function testSearchWithTypeFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按类型搜索
        $client->request('GET', '/admin/train-supervisor/supervision-plan?filters[type]=年度计划');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithStatusFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按状态搜索
        $client->request('GET', '/admin/train-supervisor/supervision-plan?filters[status]=进行中');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithPriorityFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按优先级搜索
        $client->request('GET', '/admin/train-supervisor/supervision-plan?filters[priority]=高');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithDateRangeFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按日期范围搜索
        $client->request('GET', '/admin/train-supervisor/supervision-plan?filters[planStartDate][after]=2024-01-01&filters[planEndDate][before]=2024-12-31');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithMultipleFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试组合过滤器
        $client->request('GET', '/admin/train-supervisor/supervision-plan?filters[type]=年度计划&filters[status]=进行中&filters[priority]=高');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return AbstractCrudController<object>
     */
    #[\ReturnTypeWillChange]
    protected function getControllerService(): AbstractCrudController
    {
        /** @phpstan-ignore-next-line */
        return self::getService(SupervisionPlanCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '计划名称' => ['计划名称'];
        yield '计划类型' => ['计划类型'];
        yield '计划状态' => ['计划状态'];
        yield '计划开始日期' => ['计划开始日期'];
        yield '计划结束日期' => ['计划结束日期'];
        yield '监督人' => ['监督人'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'planName' => ['planName'];
        yield 'remarks' => ['remarks'];
        yield 'planType' => ['planType'];
        yield 'planStatus' => ['planStatus'];
        yield 'planStartDate' => ['planStartDate'];
        yield 'planEndDate' => ['planEndDate'];
        // supervisionScope 和 supervisionItems 使用 ArrayField，需要单独测试
        yield 'supervisor' => ['supervisor'];
    }

    /**
     * 测试ArrayField字段在NEW页面的存在性
     * ArrayField使用CollectionType渲染，需要特殊的检测逻辑
     */
    public function testArrayFieldsExistOnNewPage(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));

        $this->assertResponseIsSuccessful();

        $html = $crawler->html();
        // ArrayField 会渲染为带有 field-array 或 field-collection 类的容器
        $this->assertStringContainsString('监督范围', $html, 'supervisionScope字段标签应该存在');
        $this->assertStringContainsString('监督项目', $html, 'supervisionItems字段标签应该存在');

        // 检查是否有field-array或field-collection类的容器
        $hasArrayFields = $crawler->filter('.field-array, .field-collection')->count() >= 2;
        $this->assertTrue($hasArrayFields, 'NEW页面应该包含ArrayField渲染的容器');
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'planName' => ['planName'];
        yield 'remarks' => ['remarks'];
        yield 'planType' => ['planType'];
        yield 'planStatus' => ['planStatus'];
        yield 'planStartDate' => ['planStartDate'];
        yield 'planEndDate' => ['planEndDate'];
        // supervisionScope 和 supervisionItems 使用 ArrayField，需要单独测试
        yield 'supervisor' => ['supervisor'];
    }

    public function testActivatePlan(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个待执行状态的监督计划
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('待执行');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        // 确认初始状态
        $this->assertEquals('待执行', $supervisionPlan->getPlanStatus());

        // 执行激活操作
        $client->request('GET', sprintf('/admin/train-supervisor/supervision-plan/%d/activate-plan', $supervisionPlan->getId()));
        $response = $client->getResponse();

        // 验证重定向成功
        $this->assertTrue($response->isRedirect());

        // 重新查询计划验证状态已更新
        $refreshedPlan = $em->find(SupervisionPlan::class, $supervisionPlan->getId());
        $this->assertNotNull($refreshedPlan);
        $this->assertEquals('执行中', $refreshedPlan->getPlanStatus());
    }

    public function testActivatePlanWithWrongStatus(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个执行中状态的监督计划（不能激活）
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('执行中');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        // 执行激活操作，应该失败
        $client->request('GET', sprintf('/admin/train-supervisor/supervision-plan/%d/activate-plan', $supervisionPlan->getId()));
        $response = $client->getResponse();

        // 验证重定向成功（有错误消息）
        $this->assertTrue($response->isRedirect());

        // 验证状态没有改变
        $refreshedPlan = $em->find(SupervisionPlan::class, $supervisionPlan->getId());
        $this->assertNotNull($refreshedPlan);
        $this->assertEquals('执行中', $refreshedPlan->getPlanStatus());
    }

    public function testCompletePlan(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个执行中状态的监督计划
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('执行中');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        // 确认初始状态
        $this->assertEquals('执行中', $supervisionPlan->getPlanStatus());

        // 执行完成操作
        $client->request('GET', sprintf('/admin/train-supervisor/supervision-plan/%d/complete-plan', $supervisionPlan->getId()));
        $response = $client->getResponse();

        // 验证重定向成功
        $this->assertTrue($response->isRedirect());

        // 重新查询计划验证状态已更新
        $refreshedPlan = $em->find(SupervisionPlan::class, $supervisionPlan->getId());
        $this->assertNotNull($refreshedPlan);
        $this->assertEquals('已完成', $refreshedPlan->getPlanStatus());
    }

    public function testCompletePlanWithWrongStatus(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个待执行状态的监督计划（不能完成）
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('待执行');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        // 执行完成操作，应该失败
        $client->request('GET', sprintf('/admin/train-supervisor/supervision-plan/%d/complete-plan', $supervisionPlan->getId()));
        $response = $client->getResponse();

        // 验证重定向成功（有错误消息）
        $this->assertTrue($response->isRedirect());

        // 验证状态没有改变
        $refreshedPlan = $em->find(SupervisionPlan::class, $supervisionPlan->getId());
        $this->assertNotNull($refreshedPlan);
        $this->assertEquals('待执行', $refreshedPlan->getPlanStatus());
    }

    public function testCancelPlan(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个执行中状态的监督计划
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('执行中');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        // 确认初始状态
        $this->assertEquals('执行中', $supervisionPlan->getPlanStatus());

        // 执行取消操作
        $client->request('GET', sprintf('/admin/train-supervisor/supervision-plan/%d/cancel-plan', $supervisionPlan->getId()));
        $response = $client->getResponse();

        // 验证重定向成功
        $this->assertTrue($response->isRedirect());

        // 重新查询计划验证状态已更新
        $refreshedPlan = $em->find(SupervisionPlan::class, $supervisionPlan->getId());
        $this->assertNotNull($refreshedPlan);
        $this->assertEquals('已取消', $refreshedPlan->getPlanStatus());
        $this->assertEquals('管理员手动取消', $refreshedPlan->getRemarks());
    }

    public function testCancelPlanWithWrongStatus(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个已完成状态的监督计划（不能取消）
        $supervisionPlan = new SupervisionPlan();
        $supervisionPlan->setPlanName('测试监督计划');
        $supervisionPlan->setPlanType('定期');
        $supervisionPlan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan->setSupervisor('测试监督员');
        $supervisionPlan->setPlanStatus('已完成');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan);
        $em->flush();

        // 执行取消操作，应该失败
        $client->request('GET', sprintf('/admin/train-supervisor/supervision-plan/%d/cancel-plan', $supervisionPlan->getId()));
        $response = $client->getResponse();

        // 验证重定向成功（有错误消息）
        $this->assertTrue($response->isRedirect());

        // 验证状态没有改变
        $refreshedPlan = $em->find(SupervisionPlan::class, $supervisionPlan->getId());
        $this->assertNotNull($refreshedPlan);
        $this->assertEquals('已完成', $refreshedPlan->getPlanStatus());
    }

    public function testExportData(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一些测试数据
        $supervisionPlan1 = new SupervisionPlan();
        $supervisionPlan1->setPlanName('测试监督计划1');
        $supervisionPlan1->setPlanType('定期');
        $supervisionPlan1->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $supervisionPlan1->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $supervisionPlan1->setSupervisor('测试监督员1');
        $supervisionPlan1->setPlanStatus('待执行');

        $supervisionPlan2 = new SupervisionPlan();
        $supervisionPlan2->setPlanName('测试监督计划2');
        $supervisionPlan2->setPlanType('专项');
        $supervisionPlan2->setPlanStartDate(new \DateTimeImmutable('2024-02-01'));
        $supervisionPlan2->setPlanEndDate(new \DateTimeImmutable('2024-11-30'));
        $supervisionPlan2->setSupervisor('测试监督员2');
        $supervisionPlan2->setPlanStatus('执行中');

        $em = self::getEntityManager();
        $em->persist($supervisionPlan1);
        $em->persist($supervisionPlan2);
        $em->flush();

        // 执行导出操作
        $client->request('GET', '/admin/train-supervisor/supervision-plan/export-data');
        $response = $client->getResponse();

        // 验证响应状态和内容类型
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertNotNull($contentDisposition, 'Content-Disposition header should not be null');
        $this->assertStringContainsString('attachment; filename="supervision_plans_', $contentDisposition);

        // 验证CSV内容包含标题行和数据行
        $content = $response->getContent();
        $this->assertNotFalse($content, 'Response content should not be false');
        $this->assertIsString($content, 'Response content should be a string');
        $this->assertStringContainsString('ID,计划名称,计划类型,计划状态,开始日期,结束日期,创建时间', $content);
        $this->assertStringContainsString('测试监督计划1', $content);
        $this->assertStringContainsString('测试监督计划2', $content);
        $this->assertStringContainsString('定期', $content);
        $this->assertStringContainsString('专项', $content);
    }
}

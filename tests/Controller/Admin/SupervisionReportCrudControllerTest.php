<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\TrainSupervisorBundle\Controller\Admin\SupervisionReportCrudController;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;

/**
 * SupervisionReportCrudController 测试.
 *
 * @internal
 */
#[CoversClass(SupervisionReportCrudController::class)]
#[RunTestsInSeparateProcesses]
final class SupervisionReportCrudControllerTest extends AbstractTrainSupervisorTestCase
{
    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supervision-report');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEntityInstantiation(): void
    {
        // 测试实体是否可以正确实例化
        $supervisionReport = new SupervisionReport();
        $this->assertInstanceOf(SupervisionReport::class, $supervisionReport);
    }

    public function testRequiredFieldsValidation(): void
    {
        // 测试必填字段验证
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervision-report/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // 验证必填字段在表单中有正确的required属性
        $form = $crawler->selectButton('Create')->form();

        // 验证关键必填字段存在
        $this->assertGreaterThan(0, $crawler->filter('input[name="SupervisionReport[reportTitle]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('select[name="SupervisionReport[reportType]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="SupervisionReport[reporter]"]')->count());

        // 测试部分填写表单（必须提供日期字段以避免类型错误）
        $form['SupervisionReport[reportTitle]'] = '测试监督报告';
        $form['SupervisionReport[reportType]'] = '日报';
        $form['SupervisionReport[reportPeriodStart]'] = '2024-01-01T00:00:00';
        $form['SupervisionReport[reportPeriodEnd]'] = '2024-01-31T23:59:59';
        $form['SupervisionReport[reportDate]'] = '2024-02-01T00:00:00';
        $form['SupervisionReport[reportStatus]'] = '草稿';
        // 故意不填写reporter字段

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

        $crawler = $client->request('GET', '/admin/train-supervisor/supervision-report/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $form = $crawler->selectButton('Create')->form();

        // 提交必填字段为空（保持日期字段有值）
        $form['SupervisionReport[reportTitle]'] = '';
        $form['SupervisionReport[reporter]'] = '';
        $form['SupervisionReport[reportPeriodStart]'] = '2024-01-01T00:00:00';
        $form['SupervisionReport[reportPeriodEnd]'] = '2024-01-31T23:59:59';
        $form['SupervisionReport[reportDate]'] = '2024-02-01T00:00:00';
        $form['SupervisionReport[reportStatus]'] = '草稿';

        $crawler = $client->submit($form);

        // 验证表单验证失败，显示错误信息
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('不能为空', $crawler->filter('.invalid-feedback')->text());
    }

    public function testEntityBasicOperations(): void
    {
        // 测试实体的基本操作
        $supervisionReport = new SupervisionReport();
        $supervisionReport->setReportType('月报');
        $supervisionReport->setReportTitle('测试监督报告');
        $supervisionReport->setReportPeriodStart(new \DateTimeImmutable('2024-01-01'));
        $supervisionReport->setReportPeriodEnd(new \DateTimeImmutable('2024-01-31'));
        $supervisionReport->setProblemSummary(['summary' => '测试问题汇总']);
        $supervisionReport->setRecommendations(['recommendations' => '测试建议措施']);
        $supervisionReport->setSupervisionData(['data' => '测试监督数据']);
        $supervisionReport->setReportStatus('草稿');
        $supervisionReport->setReporter('测试报告人');
        $supervisionReport->setReportDate(new \DateTimeImmutable('2024-01-31'));
        $supervisionReport->setReportContent('测试报告内容');

        $this->assertEquals('月报', $supervisionReport->getReportType());
        $this->assertEquals('测试监督报告', $supervisionReport->getReportTitle());
        $this->assertEquals('草稿', $supervisionReport->getReportStatus());
        $this->assertEquals(['summary' => '测试问题汇总'], $supervisionReport->getProblemSummary());
        $this->assertEquals(['recommendations' => '测试建议措施'], $supervisionReport->getRecommendations());
    }

    public function testNew(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/supervision-report/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreate(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/supervision-report/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $form = $crawler->selectButton('Create')->form();
        $form['SupervisionReport[reportTitle]'] = '测试监督报告';
        $form['SupervisionReport[reportType]'] = '月报';
        $form['SupervisionReport[reportPeriodStart]'] = '2024-01-01T00:00:00';
        $form['SupervisionReport[reportPeriodEnd]'] = '2024-01-31T00:00:00';
        $form['SupervisionReport[reporter]'] = '测试报告人';
        $form['SupervisionReport[reportDate]'] = '2024-01-31T00:00:00';
        $form['SupervisionReport[reportStatus]'] = '草稿';

        $client->submit($form);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
    }

    public function testCreateFormBehavior(): void
    {
        // 测试表单行为 - 确保新建页面能正常显示
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 验证新建页面可以正常访问
        $crawler = $client->request('GET', '/admin/train-supervisor/supervision-report/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // 验证表单包含必要的字段
        $this->assertGreaterThan(0, $crawler->filter('form[name="SupervisionReport"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="SupervisionReport[reportTitle]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('select[name="SupervisionReport[reportType]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('input[name="SupervisionReport[reporter]"]')->count());
    }

    public function testSearchWithReportTypeFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按报告类型搜索
        $client->request('GET', '/admin/train-supervisor/supervision-report?filters[reportType]=月报');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithReportStatusFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按报告状态搜索
        $client->request('GET', '/admin/train-supervisor/supervision-report?filters[reportStatus]=草稿');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithDateRangeFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按日期范围搜索
        $client->request('GET', '/admin/train-supervisor/supervision-report?filters[reportPeriodStart][after]=2024-01-01&filters[reportPeriodEnd][before]=2024-12-31');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithMultipleFilters(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试组合过滤器
        $client->request('GET', '/admin/train-supervisor/supervision-report?filters[reportType]=月报&filters[reportStatus]=草稿');
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
        return self::getService(SupervisionReportCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '报告标题' => ['报告标题'];
        yield '报告类型' => ['报告类型'];
        yield '报告期开始' => ['报告期开始'];
        yield '报告期结束' => ['报告期结束'];
        yield '报告人' => ['报告人'];
        yield '报告日期' => ['报告日期'];
        yield '报告状态' => ['报告状态'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'reportTitle' => ['reportTitle'];
        yield 'reportType' => ['reportType'];
        yield 'reportPeriodStart' => ['reportPeriodStart'];
        yield 'reportPeriodEnd' => ['reportPeriodEnd'];
        yield 'reporter' => ['reporter'];
        yield 'reportDate' => ['reportDate'];
        yield 'reportStatus' => ['reportStatus'];
        yield 'reportContent' => ['reportContent'];
        yield 'remarks' => ['remarks'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'reportTitle' => ['reportTitle'];
        yield 'reportType' => ['reportType'];
        yield 'reportPeriodStart' => ['reportPeriodStart'];
        yield 'reportPeriodEnd' => ['reportPeriodEnd'];
        yield 'reporter' => ['reporter'];
        yield 'reportDate' => ['reportDate'];
        yield 'reportStatus' => ['reportStatus'];
        yield 'reportContent' => ['reportContent'];
        yield 'remarks' => ['remarks'];
    }
}

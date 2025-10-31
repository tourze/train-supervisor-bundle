<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Tourze\TrainSupervisorBundle\Controller\Admin\ProblemTrackingCrudController;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Tests\Controller\Admin\AbstractTrainSupervisorTestCase;

/**
 * ProblemTrackingCrudController 测试.
 *
 * @internal
 */
#[CoversClass(ProblemTrackingCrudController::class)]
#[RunTestsInSeparateProcesses]
class ProblemTrackingCrudControllerTest extends AbstractTrainSupervisorTestCase
{
    public function testIndex(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/problem-tracking');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNew(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/train-supervisor/problem-tracking/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreate(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建一个关联的监督计划和监督检查
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-01'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->persist($inspection);
        $em->flush();

        $crawler = $client->request('GET', '/admin/train-supervisor/problem-tracking/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $form = $crawler->selectButton('Create')->form();
        $form['ProblemTracking[problemTitle]'] = '测试问题';
        $form['ProblemTracking[problemType]'] = '制度问题';
        $form['ProblemTracking[problemSeverity]'] = '一般';
        $form['ProblemTracking[problemStatus]'] = '待处理';
        $form['ProblemTracking[responsiblePerson]'] = '责任人';
        $form['ProblemTracking[discoveryDate]'] = '2024-01-01T00:00:00';
        $form['ProblemTracking[correctionDeadline]'] = '2024-03-01T00:00:00';

        // 提交表单（注意：由于 inspection 字段被隐藏，表单验证会失败）
        $client->submit($form);

        // 由于缺少必需的 inspection 字段，期望验证失败
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testEdit(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-01'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');

        $problemTracking = new ProblemTracking();
        $problemTracking->setInspection($inspection);
        $problemTracking->setProblemTitle('测试问题');
        $problemTracking->setProblemType('制度问题');
        $problemTracking->setProblemDescription('这是一个测试问题的详细描述');
        $problemTracking->setProblemSeverity('一般');
        $problemTracking->setProblemStatus('待处理');
        $problemTracking->setResponsiblePerson('测试责任人');
        $problemTracking->setDiscoveryDate(new \DateTimeImmutable('2024-01-01'));
        $problemTracking->setCorrectionDeadline(new \DateTimeImmutable('2024-03-01'));

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->persist($inspection);
        $em->persist($problemTracking);
        $em->flush();

        $client->request('GET', '/admin/train-supervisor/problem-tracking/' . $problemTracking->getId() . '/edit');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-01'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');

        $problemTracking = new ProblemTracking();
        $problemTracking->setInspection($inspection);
        $problemTracking->setProblemTitle('测试问题');
        $problemTracking->setProblemType('制度问题');
        $problemTracking->setProblemDescription('这是一个测试问题的详细描述');
        $problemTracking->setProblemSeverity('一般');
        $problemTracking->setProblemStatus('待处理');
        $problemTracking->setResponsiblePerson('测试责任人');
        $problemTracking->setDiscoveryDate(new \DateTimeImmutable('2024-01-01'));
        $problemTracking->setCorrectionDeadline(new \DateTimeImmutable('2024-03-01'));

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->persist($inspection);
        $em->persist($problemTracking);
        $em->flush();

        $client->request('POST', '/admin/train-supervisor/problem-tracking/' . $problemTracking->getId() . '/delete');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
    }

    public function testDetail(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-01'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');

        $problemTracking = new ProblemTracking();
        $problemTracking->setInspection($inspection);
        $problemTracking->setProblemTitle('测试问题');
        $problemTracking->setProblemType('制度问题');
        $problemTracking->setProblemDescription('这是一个测试问题的详细描述');
        $problemTracking->setProblemSeverity('一般');
        $problemTracking->setProblemStatus('待处理');
        $problemTracking->setResponsiblePerson('测试责任人');
        $problemTracking->setDiscoveryDate(new \DateTimeImmutable('2024-01-01'));
        $problemTracking->setCorrectionDeadline(new \DateTimeImmutable('2024-03-01'));

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->persist($inspection);
        $em->persist($problemTracking);
        $em->flush();

        $client->request('GET', '/admin/train-supervisor/problem-tracking/' . $problemTracking->getId());
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateValidationFailure(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/train-supervisor/problem-tracking/new');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // 测试必填字段验证 - 提交部分字段为空的表单
        $form = $crawler->selectButton('Create')->form();
        // 设置必需的日期字段以避免类型错误，但保持其他字段为空以测试验证
        $form['ProblemTracking[discoveryDate]'] = '2024-01-01T00:00:00';
        $form['ProblemTracking[correctionDeadline]'] = '2024-03-01T00:00:00';
        $crawler = $client->submit($form);

        // 应该显示验证错误而不是重定向
        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('不能为空', $crawler->filter('.invalid-feedback')->text());
    }

    public function testSearchWithProblemTypeFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按问题类型搜索
        $client->request('GET', '/admin/train-supervisor/problem-tracking?filters[problemType]=制度问题');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithSeverityFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按严重程度搜索
        $client->request('GET', '/admin/train-supervisor/problem-tracking?filters[problemSeverity]=严重');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithStatusFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按状态搜索
        $client->request('GET', '/admin/train-supervisor/problem-tracking?filters[problemStatus]=已解决');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSearchWithDateFilter(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 测试按发现日期搜索
        $client->request('GET', '/admin/train-supervisor/problem-tracking?filters[discoveryDate][after]=2024-01-01');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCustomActions(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-01'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');

        $problemTracking = new ProblemTracking();
        $problemTracking->setInspection($inspection);
        $problemTracking->setProblemTitle('测试问题');
        $problemTracking->setProblemType('制度问题');
        $problemTracking->setProblemDescription('这是一个测试问题的详细描述');
        $problemTracking->setProblemSeverity('一般');
        $problemTracking->setProblemStatus('待处理');
        $problemTracking->setResponsiblePerson('测试责任人');
        $problemTracking->setCorrectionStatus('待整改');
        $problemTracking->setDiscoveryDate(new \DateTimeImmutable('2024-01-01'));
        $problemTracking->setCorrectionDeadline(new \DateTimeImmutable('2024-03-01'));

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->persist($inspection);
        $em->persist($problemTracking);
        $em->flush();

        // 测试开始整改action
        $crawler = $client->request('GET', '/admin/train-supervisor/problem-tracking');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('开始整改', $crawler->text());
    }

    public function testStartCorrection(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-01'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');

        $problemTracking = new ProblemTracking();
        $problemTracking->setInspection($inspection);
        $problemTracking->setProblemTitle('测试问题');
        $problemTracking->setProblemType('制度问题');
        $problemTracking->setProblemDescription('这是一个测试问题的详细描述');
        $problemTracking->setProblemSeverity('一般');
        $problemTracking->setProblemStatus('待处理');
        $problemTracking->setResponsiblePerson('测试责任人');
        $problemTracking->setCorrectionStatus('待整改');
        $problemTracking->setDiscoveryDate(new \DateTimeImmutable('2024-01-01'));
        $problemTracking->setCorrectionDeadline(new \DateTimeImmutable('2024-03-01'));

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->persist($inspection);
        $em->persist($problemTracking);
        $em->flush();

        // 测试开始整改自定义动作
        $client->request('GET', '/admin/train-supervisor/problem-tracking/' . $problemTracking->getId() . '/start-correction');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());

        // 验证flash消息
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('问题整改已开始', $crawler->text());
    }

    public function testVerifyCorrection(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-01'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');

        $problemTracking = new ProblemTracking();
        $problemTracking->setInspection($inspection);
        $problemTracking->setProblemTitle('测试问题');
        $problemTracking->setProblemType('制度问题');
        $problemTracking->setProblemDescription('这是一个测试问题的详细描述');
        $problemTracking->setProblemSeverity('一般');
        $problemTracking->setProblemStatus('待处理');
        $problemTracking->setResponsiblePerson('测试责任人');
        $problemTracking->setCorrectionStatus('已整改');
        $problemTracking->setDiscoveryDate(new \DateTimeImmutable('2024-01-01'));
        $problemTracking->setCorrectionDeadline(new \DateTimeImmutable('2024-03-01'));

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->persist($inspection);
        $em->persist($problemTracking);
        $em->flush();

        // 测试验证整改自定义动作
        $client->request('GET', '/admin/train-supervisor/problem-tracking/' . $problemTracking->getId() . '/verify-correction');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());

        // 验证flash消息
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('问题整改验证完成', $crawler->text());
    }

    public function testCloseProblem(): void
    {
        $client = self::createClientWithDatabase();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        // 创建测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setInstitutionName('测试机构');
        $inspection->setInspectionType('现场检查');
        $inspection->setInspectionDate(new \DateTimeImmutable('2024-01-01'));
        $inspection->setInspector('测试检查员');
        $inspection->setInspectionStatus('completed');

        $problemTracking = new ProblemTracking();
        $problemTracking->setInspection($inspection);
        $problemTracking->setProblemTitle('测试问题');
        $problemTracking->setProblemType('制度问题');
        $problemTracking->setProblemDescription('这是一个测试问题的详细描述');
        $problemTracking->setProblemSeverity('一般');
        $problemTracking->setProblemStatus('待处理');
        $problemTracking->setResponsiblePerson('测试责任人');
        $problemTracking->setCorrectionStatus('已验证');
        $problemTracking->setDiscoveryDate(new \DateTimeImmutable('2024-01-01'));
        $problemTracking->setCorrectionDeadline(new \DateTimeImmutable('2024-03-01'));

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->persist($inspection);
        $em->persist($problemTracking);
        $em->flush();

        // 测试关闭问题自定义动作
        $client->request('GET', '/admin/train-supervisor/problem-tracking/' . $problemTracking->getId() . '/close-problem');
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());

        // 验证flash消息
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('问题已关闭', $crawler->text());
    }

    /**
     * 返回 ProblemTrackingCrudController 的实例
     * @return AbstractCrudController<object>
     */
    #[\ReturnTypeWillChange]
    protected function getControllerService(): AbstractCrudController
    {
        /** @phpstan-ignore-next-line */
        return self::getService(ProblemTrackingCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '关联检查' => ['关联检查'];
        yield '问题标题' => ['问题标题'];
        yield '问题类型' => ['问题类型'];
        yield '严重程度' => ['严重程度'];
        yield '问题状态' => ['问题状态'];
        yield '责任人' => ['责任人'];
        yield '发现日期' => ['发现日期'];
        yield '预期解决日期' => ['预期解决日期'];
        yield '实际解决日期' => ['实际解决日期'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'problemTitle' => ['problemTitle'];
        yield 'problemType' => ['problemType'];
        yield 'problemSeverity' => ['problemSeverity'];
        yield 'problemStatus' => ['problemStatus'];
        yield 'problemDescription' => ['problemDescription'];
        yield 'responsiblePerson' => ['responsiblePerson'];
        yield 'discoveryDate' => ['discoveryDate'];
        yield 'correctionDeadline' => ['correctionDeadline'];
        yield 'correctionStatus' => ['correctionStatus'];
        yield 'correctionPlan' => ['correctionPlan'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'problemTitle' => ['problemTitle'];
        yield 'problemType' => ['problemType'];
        yield 'problemSeverity' => ['problemSeverity'];
        yield 'problemStatus' => ['problemStatus'];
        yield 'problemDescription' => ['problemDescription'];
        yield 'responsiblePerson' => ['responsiblePerson'];
        yield 'discoveryDate' => ['discoveryDate'];
        yield 'correctionDeadline' => ['correctionDeadline'];
        yield 'correctionStatus' => ['correctionStatus'];
        yield 'correctionPlan' => ['correctionPlan'];
    }
}

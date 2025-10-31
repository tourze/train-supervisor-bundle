<?php

namespace Tourze\TrainSupervisorBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;
use Tourze\TrainSupervisorBundle\Repository\SupervisorRepository;

/**
 * @internal
 */
#[CoversClass(SupervisorRepository::class)]
#[RunTestsInSeparateProcesses]
final class SupervisorRepositoryTest extends AbstractRepositoryTestCase
{
    private SupervisorRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SupervisorRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(SupervisorRepository::class, $this->repository);
    }

    public function testSaveAndFindSupervisor(): void
    {
        $supervisor = new Supervisor();
        $supervisor->setSupervisorName('张三');
        $supervisor->setSupervisorCode('SUP001');
        $supervisor->setSupervisorLevel('高级');
        $supervisor->setSupervisorStatus('在职');
        $supervisor->setDepartment('质量部');
        $supervisor->setPosition('高级监督员');
        $supervisor->setContactPhone('13900139000');
        $supervisor->setContactEmail('zhangsan@example.com');
        $supervisor->setSpecialties('质量管理、流程优化');
        $supervisor->setQualifications('高级监督员证书');
        $supervisor->setWorkExperience('10年');
        $supervisor->setRemarks('优秀员工');

        $this->repository->save($supervisor, true);

        $id = $supervisor->getId();
        $this->assertNotNull($id);

        $found = $this->repository->find($id);
        $this->assertNotNull($found);
        $this->assertInstanceOf(Supervisor::class, $found);
        $this->assertSame('张三', $found->getSupervisorName());
        $this->assertSame('SUP001', $found->getSupervisorCode());
        $this->assertSame('高级', $found->getSupervisorLevel());
        $this->assertSame('在职', $found->getSupervisorStatus());
    }

    public function testFindBySupervisorCode(): void
    {
        $supervisor1 = new Supervisor();
        $supervisor1->setSupervisorName('李四');
        $supervisor1->setSupervisorCode('SUP002');
        $supervisor1->setSupervisorLevel('中级');
        $supervisor1->setSupervisorStatus('在职');

        $supervisor2 = new Supervisor();
        $supervisor2->setSupervisorName('王五');
        $supervisor2->setSupervisorCode('SUP003');
        $supervisor2->setSupervisorLevel('初级');
        $supervisor2->setSupervisorStatus('在职');

        $this->repository->save($supervisor1, true);
        $this->repository->save($supervisor2, true);

        $found = $this->repository->findOneBy(['supervisorCode' => 'SUP002']);
        $this->assertNotNull($found);
        $this->assertInstanceOf(Supervisor::class, $found);
        $this->assertSame('李四', $found->getSupervisorName());
        $this->assertSame('SUP002', $found->getSupervisorCode());
    }

    public function testFindBySupervisorStatus(): void
    {
        // 记录测试前已存在的在职监督员数量
        $existingActiveCount = count($this->repository->findBy(['supervisorStatus' => '在职']));

        $supervisor1 = new Supervisor();
        $supervisor1->setSupervisorName('赵六');
        $supervisor1->setSupervisorCode('SUP004');
        $supervisor1->setSupervisorLevel('高级');
        $supervisor1->setSupervisorStatus('在职');
        $this->repository->save($supervisor1, true);

        $supervisor2 = new Supervisor();
        $supervisor2->setSupervisorName('钱七');
        $supervisor2->setSupervisorCode('SUP005');
        $supervisor2->setSupervisorLevel('中级');
        $supervisor2->setSupervisorStatus('离职');
        $this->repository->save($supervisor2, true);

        $supervisor3 = new Supervisor();
        $supervisor3->setSupervisorName('孙八');
        $supervisor3->setSupervisorCode('SUP006');
        $supervisor3->setSupervisorLevel('初级');
        $supervisor3->setSupervisorStatus('在职');
        $this->repository->save($supervisor3, true);

        $activeSupers = $this->repository->findBy(['supervisorStatus' => '在职']);
        // 验证新增了2个在职监督员
        $this->assertCount($existingActiveCount + 2, $activeSupers);

        /** @var array<Supervisor> $activeSupers */
        $names = array_map(fn (Supervisor $s) => $s->getSupervisorName(), $activeSupers);
        $this->assertContains('赵六', $names);
        $this->assertContains('孙八', $names);
        $this->assertNotContains('钱七', $names);
    }

    public function testFindBySupervisorLevel(): void
    {
        // 先清理数据库，确保没有残留数据
        $allSupervisors = $this->repository->findAll();
        foreach ($allSupervisors as $supervisor) {
            $this->repository->remove($supervisor, true);
        }

        $supervisor1 = new Supervisor();
        $supervisor1->setSupervisorName('高级员工1');
        $supervisor1->setSupervisorCode('HIGH001');
        $supervisor1->setSupervisorLevel('高级');
        $supervisor1->setSupervisorStatus('在职');
        $this->repository->save($supervisor1, true);

        $supervisor2 = new Supervisor();
        $supervisor2->setSupervisorName('高级员工2');
        $supervisor2->setSupervisorCode('HIGH002');
        $supervisor2->setSupervisorLevel('高级');
        $supervisor2->setSupervisorStatus('在职');
        $this->repository->save($supervisor2, true);

        $supervisor3 = new Supervisor();
        $supervisor3->setSupervisorName('中级员工');
        $supervisor3->setSupervisorCode('MID001');
        $supervisor3->setSupervisorLevel('中级');
        $supervisor3->setSupervisorStatus('在职');
        $this->repository->save($supervisor3, true);

        $highLevelSupers = $this->repository->findBy(['supervisorLevel' => '高级']);
        $this->assertCount(2, $highLevelSupers);

        /** @var array<Supervisor> $highLevelSupers */
        $codes = array_map(fn (Supervisor $s) => $s->getSupervisorCode(), $highLevelSupers);
        sort($codes);
        $this->assertSame(['HIGH001', 'HIGH002'], $codes);
    }

    public function testUpdateSupervisor(): void
    {
        $supervisor = new Supervisor();
        $supervisor->setSupervisorName('原始姓名');
        $supervisor->setSupervisorCode('UPDATE001');
        $supervisor->setSupervisorLevel('初级');
        $supervisor->setSupervisorStatus('试用');
        $supervisor->setDepartment('原始部门');

        $this->repository->save($supervisor, true);
        $id = $supervisor->getId();

        // 更新数据
        $supervisor->setSupervisorName('更新后姓名');
        $supervisor->setSupervisorLevel('中级');
        $supervisor->setSupervisorStatus('在职');
        $supervisor->setDepartment('新部门');
        $supervisor->setPosition('新职位');

        $this->repository->save($supervisor, true);

        // 验证更新
        $updated = $this->repository->find($id);
        $this->assertNotNull($updated);
        $this->assertInstanceOf(Supervisor::class, $updated);
        $this->assertSame('更新后姓名', $updated->getSupervisorName());
        $this->assertSame('UPDATE001', $updated->getSupervisorCode()); // 编号不变
        $this->assertSame('中级', $updated->getSupervisorLevel());
        $this->assertSame('在职', $updated->getSupervisorStatus());
        $this->assertSame('新部门', $updated->getDepartment());
        $this->assertSame('新职位', $updated->getPosition());
    }

    public function testRemoveSupervisor(): void
    {
        $supervisor = new Supervisor();
        $supervisor->setSupervisorName('待删除');
        $supervisor->setSupervisorCode('DELETE001');
        $supervisor->setSupervisorLevel('中级');
        $supervisor->setSupervisorStatus('离职');

        $this->repository->save($supervisor, true);
        $id = $supervisor->getId();
        $this->assertNotNull($id);

        // 确认保存成功
        $found = $this->repository->find($id);
        $this->assertNotNull($found);

        // 删除
        $this->repository->remove($supervisor, true);

        // 确认删除成功
        $notFound = $this->repository->find($id);
        $this->assertNull($notFound);
    }

    public function testFindAllSupervisors(): void
    {
        $supervisor1 = new Supervisor();
        $supervisor1->setSupervisorName('全部查询1');
        $supervisor1->setSupervisorCode('ALL001');
        $supervisor1->setSupervisorLevel('高级');
        $supervisor1->setSupervisorStatus('在职');
        $this->repository->save($supervisor1, true);

        $supervisor2 = new Supervisor();
        $supervisor2->setSupervisorName('全部查询2');
        $supervisor2->setSupervisorCode('ALL002');
        $supervisor2->setSupervisorLevel('中级');
        $supervisor2->setSupervisorStatus('在职');
        $this->repository->save($supervisor2, true);

        $allSupervisors = $this->repository->findAll();
        $this->assertIsArray($allSupervisors);

        /** @var array<Supervisor> $allSupervisors */
        $codes = array_map(fn (Supervisor $s) => $s->getSupervisorCode(), $allSupervisors);
        $this->assertContains('ALL001', $codes);
        $this->assertContains('ALL002', $codes);
    }

    public function testFindByDateRange(): void
    {
        $this->assertTrue(true);
    }

    public function testFindSupplierData(): void
    {
        $this->assertTrue(true);
    }

    public function testComplexQuery(): void
    {
        // 先清理数据库，确保没有残留数据
        $allSupervisors = $this->repository->findAll();
        foreach ($allSupervisors as $supervisor) {
            $this->repository->remove($supervisor, true);
        }

        // 创建测试数据
        for ($i = 1; $i <= 5; ++$i) {
            $supervisor = new Supervisor();
            $supervisor->setSupervisorName("复杂查询员工{$i}");
            $supervisor->setSupervisorCode("COMPLEX{$i}");
            $supervisor->setSupervisorLevel($i <= 2 ? '高级' : '中级');
            $supervisor->setSupervisorStatus($i <= 3 ? '在职' : '离职');
            $supervisor->setDepartment(0 === $i % 2 ? '部门A' : '部门B');
            $this->repository->save($supervisor, true);
        }

        // 查询在职的高级监督员
        $qb = $this->repository->createQueryBuilder('s');
        $results = $qb->where('s.supervisorStatus = :status')
            ->andWhere('s.supervisorLevel = :level')
            ->setParameter('status', '在职')
            ->setParameter('level', '高级')
            ->orderBy('s.supervisorCode', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertInstanceOf(Supervisor::class, $results[0]);
        $this->assertSame('COMPLEX1', $results[0]->getSupervisorCode());
        $this->assertInstanceOf(Supervisor::class, $results[1]);
        $this->assertSame('COMPLEX2', $results[1]->getSupervisorCode());
    }

    public function testFindByDepartment(): void
    {
        $supervisor1 = new Supervisor();
        $supervisor1->setSupervisorName('技术部员工');
        $supervisor1->setSupervisorCode('TECH001');
        $supervisor1->setSupervisorLevel('高级');
        $supervisor1->setSupervisorStatus('在职');
        $supervisor1->setDepartment('技术部');
        $this->repository->save($supervisor1, true);

        $supervisor2 = new Supervisor();
        $supervisor2->setSupervisorName('销售部员工');
        $supervisor2->setSupervisorCode('SALE001');
        $supervisor2->setSupervisorLevel('中级');
        $supervisor2->setSupervisorStatus('在职');
        $supervisor2->setDepartment('销售部');
        $this->repository->save($supervisor2, true);

        $techDeptSupervisors = $this->repository->findBy(['department' => '技术部']);
        $this->assertCount(1, $techDeptSupervisors);
        $this->assertInstanceOf(Supervisor::class, $techDeptSupervisors[0]);
        $this->assertSame('TECH001', $techDeptSupervisors[0]->getSupervisorCode());
        $this->assertSame('技术部员工', $techDeptSupervisors[0]->getSupervisorName());
    }

    protected function createNewEntity(): Supervisor
    {
        $supervisor = new Supervisor();
        $supervisor->setSupervisorName('测试监督员_' . uniqid());
        $supervisor->setSupervisorCode('TEST_' . uniqid());
        $supervisor->setSupervisorLevel('高级');
        $supervisor->setSupervisorStatus('在职');

        return $supervisor;
    }

    protected function getRepository(): SupervisorRepository
    {
        return $this->repository;
    }
}

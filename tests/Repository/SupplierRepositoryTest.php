<?php

namespace Tourze\TrainSupervisorBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainSupervisorBundle\Entity\Supplier;
use Tourze\TrainSupervisorBundle\Repository\SupplierRepository;

/**
 * @internal
 */
#[CoversClass(SupplierRepository::class)]
#[RunTestsInSeparateProcesses]
final class SupplierRepositoryTest extends AbstractRepositoryTestCase
{
    private SupplierRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SupplierRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(SupplierRepository::class, $this->repository);
    }

    public function testSaveAndFind(): void
    {
        $supplier = new Supplier();
        $supplier->setName('测试供应商');
        $supplier->setContact('张三');
        $supplier->setPhone('13800138000');
        $supplier->setEmail('test@supplier.com');
        $supplier->setAddress('江苏省南京市雨花台区');
        $supplier->setStatus('active');

        $this->repository->save($supplier, true);
        $id = $supplier->getId();
        $this->assertNotNull($id);

        $found = $this->repository->find($id);
        $this->assertNotNull($found);
        $this->assertSame('测试供应商', $found->getName());
        $this->assertSame('张三', $found->getContact());
        $this->assertSame('13800138000', $found->getPhone());
        $this->assertSame('test@supplier.com', $found->getEmail());
        $this->assertSame('江苏省南京市雨花台区', $found->getAddress());
        $this->assertSame('active', $found->getStatus());
    }

    public function testFindByStatus(): void
    {
        $supplier1 = new Supplier();
        $supplier1->setName('测试状态供应商1');
        $supplier1->setStatus('active');
        $this->repository->save($supplier1, true);

        $supplier2 = new Supplier();
        $supplier2->setName('测试状态供应商2');
        $supplier2->setStatus('active');
        $this->repository->save($supplier2, true);

        $supplier3 = new Supplier();
        $supplier3->setName('测试状态供应商3');
        $supplier3->setStatus('inactive');
        $this->repository->save($supplier3, true);

        // 只查找本测试创建的供应商
        $qb = $this->repository->createQueryBuilder('s');
        $activeSuppliers = $qb->where('s.status = :status')
            ->andWhere('s.name LIKE :pattern')
            ->setParameter('status', 'active')
            ->setParameter('pattern', '测试状态供应商%')
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($activeSuppliers);
        $this->assertCount(2, $activeSuppliers);

        /** @var array<int, Supplier> $activeSuppliers */
        $names = array_map(static fn (Supplier $s): string => $s->getName() ?? '', $activeSuppliers);
        sort($names);
        $this->assertSame(['测试状态供应商1', '测试状态供应商2'], $names);

        // 查找inactive状态的供应商
        $qb2 = $this->repository->createQueryBuilder('s');
        $inactiveSuppliers = $qb2->where('s.status = :status')
            ->andWhere('s.name LIKE :pattern')
            ->setParameter('status', 'inactive')
            ->setParameter('pattern', '测试状态供应商%')
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($inactiveSuppliers);
        $this->assertCount(1, $inactiveSuppliers);
        $this->assertInstanceOf(Supplier::class, $inactiveSuppliers[0]);
        $this->assertSame('测试状态供应商3', $inactiveSuppliers[0]->getName());
    }

    public function testFindByName(): void
    {
        $supplier = new Supplier();
        $supplier->setName('特殊名称供应商');
        $supplier->setStatus('active');
        $this->repository->save($supplier, true);

        $found = $this->repository->findOneBy(['name' => '特殊名称供应商']);
        $this->assertNotNull($found);
        $this->assertSame('特殊名称供应商', $found->getName());
    }

    public function testUpdate(): void
    {
        $supplier = new Supplier();
        $supplier->setName('原始名称');
        $supplier->setContact('李四');
        $supplier->setPhone('13900139000');
        $supplier->setStatus('active');

        $this->repository->save($supplier, true);
        $id = $supplier->getId();

        $supplier->setName('更新后的名称');
        $supplier->setContact('王五');
        $supplier->setPhone('13700137000');
        $supplier->setEmail('updated@supplier.com');
        $supplier->setStatus('inactive');

        $this->repository->save($supplier, true);

        $updated = $this->repository->find($id);
        $this->assertNotNull($updated);
        $this->assertSame('更新后的名称', $updated->getName());
        $this->assertSame('王五', $updated->getContact());
        $this->assertSame('13700137000', $updated->getPhone());
        $this->assertSame('updated@supplier.com', $updated->getEmail());
        $this->assertSame('inactive', $updated->getStatus());
    }

    public function testRemove(): void
    {
        $supplier = new Supplier();
        $supplier->setName('待删除供应商');
        $supplier->setStatus('active');

        $this->repository->save($supplier, true);
        $id = $supplier->getId();
        $this->assertNotNull($id);

        $found = $this->repository->find($id);
        $this->assertNotNull($found);

        $this->repository->remove($supplier, true);

        $notFound = $this->repository->find($id);
        $this->assertNull($notFound);
    }

    public function testFindAll(): void
    {
        $supplier1 = new Supplier();
        $supplier1->setName('全部查询供应商1');
        $supplier1->setStatus('active');
        $this->repository->save($supplier1, true);

        $supplier2 = new Supplier();
        $supplier2->setName('全部查询供应商2');
        $supplier2->setStatus('inactive');
        $this->repository->save($supplier2, true);

        $allSuppliers = $this->repository->findAll();
        $this->assertIsArray($allSuppliers);

        $names = array_map(fn ($s) => $s->getName(), $allSuppliers);
        $this->assertContains('全部查询供应商1', $names);
        $this->assertContains('全部查询供应商2', $names);
    }

    public function testQueryBuilder(): void
    {
        $supplier1 = new Supplier();
        $supplier1->setName('查询构建器供应商A');
        $supplier1->setEmail('a@supplier.com');
        $supplier1->setStatus('active');
        $this->repository->save($supplier1, true);

        $supplier2 = new Supplier();
        $supplier2->setName('查询构建器供应商B');
        $supplier2->setEmail('b@supplier.com');
        $supplier2->setStatus('active');
        $this->repository->save($supplier2, true);

        $qb = $this->repository->createQueryBuilder('s');
        $results = $qb->where('s.name LIKE :pattern')
            ->andWhere('s.status = :status')
            ->setParameter('pattern', '%查询构建器%')
            ->setParameter('status', 'active')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertInstanceOf(Supplier::class, $results[0]);
        $this->assertSame('查询构建器供应商A', $results[0]->getName());
        $this->assertInstanceOf(Supplier::class, $results[1]);
        $this->assertSame('查询构建器供应商B', $results[1]->getName());
    }

    protected function createNewEntity(): Supplier
    {
        $supplier = new Supplier();
        $supplier->setName('测试供应商_' . uniqid());
        $supplier->setStatus('active');

        return $supplier;
    }

    protected function getRepository(): SupplierRepository
    {
        return $this->repository;
    }
}

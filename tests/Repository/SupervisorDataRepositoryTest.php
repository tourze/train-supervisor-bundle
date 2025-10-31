<?php

namespace Tourze\TrainSupervisorBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;
use Tourze\TrainSupervisorBundle\Repository\SupervisorDataRepository;

/**
 * @internal
 */
#[CoversClass(SupervisorDataRepository::class)]
#[RunTestsInSeparateProcesses]
final class SupervisorDataRepositoryTest extends AbstractRepositoryTestCase
{
    private SupervisorDataRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SupervisorDataRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(SupervisorDataRepository::class, $this->repository);
    }

    public function testSaveAndFind(): void
    {
        // 创建并保存Supplier
        $supplier = new Supplier();
        $supplier->setName('测试供应商');
        $supplier->setStatus('active');
        $em = self::getEntityManager();
        $em->persist($supplier);
        $em->flush();

        $supervisorData = new SupervisorData();
        $date = new \DateTimeImmutable('2024-01-15');
        $supervisorData->setDate($date);
        $supervisorData->setSupplier($supplier);
        $supervisorData->setSupplierId((int) $supplier->getId());
        $supervisorData->setDailyLoginCount(500);
        $supervisorData->setDailyLearnCount(300);
        $supervisorData->setTotalClassroomCount(50);
        $supervisorData->setNewClassroomCount(5);
        $supervisorData->setDailyCheatCount(2);
        $supervisorData->setFaceDetectSuccessCount(450);
        $supervisorData->setFaceDetectFailCount(50);
        $supervisorData->setRegion('华东地区');
        $supervisorData->setProvince('江苏省');
        $supervisorData->setCity('南京市');
        $supervisorData->setAgeGroup('26-35');

        $this->repository->save($supervisorData, true);
        $id = $supervisorData->getId();
        $this->assertNotNull($id);

        $found = $this->repository->find($id);
        $this->assertNotNull($found);
        $foundDate = $found->getDate();
        $this->assertNotNull($foundDate);
        $this->assertSame($date->format('Y-m-d'), $foundDate->format('Y-m-d'));
        $this->assertSame((int) $supplier->getId(), $found->getSupplierId());
        $this->assertSame(500, $found->getDailyLoginCount());
        $this->assertSame(300, $found->getDailyLearnCount());
        $this->assertSame(50, $found->getTotalClassroomCount());
        $this->assertSame(5, $found->getNewClassroomCount());
        $this->assertSame(2, $found->getDailyCheatCount());
        $this->assertSame(450, $found->getFaceDetectSuccessCount());
        $this->assertSame(50, $found->getFaceDetectFailCount());
        $this->assertSame('华东地区', $found->getRegion());
        $this->assertSame('江苏省', $found->getProvince());
        $this->assertSame('南京市', $found->getCity());
        $this->assertSame('26-35', $found->getAgeGroup());
    }

    public function testFindBySupplierId(): void
    {
        // 创建并保存Supplier
        $supplier1 = new Supplier();
        $supplier1->setName('测试供应商1');
        $supplier1->setStatus('active');
        $supplier2 = new Supplier();
        $supplier2->setName('测试供应商2');
        $supplier2->setStatus('active');
        $em = self::getEntityManager();
        $em->persist($supplier1);
        $em->persist($supplier2);
        $em->flush();

        $supervisorData1 = new SupervisorData();
        $supervisorData1->setDate(new \DateTimeImmutable('2024-01-01'));
        $supervisorData1->setSupplier($supplier1);
        $supervisorData1->setSupplierId((int) $supplier1->getId());
        $supervisorData1->setDailyLoginCount(100);
        $this->repository->save($supervisorData1, true);

        $supervisorData2 = new SupervisorData();
        $supervisorData2->setDate(new \DateTimeImmutable('2024-01-02'));
        $supervisorData2->setSupplier($supplier1);
        $supervisorData2->setSupplierId((int) $supplier1->getId());
        $supervisorData2->setDailyLoginCount(150);
        $this->repository->save($supervisorData2, true);

        $supervisorData3 = new SupervisorData();
        $supervisorData3->setDate(new \DateTimeImmutable('2024-01-03'));
        $supervisorData3->setSupplier($supplier2);
        $supervisorData3->setSupplierId((int) $supplier2->getId());
        $supervisorData3->setDailyLoginCount(200);
        $this->repository->save($supervisorData3, true);

        $results = $this->repository->findBy(['supplierId' => $supplier1->getId()]);
        $this->assertCount(2, $results);

        $loginCounts = array_map(fn ($data) => $data->getDailyLoginCount(), $results);
        sort($loginCounts);
        $this->assertSame([100, 150], $loginCounts);
    }

    public function testFindByDateRange(): void
    {
        // 创建并保存Supplier
        $supplier = new Supplier();
        $supplier->setName('测试供应商DateRange_' . uniqid());
        $supplier->setStatus('active');
        $em = self::getEntityManager();
        $em->persist($supplier);
        $em->flush();

        $date1 = new \DateTimeImmutable('2024-03-10');
        $date2 = new \DateTimeImmutable('2024-03-15');
        $date3 = new \DateTimeImmutable('2024-03-20');

        $supervisorData1 = new SupervisorData();
        $supervisorData1->setDate($date1);
        $supervisorData1->setSupplier($supplier);
        $supervisorData1->setSupplierId((int) $supplier->getId());
        $supervisorData1->setDailyLoginCount(100);
        $this->repository->save($supervisorData1, true);

        $supervisorData2 = new SupervisorData();
        $supervisorData2->setDate($date2);
        $supervisorData2->setSupplier($supplier);
        $supervisorData2->setSupplierId((int) $supplier->getId());
        $supervisorData2->setDailyLoginCount(200);
        $this->repository->save($supervisorData2, true);

        $supervisorData3 = new SupervisorData();
        $supervisorData3->setDate($date3);
        $supervisorData3->setSupplier($supplier);
        $supervisorData3->setSupplierId((int) $supplier->getId());
        $supervisorData3->setDailyLoginCount(300);
        $this->repository->save($supervisorData3, true);

        $qb = $this->repository->createQueryBuilder('sd');
        $results = $qb->where('sd.date >= :startDate')
            ->andWhere('sd.date <= :endDate')
            ->andWhere('sd.supplierId = :supplierId')
            ->setParameter('startDate', $date1->format('Y-m-d'))
            ->setParameter('endDate', $date2->format('Y-m-d'))
            ->setParameter('supplierId', $supplier->getId())
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        /** @var array<int, SupervisorData> $results */
        $loginCounts = array_map(static fn (SupervisorData $data): int => $data->getDailyLoginCount(), $results);
        sort($loginCounts);
        $this->assertSame([100, 200], $loginCounts);
    }

    public function testRemove(): void
    {
        // 创建并保存Supplier
        $supplier = new Supplier();
        $supplier->setName('测试供应商Remove');
        $supplier->setStatus('active');
        $em = self::getEntityManager();
        $em->persist($supplier);
        $em->flush();

        $supervisorData = new SupervisorData();
        $supervisorData->setDate(new \DateTimeImmutable('2024-01-01'));
        $supervisorData->setSupplier($supplier);
        $supervisorData->setSupplierId((int) $supplier->getId());
        $supervisorData->setDailyLoginCount(100);

        $this->repository->save($supervisorData, true);
        $id = $supervisorData->getId();
        $this->assertNotNull($id);

        $found = $this->repository->find($id);
        $this->assertNotNull($found);

        $this->repository->remove($supervisorData, true);

        $notFound = $this->repository->find($id);
        $this->assertNull($notFound);
    }

    public function testFindOneBySupplierIdAndDate(): void
    {
        // 创建并保存Supplier
        $supplier = new Supplier();
        $supplier->setName('测试供应商FindOne');
        $supplier->setStatus('active');
        $em = self::getEntityManager();
        $em->persist($supplier);
        $em->flush();

        $date = new \DateTimeImmutable('2024-03-01');
        $supplierId = (int) $supplier->getId();

        $supervisorData = new SupervisorData();
        $supervisorData->setDate($date);
        $supervisorData->setSupplier($supplier);
        $supervisorData->setSupplierId($supplierId);
        $supervisorData->setDailyLoginCount(250);

        $this->repository->save($supervisorData, true);

        $found = $this->repository->findOneBy([
            'supplierId' => $supplierId,
            'date' => $date,
        ]);

        $this->assertNotNull($found);
        $this->assertSame(250, $found->getDailyLoginCount());
        $this->assertSame($supplierId, $found->getSupplierId());
        $foundDate = $found->getDate();
        $this->assertNotNull($foundDate);
        $this->assertSame($date->format('Y-m-d'), $foundDate->format('Y-m-d'));
    }

    protected function createNewEntity(): SupervisorData
    {
        // 创建并保存Supplier
        $supplier = new Supplier();
        $supplier->setName('测试供应商_' . uniqid());
        $supplier->setStatus('active');
        $em = self::getEntityManager();
        $em->persist($supplier);
        $em->flush();

        $supervisorData = new SupervisorData();
        $supervisorData->setDate(new \DateTimeImmutable());
        $supervisorData->setSupplier($supplier);
        $supervisorData->setSupplierId((int) $supplier->getId());
        $supervisorData->setDailyLoginCount(100);

        return $supervisorData;
    }

    protected function getRepository(): SupervisorDataRepository
    {
        return $this->repository;
    }
}

<?php

namespace Tourze\TrainSupervisorBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;

class SupervisorDataFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 创建测试供应商
        $supplier1 = new Supplier();
        $supplier1->setName('测试供应商1');
        $supplier1->setStatus('active');
        $manager->persist($supplier1);

        $supplier2 = new Supplier();
        $supplier2->setName('测试供应商2');
        $supplier2->setStatus('active');
        $manager->persist($supplier2);

        $manager->flush(); // 先保存供应商以获取ID

        $supervisorData1 = new SupervisorData();
        $supervisorData1->setDate(new \DateTimeImmutable('2024-01-01'));
        $supervisorData1->setSupplier($supplier1);
        $supervisorData1->setSupplierId((int) $supplier1->getId());
        $supervisorData1->setDailyLoginCount(500);
        $supervisorData1->setDailyLearnCount(300);
        $supervisorData1->setTotalClassroomCount(50);
        $supervisorData1->setNewClassroomCount(5);
        $supervisorData1->setDailyCheatCount(2);
        $supervisorData1->setFaceDetectSuccessCount(450);
        $supervisorData1->setFaceDetectFailCount(50);
        $supervisorData1->setRegion('华东地区');
        $supervisorData1->setProvince('江苏省');
        $supervisorData1->setCity('南京市');
        $supervisorData1->setAgeGroup('26-35');
        $manager->persist($supervisorData1);

        $supervisorData2 = new SupervisorData();
        $supervisorData2->setDate(new \DateTimeImmutable('2024-01-02'));
        $supervisorData2->setSupplier($supplier1);
        $supervisorData2->setSupplierId((int) $supplier1->getId());
        $supervisorData2->setDailyLoginCount(550);
        $supervisorData2->setDailyLearnCount(320);
        $supervisorData2->setTotalClassroomCount(52);
        $supervisorData2->setNewClassroomCount(2);
        $supervisorData2->setDailyCheatCount(1);
        $supervisorData2->setFaceDetectSuccessCount(490);
        $supervisorData2->setFaceDetectFailCount(60);
        $supervisorData2->setRegion('华东地区');
        $supervisorData2->setProvince('江苏省');
        $supervisorData2->setCity('南京市');
        $supervisorData2->setAgeGroup('26-35');
        $manager->persist($supervisorData2);

        $supervisorData3 = new SupervisorData();
        $supervisorData3->setDate(new \DateTimeImmutable('2024-01-03'));
        $supervisorData3->setSupplier($supplier2);
        $supervisorData3->setSupplierId((int) $supplier2->getId());
        $supervisorData3->setDailyLoginCount(400);
        $supervisorData3->setDailyLearnCount(250);
        $supervisorData3->setTotalClassroomCount(40);
        $supervisorData3->setNewClassroomCount(3);
        $supervisorData3->setDailyCheatCount(0);
        $supervisorData3->setFaceDetectSuccessCount(380);
        $supervisorData3->setFaceDetectFailCount(20);
        $supervisorData3->setRegion('华北地区');
        $supervisorData3->setProvince('北京市');
        $supervisorData3->setCity('北京市');
        $supervisorData3->setAgeGroup('18-25');
        $manager->persist($supervisorData3);

        $manager->flush();
    }
}

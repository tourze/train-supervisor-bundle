<?php

namespace Tourze\TrainSupervisorBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\TrainSupervisorBundle\Entity\Supplier;

class SupplierFixtures extends Fixture
{
    public const SUPPLIER_1_REFERENCE = 'supplier-1';
    public const SUPPLIER_2_REFERENCE = 'supplier-2';
    public const SUPPLIER_3_REFERENCE = 'supplier-3';

    public function load(ObjectManager $manager): void
    {
        $supplier1 = new Supplier();
        $supplier1->setName('培训供应商A');
        $supplier1->setContact('张经理');
        $supplier1->setPhone('13800138001');
        $supplier1->setEmail('contact@supplier-a.com');
        $supplier1->setAddress('江苏省南京市雨花台区软件大道1号');
        $supplier1->setStatus('active');
        $manager->persist($supplier1);
        $this->addReference(self::SUPPLIER_1_REFERENCE, $supplier1);

        $supplier2 = new Supplier();
        $supplier2->setName('培训供应商B');
        $supplier2->setContact('李经理');
        $supplier2->setPhone('13900139002');
        $supplier2->setEmail('contact@supplier-b.com');
        $supplier2->setAddress('北京市海淀区中关村大街2号');
        $supplier2->setStatus('active');
        $manager->persist($supplier2);
        $this->addReference(self::SUPPLIER_2_REFERENCE, $supplier2);

        $supplier3 = new Supplier();
        $supplier3->setName('培训供应商C');
        $supplier3->setContact('王经理');
        $supplier3->setPhone('13700137003');
        $supplier3->setEmail('contact@supplier-c.com');
        $supplier3->setAddress('上海市浦东新区张江高科技园区3号');
        $supplier3->setStatus('inactive');
        $manager->persist($supplier3);
        $this->addReference(self::SUPPLIER_3_REFERENCE, $supplier3);

        $manager->flush();
    }
}

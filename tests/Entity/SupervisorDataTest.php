<?php

namespace Tourze\TrainSupervisorBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;

/**
 * @internal
 */
#[CoversClass(SupervisorData::class)]
class SupervisorDataTest extends AbstractEntityTestCase
{
    private SupervisorData $supervisorData;

    protected function setUp(): void
    {
        $this->supervisorData = new SupervisorData();
    }

    protected function createEntity(): object
    {
        return new SupervisorData();
    }

    public static function propertiesProvider(): iterable
    {
        return [
            'dailyLoginCount' => ['dailyLoginCount', 123],
            'dailyLearnCount' => ['dailyLearnCount', 123],
            'totalClassroomCount' => ['totalClassroomCount', 123],
            'newClassroomCount' => ['newClassroomCount', 123],
            'dailyCheatCount' => ['dailyCheatCount', 123],
            'faceDetectSuccessCount' => ['faceDetectSuccessCount', 123],
            'faceDetectFailCount' => ['faceDetectFailCount', 123],
        ];
    }

    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(SupervisorData::class, $this->supervisorData);
    }

    public function testDateGetterAndSetter(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');
        $this->supervisorData->setDate($date);
        $this->assertSame($date, $this->supervisorData->getDate());
    }

    public function testSupplierIdGetterAndSetter(): void
    {
        $supplierId = 123;
        $this->supervisorData->setSupplierId($supplierId);
        $this->assertSame($supplierId, $this->supervisorData->getSupplierId());
    }

    public function testDailyLoginCountGetterAndSetter(): void
    {
        $count = 100;
        $this->supervisorData->setDailyLoginCount($count);
        $this->assertSame($count, $this->supervisorData->getDailyLoginCount());
    }

    public function testDailyLearnCountGetterAndSetter(): void
    {
        $count = 50;
        $this->supervisorData->setDailyLearnCount($count);
        $this->assertSame($count, $this->supervisorData->getDailyLearnCount());
    }

    public function testTotalClassroomCountGetterAndSetter(): void
    {
        $count = 20;
        $this->supervisorData->setTotalClassroomCount($count);
        $this->assertSame($count, $this->supervisorData->getTotalClassroomCount());
    }

    public function testNewClassroomCountGetterAndSetter(): void
    {
        $count = 5;
        $this->supervisorData->setNewClassroomCount($count);
        $this->assertSame($count, $this->supervisorData->getNewClassroomCount());
    }

    public function testDailyCheatCountGetterAndSetter(): void
    {
        $count = 3;
        $this->supervisorData->setDailyCheatCount($count);
        $this->assertSame($count, $this->supervisorData->getDailyCheatCount());
    }

    public function testFaceDetectSuccessCountGetterAndSetter(): void
    {
        $count = 95;
        $this->supervisorData->setFaceDetectSuccessCount($count);
        $this->assertSame($count, $this->supervisorData->getFaceDetectSuccessCount());
    }

    public function testFaceDetectFailCountGetterAndSetter(): void
    {
        $count = 5;
        $this->supervisorData->setFaceDetectFailCount($count);
        $this->assertSame($count, $this->supervisorData->getFaceDetectFailCount());
    }

    public function testRegionGetterAndSetter(): void
    {
        $region = '华东地区';
        $this->supervisorData->setRegion($region);
        $this->assertSame($region, $this->supervisorData->getRegion());
    }

    public function testProvinceGetterAndSetter(): void
    {
        $province = '江苏省';
        $this->supervisorData->setProvince($province);
        $this->assertSame($province, $this->supervisorData->getProvince());
    }

    public function testCityGetterAndSetter(): void
    {
        $city = '南京市';
        $this->supervisorData->setCity($city);
        $this->assertSame($city, $this->supervisorData->getCity());
    }

    public function testAgeGroupGetterAndSetter(): void
    {
        $ageGroup = '18-25';
        $this->supervisorData->setAgeGroup($ageGroup);
        $this->assertSame($ageGroup, $this->supervisorData->getAgeGroup());
    }

    public function testSupplierGetterAndSetter(): void
    {
        $supplier = new Supplier();
        $this->supervisorData->setSupplier($supplier);
        $this->assertSame($supplier, $this->supervisorData->getSupplier());
    }

    public function testToString(): void
    {
        $date = new \DateTimeImmutable('2024-01-15');
        $this->supervisorData->setDate($date);
        $expected = 'SupervisorData #new (2024-01-15)';
        $this->assertSame($expected, (string) $this->supervisorData);
    }

    public function testToStringWithoutDate(): void
    {
        $expected = 'SupervisorData #new (no date)';
        $this->assertSame($expected, (string) $this->supervisorData);
    }

    public function testSettersAndGetters(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');

        $this->supervisorData->setDate($date);
        $this->supervisorData->setSupplierId(123);
        $this->supervisorData->setDailyLoginCount(100);
        $this->supervisorData->setDailyLearnCount(50);
        $this->supervisorData->setTotalClassroomCount(20);
        $this->supervisorData->setNewClassroomCount(5);
        $this->supervisorData->setDailyCheatCount(3);
        $this->supervisorData->setFaceDetectSuccessCount(95);
        $this->supervisorData->setFaceDetectFailCount(5);
        $this->supervisorData->setRegion('华东地区');
        $this->supervisorData->setProvince('江苏省');
        $this->supervisorData->setCity('南京市');
        $this->supervisorData->setAgeGroup('18-25');

        $this->assertSame($date, $this->supervisorData->getDate());
        $this->assertSame(123, $this->supervisorData->getSupplierId());
        $this->assertSame(100, $this->supervisorData->getDailyLoginCount());
        $this->assertSame(50, $this->supervisorData->getDailyLearnCount());
    }
}

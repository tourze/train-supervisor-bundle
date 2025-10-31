<?php

namespace Tourze\TrainSupervisorBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainSupervisorBundle\Entity\Supplier;

/**
 * @internal
 */
#[CoversClass(Supplier::class)]
class SupplierTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Supplier();
    }

    public static function propertiesProvider(): iterable
    {
        return [
            'status' => ['status', 'test_value'],
        ];
    }

    private Supplier $supplier;

    protected function setUp(): void
    {
        $this->supplier = new Supplier();
    }

    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(Supplier::class, $this->supplier);
    }

    public function testNameGetterAndSetter(): void
    {
        $name = '测试供应商';
        $this->supplier->setName($name);
        $this->assertSame($name, $this->supplier->getName());
    }

    public function testContactGetterAndSetter(): void
    {
        $contact = '张三';
        $this->supplier->setContact($contact);
        $this->assertSame($contact, $this->supplier->getContact());
    }

    public function testPhoneGetterAndSetter(): void
    {
        $phone = '13800138000';
        $this->supplier->setPhone($phone);
        $this->assertSame($phone, $this->supplier->getPhone());
    }

    public function testEmailGetterAndSetter(): void
    {
        $email = 'test@supplier.com';
        $this->supplier->setEmail($email);
        $this->assertSame($email, $this->supplier->getEmail());
    }

    public function testAddressGetterAndSetter(): void
    {
        $address = '江苏省南京市雨花台区软件大道1号';
        $this->supplier->setAddress($address);
        $this->assertSame($address, $this->supplier->getAddress());
    }

    public function testStatusGetterAndSetter(): void
    {
        $status = 'inactive';
        $this->supplier->setStatus($status);
        $this->assertSame($status, $this->supplier->getStatus());
    }

    public function testDefaultStatusIsActive(): void
    {
        $this->assertSame('active', $this->supplier->getStatus());
    }

    public function testToString(): void
    {
        $name = '测试供应商';
        $this->supplier->setName($name);
        $this->assertSame($name, (string) $this->supplier);
    }

    public function testToStringWithoutName(): void
    {
        $this->assertSame('', (string) $this->supplier);
    }

    public function testSettersAndGetters(): void
    {
        $this->supplier->setName('测试供应商');
        $this->supplier->setContact('张三');
        $this->supplier->setPhone('13800138000');
        $this->supplier->setEmail('test@supplier.com');
        $this->supplier->setAddress('江苏省南京市');
        $this->supplier->setStatus('active');

        $this->assertSame('测试供应商', $this->supplier->getName());
        $this->assertSame('张三', $this->supplier->getContact());
        $this->assertSame('13800138000', $this->supplier->getPhone());
        $this->assertSame('test@supplier.com', $this->supplier->getEmail());
    }

    public function testNullableFields(): void
    {
        $this->supplier->setContact(null);
        $this->supplier->setPhone(null);
        $this->supplier->setEmail(null);
        $this->supplier->setAddress(null);

        $this->assertNull($this->supplier->getContact());
        $this->assertNull($this->supplier->getPhone());
        $this->assertNull($this->supplier->getEmail());
        $this->assertNull($this->supplier->getAddress());
    }
}

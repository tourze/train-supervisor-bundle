<?php

namespace Tourze\TrainSupervisorBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;

/**
 * @internal
 */
#[CoversClass(Supervisor::class)]
final class SupervisorTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Supervisor();
    }

    public static function propertiesProvider(): iterable
    {
        return [
            'createTime' => ['createTime', new \DateTimeImmutable()],
            'updateTime' => ['updateTime', new \DateTimeImmutable()],
        ];
    }

    private Supervisor $supervisor;

    protected function setUp(): void
    {
        $this->supervisor = new Supervisor();
    }

    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(Supervisor::class, $this->supervisor);
    }

    public function testSupervisorNameGetterAndSetter(): void
    {
        $name = '张三';
        $this->supervisor->setSupervisorName($name);
        $this->assertSame($name, $this->supervisor->getSupervisorName());
    }

    public function testSupervisorCodeGetterAndSetter(): void
    {
        $code = 'SUP001';
        $this->supervisor->setSupervisorCode($code);
        $this->assertSame($code, $this->supervisor->getSupervisorCode());
    }

    public function testDepartmentGetterAndSetter(): void
    {
        $department = '质量监督部';
        $this->supervisor->setDepartment($department);
        $this->assertSame($department, $this->supervisor->getDepartment());
    }

    public function testPositionGetterAndSetter(): void
    {
        $position = '高级监督员';
        $this->supervisor->setPosition($position);
        $this->assertSame($position, $this->supervisor->getPosition());
    }

    public function testContactPhoneGetterAndSetter(): void
    {
        $phone = '13800138000';
        $this->supervisor->setContactPhone($phone);
        $this->assertSame($phone, $this->supervisor->getContactPhone());
    }

    public function testContactEmailGetterAndSetter(): void
    {
        $email = 'supervisor@example.com';
        $this->supervisor->setContactEmail($email);
        $this->assertSame($email, $this->supervisor->getContactEmail());
    }

    public function testSupervisorLevelGetterAndSetter(): void
    {
        $level = '高级';
        $this->supervisor->setSupervisorLevel($level);
        $this->assertSame($level, $this->supervisor->getSupervisorLevel());
    }

    public function testSupervisorStatusGetterAndSetter(): void
    {
        $status = '在职';
        $this->supervisor->setSupervisorStatus($status);
        $this->assertSame($status, $this->supervisor->getSupervisorStatus());
    }

    public function testSpecialtiesGetterAndSetter(): void
    {
        $specialties = '培训监督、质量管理、流程优化';
        $this->supervisor->setSpecialties($specialties);
        $this->assertSame($specialties, $this->supervisor->getSpecialties());
    }

    public function testQualificationsGetterAndSetter(): void
    {
        $qualifications = '国家级监督员证书、ISO9001内审员';
        $this->supervisor->setQualifications($qualifications);
        $this->assertSame($qualifications, $this->supervisor->getQualifications());
    }

    public function testWorkExperienceGetterAndSetter(): void
    {
        $experience = '10年培训行业监督管理经验';
        $this->supervisor->setWorkExperience($experience);
        $this->assertSame($experience, $this->supervisor->getWorkExperience());
    }

    public function testRemarksGetterAndSetter(): void
    {
        $remarks = '优秀员工，表现突出';
        $this->supervisor->setRemarks($remarks);
        $this->assertSame($remarks, $this->supervisor->getRemarks());
    }

    public function testToString(): void
    {
        $name = '李四';
        $this->supervisor->setSupervisorName($name);
        $this->assertSame($name, (string) $this->supervisor);
    }

    public function testToStringWithoutName(): void
    {
        $this->assertSame('', (string) $this->supervisor);
    }

    public function testSettersAndGetters(): void
    {
        $this->supervisor->setSupervisorName('王五');
        $this->supervisor->setSupervisorCode('SUP002');
        $this->supervisor->setDepartment('培训部');
        $this->supervisor->setPosition('监督专员');
        $this->supervisor->setContactPhone('13900139000');
        $this->supervisor->setContactEmail('wangwu@example.com');
        $this->supervisor->setSupervisorLevel('中级');
        $this->supervisor->setSupervisorStatus('在职');
        $this->supervisor->setSpecialties('培训质量监督');
        $this->supervisor->setQualifications('中级监督员证书');
        $this->supervisor->setWorkExperience('5年经验');
        $this->supervisor->setRemarks('工作认真负责');

        $this->assertSame('王五', $this->supervisor->getSupervisorName());
        $this->assertSame('SUP002', $this->supervisor->getSupervisorCode());
        $this->assertSame('培训部', $this->supervisor->getDepartment());
        $this->assertSame('监督专员', $this->supervisor->getPosition());
    }

    public function testNullableFields(): void
    {
        $this->supervisor->setDepartment(null);
        $this->supervisor->setPosition(null);
        $this->supervisor->setContactPhone(null);
        $this->supervisor->setContactEmail(null);
        $this->supervisor->setSpecialties(null);
        $this->supervisor->setQualifications(null);
        $this->supervisor->setWorkExperience(null);
        $this->supervisor->setRemarks(null);

        $this->assertNull($this->supervisor->getDepartment());
        $this->assertNull($this->supervisor->getPosition());
        $this->assertNull($this->supervisor->getContactPhone());
        $this->assertNull($this->supervisor->getContactEmail());
        $this->assertNull($this->supervisor->getSpecialties());
        $this->assertNull($this->supervisor->getQualifications());
        $this->assertNull($this->supervisor->getWorkExperience());
        $this->assertNull($this->supervisor->getRemarks());
    }

    public function testRequiredFields(): void
    {
        $this->supervisor->setSupervisorName('赵六');
        $this->supervisor->setSupervisorCode('SUP003');
        $this->supervisor->setSupervisorLevel('初级');
        $this->supervisor->setSupervisorStatus('试用');

        $this->assertSame('赵六', $this->supervisor->getSupervisorName());
        $this->assertSame('SUP003', $this->supervisor->getSupervisorCode());
        $this->assertSame('初级', $this->supervisor->getSupervisorLevel());
        $this->assertSame('试用', $this->supervisor->getSupervisorStatus());
    }

    public function testCompleteProfile(): void
    {
        $this->supervisor->setSupervisorName('完整档案测试');
        $this->supervisor->setSupervisorCode('SUP999');
        $this->supervisor->setDepartment('综合管理部');
        $this->supervisor->setPosition('首席监督官');
        $this->supervisor->setContactPhone('18888888888');
        $this->supervisor->setContactEmail('chief@company.com');
        $this->supervisor->setSupervisorLevel('特级');
        $this->supervisor->setSupervisorStatus('在职');
        $this->supervisor->setSpecialties('全面质量管理、流程优化、团队建设');
        $this->supervisor->setQualifications('高级监督员证书、PMP认证、六西格玛黑带');
        $this->supervisor->setWorkExperience('20年行业经验，曾任多家大型企业质量总监');
        $this->supervisor->setRemarks('公司核心管理人员，负责全公司质量体系建设');

        $this->assertSame('完整档案测试', $this->supervisor->getSupervisorName());
        $this->assertSame('SUP999', $this->supervisor->getSupervisorCode());
        $this->assertSame('综合管理部', $this->supervisor->getDepartment());
        $this->assertSame('首席监督官', $this->supervisor->getPosition());
        $this->assertSame('18888888888', $this->supervisor->getContactPhone());
        $this->assertSame('chief@company.com', $this->supervisor->getContactEmail());
        $this->assertSame('特级', $this->supervisor->getSupervisorLevel());
        $this->assertSame('在职', $this->supervisor->getSupervisorStatus());
        $specialties = $this->supervisor->getSpecialties();
        $this->assertNotNull($specialties);
        $this->assertStringContainsString('全面质量管理', $specialties);

        $qualifications = $this->supervisor->getQualifications();
        $this->assertNotNull($qualifications);
        $this->assertStringContainsString('PMP认证', $qualifications);

        $workExperience = $this->supervisor->getWorkExperience();
        $this->assertNotNull($workExperience);
        $this->assertStringContainsString('20年行业经验', $workExperience);

        $remarks = $this->supervisor->getRemarks();
        $this->assertNotNull($remarks);
        $this->assertStringContainsString('核心管理人员', $remarks);
    }
}

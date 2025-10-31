<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\SupervisorRepository;

#[ORM\Entity(repositoryClass: SupervisorRepository::class)]
#[ORM\Table(name: 'train_supervisor', options: ['comment' => '监督员'])]
class Supervisor implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[Assert\NotBlank(message: '监督员姓名不能为空')]
    #[Assert\Length(max: 100, maxMessage: '监督员姓名长度不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '监督员姓名'])]
    private ?string $supervisorName = null;

    #[Assert\NotBlank(message: '监督员编号不能为空')]
    #[Assert\Length(max: 50, maxMessage: '监督员编号长度不能超过50个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, unique: true, options: ['comment' => '监督员编号'])]
    private ?string $supervisorCode = null;

    #[Assert\Length(max: 100, maxMessage: '所属部门长度不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '所属部门'])]
    private ?string $department = null;

    #[Assert\Length(max: 100, maxMessage: '职位长度不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '职位'])]
    private ?string $position = null;

    #[Assert\Length(max: 20, maxMessage: '联系电话长度不能超过20个字符')]
    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '联系电话'])]
    private ?string $contactPhone = null;

    #[Assert\Email(message: '邮箱格式不正确')]
    #[Assert\Length(max: 255, maxMessage: '联系邮箱长度不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '联系邮箱'])]
    private ?string $contactEmail = null;

    #[Assert\NotBlank(message: '监督员级别不能为空')]
    #[Assert\Length(max: 20, maxMessage: '监督员级别长度不能超过20个字符')]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '监督员级别'])]
    private ?string $supervisorLevel = null;

    #[Assert\NotBlank(message: '状态不能为空')]
    #[Assert\Length(max: 20, maxMessage: '状态长度不能超过20个字符')]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '状态'])]
    private ?string $supervisorStatus = null;

    #[Assert\Length(max: 65535, maxMessage: '专业领域长度不能超过65535个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '专业领域'])]
    private ?string $specialties = null;

    #[Assert\Length(max: 65535, maxMessage: '资质证书长度不能超过65535个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '资质证书'])]
    private ?string $qualifications = null;

    #[Assert\Length(max: 65535, maxMessage: '工作经历长度不能超过65535个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '工作经历'])]
    private ?string $workExperience = null;

    #[Assert\Length(max: 65535, maxMessage: '备注长度不能超过65535个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remarks = null;

    public function getSupervisorName(): ?string
    {
        return $this->supervisorName;
    }

    public function setSupervisorName(?string $supervisorName): void
    {
        $this->supervisorName = $supervisorName;
    }

    public function getSupervisorCode(): ?string
    {
        return $this->supervisorCode;
    }

    public function setSupervisorCode(?string $supervisorCode): void
    {
        $this->supervisorCode = $supervisorCode;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): void
    {
        $this->department = $department;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): void
    {
        $this->contactPhone = $contactPhone;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getSupervisorLevel(): ?string
    {
        return $this->supervisorLevel;
    }

    public function setSupervisorLevel(?string $supervisorLevel): void
    {
        $this->supervisorLevel = $supervisorLevel;
    }

    public function getSupervisorStatus(): ?string
    {
        return $this->supervisorStatus;
    }

    public function setSupervisorStatus(?string $supervisorStatus): void
    {
        $this->supervisorStatus = $supervisorStatus;
    }

    public function getSpecialties(): ?string
    {
        return $this->specialties;
    }

    public function setSpecialties(?string $specialties): void
    {
        $this->specialties = $specialties;
    }

    public function getQualifications(): ?string
    {
        return $this->qualifications;
    }

    public function setQualifications(?string $qualifications): void
    {
        $this->qualifications = $qualifications;
    }

    public function getWorkExperience(): ?string
    {
        return $this->workExperience;
    }

    public function setWorkExperience(?string $workExperience): void
    {
        $this->workExperience = $workExperience;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): void
    {
        $this->remarks = $remarks;
    }

    public function __toString(): string
    {
        return $this->supervisorName ?? (string) $this->id;
    }
}

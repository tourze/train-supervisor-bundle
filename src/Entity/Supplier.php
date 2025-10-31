<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\SupplierRepository;

#[ORM\Entity(repositoryClass: SupplierRepository::class)]
#[ORM\Table(name: 'train_supplier', options: ['comment' => '供应商'])]
class Supplier implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[Assert\NotBlank(message: '供应商名称不能为空')]
    #[Assert\Length(max: 255, maxMessage: '供应商名称长度不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '供应商名称'])]
    private ?string $name = null;

    #[Assert\Length(max: 255, maxMessage: '联系人长度不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '联系人'])]
    private ?string $contact = null;

    #[Assert\Length(max: 20, maxMessage: '联系电话长度不能超过20个字符')]
    #[ORM\Column(type: Types::STRING, length: 20, nullable: true, options: ['comment' => '联系电话'])]
    private ?string $phone = null;

    #[Assert\Email(message: '邮箱格式不正确')]
    #[Assert\Length(max: 255, maxMessage: '联系邮箱长度不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '联系邮箱'])]
    private ?string $email = null;

    #[Assert\Length(max: 65535, maxMessage: '地址长度不能超过65535个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '地址'])]
    private ?string $address = null;

    #[Assert\NotBlank(message: '状态不能为空')]
    #[Assert\Length(max: 20, maxMessage: '状态长度不能超过20个字符')]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '状态', 'default' => 'active'])]
    private string $status = 'active';

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): void
    {
        $this->contact = $contact;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function __toString(): string
    {
        return $this->name ?? (string) $this->id;
    }
}

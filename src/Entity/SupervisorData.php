<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\SupervisorDataRepository;

#[ORM\Entity(repositoryClass: SupervisorDataRepository::class)]
#[ORM\Table(name: 'train_supervisor_data', options: ['comment' => '监督员统计数据'])]
class SupervisorData implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(name: 'supplier_id', referencedColumnName: 'id')]
    private ?Supplier $supplier = null;

    #[Assert\NotNull(message: '日期不能为空')]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '日期'])]
    private ?\DateTimeImmutable $date = null;

    #[Assert\NotNull(message: '供应商ID不能为空')]
    #[Assert\PositiveOrZero(message: '供应商ID必须大于等于0')]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '供应商ID', 'default' => 0])]
    private ?int $supplierId = null;

    #[Assert\PositiveOrZero(message: '日登录人数必须大于等于0')]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '日登录人数', 'default' => 0])]
    private int $dailyLoginCount = 0;

    #[Assert\PositiveOrZero(message: '日学习人数必须大于等于0')]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '日学习人数', 'default' => 0])]
    private int $dailyLearnCount = 0;

    #[Assert\PositiveOrZero(message: '总班级数必须大于等于0')]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '总班级数', 'default' => 0])]
    private int $totalClassroomCount = 0;

    #[Assert\PositiveOrZero(message: '新增班级数必须大于等于0')]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '新增班级数', 'default' => 0])]
    private int $newClassroomCount = 0;

    #[Assert\Length(max: 100, maxMessage: '地区长度不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '地区'])]
    private ?string $region = null;

    #[Assert\Length(max: 100, maxMessage: '省份长度不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '省份'])]
    private ?string $province = null;

    #[Assert\Length(max: 100, maxMessage: '城市长度不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '城市'])]
    private ?string $city = null;

    #[Assert\Length(max: 50, maxMessage: '年龄段长度不能超过50个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '年龄段'])]
    private ?string $ageGroup = null;

    #[Assert\PositiveOrZero(message: '日作弊人数必须大于等于0')]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '日作弊人数', 'default' => 0])]
    private int $dailyCheatCount = 0;

    #[Assert\PositiveOrZero(message: '人脸检测成功次数必须大于等于0')]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '人脸检测成功次数', 'default' => 0])]
    private int $faceDetectSuccessCount = 0;

    #[Assert\PositiveOrZero(message: '人脸检测失败次数必须大于等于0')]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '人脸检测失败次数', 'default' => 0])]
    private int $faceDetectFailCount = 0;

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getSupplierId(): ?int
    {
        return $this->supplierId;
    }

    public function setSupplierId(?int $supplierId): void
    {
        $this->supplierId = $supplierId;
    }

    public function getDailyLoginCount(): int
    {
        return $this->dailyLoginCount;
    }

    public function setDailyLoginCount(int $dailyLoginCount): void
    {
        $this->dailyLoginCount = $dailyLoginCount;
    }

    public function getDailyLearnCount(): int
    {
        return $this->dailyLearnCount;
    }

    public function setDailyLearnCount(int $dailyLearnCount): void
    {
        $this->dailyLearnCount = $dailyLearnCount;
    }

    public function getTotalClassroomCount(): int
    {
        return $this->totalClassroomCount;
    }

    public function setTotalClassroomCount(int $totalClassroomCount): void
    {
        $this->totalClassroomCount = $totalClassroomCount;
    }

    public function getNewClassroomCount(): int
    {
        return $this->newClassroomCount;
    }

    public function setNewClassroomCount(int $newClassroomCount): void
    {
        $this->newClassroomCount = $newClassroomCount;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): void
    {
        $this->region = $region;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): void
    {
        $this->province = $province;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getAgeGroup(): ?string
    {
        return $this->ageGroup;
    }

    public function setAgeGroup(?string $ageGroup): void
    {
        $this->ageGroup = $ageGroup;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): void
    {
        $this->supplier = $supplier;
        if (null !== $supplier) {
            $this->supplierId = (int) $supplier->getId();
        }
    }

    public function getDailyCheatCount(): int
    {
        return $this->dailyCheatCount;
    }

    public function setDailyCheatCount(int $dailyCheatCount): void
    {
        $this->dailyCheatCount = $dailyCheatCount;
    }

    public function getFaceDetectSuccessCount(): int
    {
        return $this->faceDetectSuccessCount;
    }

    public function setFaceDetectSuccessCount(int $faceDetectSuccessCount): void
    {
        $this->faceDetectSuccessCount = $faceDetectSuccessCount;
    }

    public function getFaceDetectFailCount(): int
    {
        return $this->faceDetectFailCount;
    }

    public function setFaceDetectFailCount(int $faceDetectFailCount): void
    {
        $this->faceDetectFailCount = $faceDetectFailCount;
    }

    public function __toString(): string
    {
        return sprintf('SupervisorData #%s (%s)', $this->id ?? 'new', $this->date?->format('Y-m-d') ?? 'no date');
    }
}

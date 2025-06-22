<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainCourseBundle\Trait\SupplierAware;
use Tourze\TrainSupervisorBundle\Repository\SupervisorRepository;

#[ORM\Entity(repositoryClass: SupervisorRepository::class)]
#[ORM\Table(name: 'job_training_supervisor', options: ['comment' => '监管明细'])]
#[ORM\UniqueConstraint(name: 'job_training_supervisor_idx_uniq', columns: ['supplier_id', 'date'])]
class Supervisor implements Stringable
{
    use TimestampableAware;
    use SupplierAware;

    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '日期'])]
    private \DateTimeInterface $date;

    #[ORM\Column(options: ['comment' => '总开班数'])]
    private int $totalClassroomCount = 0;

    #[ORM\Column(options: ['comment' => '新开班数'])]
    private int $newClassroomCount = 0;

    #[ORM\Column(options: ['comment' => '登录人数'])]
    private int $dailyLoginCount = 0;

    #[ORM\Column(options: ['comment' => '学习人数'])]
    private int $dailyLearnCount = 0;

    #[ORM\Column(options: ['comment' => '作弊次数'])]
    private int $dailyCheatCount = 0;

    #[ORM\Column(options: ['comment' => '人脸识别成功次数'])]
    private int $faceDetectSuccessCount = 0;

    #[ORM\Column(options: ['comment' => '人脸识别失败次数'])]
    private int $faceDetectFailCount = 0;

    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTotalClassroomCount(): int
    {
        return $this->totalClassroomCount;
    }

    public function setTotalClassroomCount(int $totalClassroomCount): static
    {
        $this->totalClassroomCount = $totalClassroomCount;

        return $this;
    }

    public function getNewClassroomCount(): int
    {
        return $this->newClassroomCount;
    }

    public function setNewClassroomCount(int $newClassroomCount): static
    {
        $this->newClassroomCount = $newClassroomCount;

        return $this;
    }

    public function getDailyLearnCount(): int
    {
        return $this->dailyLearnCount;
    }

    public function setDailyLearnCount(int $dailyLearnCount): static
    {
        $this->dailyLearnCount = $dailyLearnCount;

        return $this;
    }

    public function getDailyCheatCount(): int
    {
        return $this->dailyCheatCount;
    }

    public function setDailyCheatCount(int $dailyCheatCount): static
    {
        $this->dailyCheatCount = $dailyCheatCount;

        return $this;
    }

    public function getDailyLoginCount(): int
    {
        return $this->dailyLoginCount;
    }

    public function setDailyLoginCount(int $dailyLoginCount): static
    {
        $this->dailyLoginCount = $dailyLoginCount;

        return $this;
    }

    public function getFaceDetectSuccessCount(): ?int
    {
        return $this->faceDetectSuccessCount;
    }

    public function setFaceDetectSuccessCount(int $faceDetectSuccessCount): static
    {
        $this->faceDetectSuccessCount = $faceDetectSuccessCount;

        return $this;
    }

    public function getFaceDetectFailCount(): int
    {
        return $this->faceDetectFailCount;
    }

    public function setFaceDetectFailCount(int $faceDetectFailCount): static
    {
        $this->faceDetectFailCount = $faceDetectFailCount;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}

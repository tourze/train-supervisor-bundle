<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use AntdCpBundle\Builder\Action\ModalWebViewAction;
use AppBundle\Entity\Supplier;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SenboTrainingBundle\Entity\CreateTimeColumn;
use SenboTrainingBundle\Entity\UpdateTimeColumn;
use SenboTrainingBundle\Repository\SupervisorRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\EasyAdmin\Attribute\Action\Exportable;
use Tourze\EasyAdmin\Attribute\Action\HeaderAction;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '监管明细')]
#[Exportable]
#[ORM\Entity(repositoryClass: SupervisorRepository::class)]
#[ORM\Table(name: 'job_training_supervisor', options: ['comment' => '监管明细'])]
#[ORM\UniqueConstraint(name: 'job_training_supervisor_idx_uniq', columns: ['supplier_id', 'date'])]
class Supervisor
{
    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ExportColumn]
    #[ListColumn(title: '机构')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Supplier $supplier;

    #[ExportColumn]
    #[IndexColumn]
    #[Filterable]
    #[ListColumn]
    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '日期'])]
    private \DateTimeInterface $date;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(options: ['comment' => '总开班数'])]
    private int $totalClassroomCount = 0;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(options: ['comment' => '新开班数'])]
    private int $newClassroomCount = 0;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(options: ['comment' => '登录人数'])]
    private int $dailyLoginCount = 0;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(options: ['comment' => '学习人数'])]
    private int $dailyLearnCount = 0;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(options: ['comment' => '作弊次数'])]
    private int $dailyCheatCount = 0;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(options: ['comment' => '人脸识别成功次数'])]
    private int $faceDetectSuccessCount = 0;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[ORM\Column(options: ['comment' => '人脸识别失败次数'])]
    private int $faceDetectFailCount = 0;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSupplier(): Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
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

    #[HeaderAction(title: '整体趋势')]
    #[AsPermission(title: '整体趋势')]
    public function renderChartButton(UrlGeneratorInterface $urlGenerator): ModalWebViewAction
    {
        return ModalWebViewAction::gen()
            ->setUrl($urlGenerator->generate('job-training-super-visor-display', ['supplierId' => 0]))
            ->setLabel('整体趋势')
            ->setContainerStyle((object) [
                'height' => 1024,
                'paddingTop' => 80,
            ])
            ->setType('modal-web-view-action')
            ->setModalWidth('90%');
    }
}

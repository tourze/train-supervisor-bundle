<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;
use Tourze\TrainSupervisorBundle\Exception\SupplierNotFoundException;
use Tourze\TrainSupervisorBundle\Repository\SupervisorDataRepository;
use Tourze\TrainSupervisorBundle\Repository\SupplierRepository;

/**
 * 监督服务
 * 负责监督数据的创建、更新和统计分析.
 */
#[Autoconfigure(public: true)]
class SupervisorService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisorDataRepository $supervisorDataRepository,
        private readonly SupplierRepository $supplierRepository,
    ) {
    }

    /**
     * 创建或更新监督记录.
     *
     * @param array<string, mixed> $data
     */
    public function createOrUpdateSupervisorRecord(
        string $supplierId,
        \DateTimeInterface $date,
        array $data,
    ): SupervisorData {
        $supervisorData = $this->findOrCreateSupervisorData($supplierId, $date);
        $this->updateSupervisorDataFromArray($supervisorData, $data);

        $this->entityManager->persist($supervisorData);
        $this->entityManager->flush();

        return $supervisorData;
    }

    private function findOrCreateSupervisorData(string $supplierId, \DateTimeInterface $date): SupervisorData
    {
        $supervisorData = $this->supervisorDataRepository->findOneBy([
            'supplierId' => (int) $supplierId,
            'date' => $date,
        ]);

        if (null !== $supervisorData) {
            return $supervisorData;
        }

        $supervisorData = new SupervisorData();
        $supplier = $this->supplierRepository->find((int) $supplierId);
        if (null === $supplier) {
            throw new SupplierNotFoundException($supplierId);
        }

        $supervisorData->setSupplier($supplier);
        $supervisorData->setDate(\DateTimeImmutable::createFromInterface($date));

        return $supervisorData;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateSupervisorDataFromArray(SupervisorData $supervisorData, array $data): void
    {
        $this->setIntFieldIfPresent($data, 'total_classroom_count', static function (int $value) use ($supervisorData): void {
            $supervisorData->setTotalClassroomCount($value);
        });
        $this->setIntFieldIfPresent($data, 'new_classroom_count', static function (int $value) use ($supervisorData): void {
            $supervisorData->setNewClassroomCount($value);
        });
        $this->setIntFieldIfPresent($data, 'daily_login_count', static function (int $value) use ($supervisorData): void {
            $supervisorData->setDailyLoginCount($value);
        });
        $this->setIntFieldIfPresent($data, 'daily_learn_count', static function (int $value) use ($supervisorData): void {
            $supervisorData->setDailyLearnCount($value);
        });
        $this->setIntFieldIfPresent($data, 'daily_cheat_count', static function (int $value) use ($supervisorData): void {
            $supervisorData->setDailyCheatCount($value);
        });
        $this->setIntFieldIfPresent($data, 'face_detect_success_count', static function (int $value) use ($supervisorData): void {
            $supervisorData->setFaceDetectSuccessCount($value);
        });
        $this->setIntFieldIfPresent($data, 'face_detect_fail_count', static function (int $value) use ($supervisorData): void {
            $supervisorData->setFaceDetectFailCount($value);
        });
    }

    /**
     * @param array<string, mixed> $data
     * @param callable(int): void $setter
     */
    private function setIntFieldIfPresent(array $data, string $key, callable $setter): void
    {
        if (isset($data[$key]) && \is_int($data[$key])) {
            $setter($data[$key]);
        }
    }

    /**
     * 批量创建监督记录.
     *
     * @param array<array{supplier_id: string, date: \DateTimeInterface, data: array<string, mixed>}> $recordsData
     * @return array<SupervisorData>
     */
    public function batchCreateSupervisorRecords(array $recordsData): array
    {
        $records = [];

        foreach ($recordsData as $recordData) {
            $record = $this->createOrUpdateSupervisorRecord(
                $recordData['supplier_id'],
                $recordData['date'],
                $recordData['data']
            );
            $records[] = $record;
        }

        return $records;
    }

    /**
     * 获取机构监督数据.
     *
     * @return array<SupervisorData>
     */
    public function getSupplierSupervisorData(
        string $supplierId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
    ): array {
        return $this->supervisorDataRepository->findByDateRange($startDate, $endDate, $supplierId);
    }

    /**
     * 获取日期范围内的监督数据.
     *
     * @return array<SupervisorData>
     */
    public function getSupervisorDataByDateRange(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        ?string $supplierId = null,
    ): array {
        return $this->supervisorDataRepository->findByDateRange($startDate, $endDate, $supplierId);
    }

    /**
     * 生成监督统计报告.
     *
     * @return array<string, mixed>
     */
    public function generateSupervisorStatistics(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        ?string $supplierId = null,
    ): array {
        $data = $this->getSupervisorDataByDateRange($startDate ?? new \DateTime('-30 days'), $endDate ?? new \DateTime(), $supplierId);

        $totalRecords = count($data);
        $totalSuppliers = count(array_unique(array_map(fn ($record) => $record->getSupplierId(), $data)));
        $totalClassrooms = array_sum(array_map(fn ($record) => $record->getTotalClassroomCount(), $data));
        $totalNewClassrooms = array_sum(array_map(fn ($record) => $record->getNewClassroomCount(), $data));
        $totalLogins = array_sum(array_map(fn ($record) => $record->getDailyLoginCount(), $data));
        $totalLearners = array_sum(array_map(fn ($record) => $record->getDailyLearnCount(), $data));
        $totalCheats = array_sum(array_map(fn ($record) => $record->getDailyCheatCount(), $data));
        $totalFaceSuccessCount = array_sum(array_map(fn ($record) => $record->getFaceDetectSuccessCount(), $data));
        $totalFaceFailCount = array_sum(array_map(fn ($record) => $record->getFaceDetectFailCount(), $data));

        $cheatRate = $totalLearners > 0 ? ($totalCheats / $totalLearners) * 100 : 0;
        $faceDetectSuccessRate = ($totalFaceSuccessCount + $totalFaceFailCount) > 0 ?
            ($totalFaceSuccessCount / ($totalFaceSuccessCount + $totalFaceFailCount)) * 100 : 0;

        // 按机构分组统计
        $bySupplier = [];
        foreach ($data as $record) {
            $supplierId = $record->getSupplierId();
            if (!isset($bySupplier[$supplierId])) {
                $bySupplier[$supplierId] = [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $supplierId, // 实际项目中应该从供应商表获取名称
                    'total_classrooms' => 0,
                    'total_logins' => 0,
                    'total_learners' => 0,
                    'total_cheats' => 0,
                    'record_count' => 0,
                ];
            }

            $bySupplier[$supplierId]['total_classrooms'] += $record->getTotalClassroomCount();
            $bySupplier[$supplierId]['total_logins'] += $record->getDailyLoginCount();
            $bySupplier[$supplierId]['total_learners'] += $record->getDailyLearnCount();
            $bySupplier[$supplierId]['total_cheats'] += $record->getDailyCheatCount();
            ++$bySupplier[$supplierId]['record_count'];
        }

        // 转换为数组并排序
        $bySupplier = array_values($bySupplier);
        usort($bySupplier, fn ($a, $b) => $b['total_learners'] <=> $a['total_learners']);

        return [
            'total_records' => $totalRecords,
            'total_suppliers' => $totalSuppliers,
            'total_classrooms' => $totalClassrooms,
            'total_new_classrooms' => $totalNewClassrooms,
            'total_logins' => $totalLogins,
            'total_learners' => $totalLearners,
            'total_cheats' => $totalCheats,
            'cheat_rate' => $cheatRate,
            'face_detect_success_rate' => $faceDetectSuccessRate,
            'by_supplier' => $bySupplier,
        ];
    }

    /**
     * 获取异常监督数据.
     *
     * @return array<array{supplier_id: int|null, supplier_name: string, date: string|null, anomaly_reasons: non-empty-array<string>}>
     */
    public function getAnomalySupervisorData(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array {
        $data = $this->getSupervisorDataByDateRange($startDate ?? new \DateTime('-7 days'), $endDate ?? new \DateTime());

        $anomalies = [];

        foreach ($data as $record) {
            $anomalyReasons = $this->detectAnomalies($record);

            if ([] !== $anomalyReasons) {
                $supplierId = $record->getSupplierId();
                $anomalies[] = [
                    'supplier_id' => $supplierId,
                    'supplier_name' => (string) $supplierId, // 实际项目中应该从供应商表获取名称
                    'date' => $record->getDate()?->format('Y-m-d'),
                    'anomaly_reasons' => $anomalyReasons,
                ];
            }
        }

        return $anomalies;
    }

    /**
     * @return array<string>
     */
    private function detectAnomalies(SupervisorData $record): array
    {
        $anomalyReasons = [];

        $anomalyReasons = $this->checkCheatRateAnomaly($record, $anomalyReasons);
        $anomalyReasons = $this->checkFaceDetectionAnomaly($record, $anomalyReasons);
        $anomalyReasons = $this->checkConversionRateAnomaly($record, $anomalyReasons);

        return $this->checkClassroomCountAnomaly($record, $anomalyReasons);
    }

    /**
     * @param array<string> $anomalyReasons
     * @return array<string>
     */
    private function checkCheatRateAnomaly(SupervisorData $record, array $anomalyReasons): array
    {
        if ($record->getDailyLearnCount() > 0) {
            $cheatRate = ($record->getDailyCheatCount() / $record->getDailyLearnCount()) * 100;
            if ($cheatRate > 5) {
                $anomalyReasons[] = sprintf('作弊率异常高：%.2f%%', $cheatRate);
            }
        }

        return $anomalyReasons;
    }

    /**
     * @param array<string> $anomalyReasons
     * @return array<string>
     */
    private function checkFaceDetectionAnomaly(SupervisorData $record, array $anomalyReasons): array
    {
        $successCount = $record->getFaceDetectSuccessCount();
        $failCount = $record->getFaceDetectFailCount();
        $totalFaceDetect = $successCount + $failCount;

        if ($totalFaceDetect > 0) {
            $faceFailRate = ($failCount / $totalFaceDetect) * 100;
            if ($faceFailRate > 20) {
                $anomalyReasons[] = sprintf('人脸识别失败率异常高：%.2f%%', $faceFailRate);
            }
        }

        return $anomalyReasons;
    }

    /**
     * @param array<string> $anomalyReasons
     * @return array<string>
     */
    private function checkConversionRateAnomaly(SupervisorData $record, array $anomalyReasons): array
    {
        if ($record->getDailyLoginCount() > 0) {
            $conversionRate = ($record->getDailyLearnCount() / $record->getDailyLoginCount()) * 100;
            if ($conversionRate < 50) {
                $anomalyReasons[] = sprintf('学习转化率异常低：%.2f%%', $conversionRate);
            }
        }

        return $anomalyReasons;
    }

    /**
     * @param array<string> $anomalyReasons
     * @return array<string>
     */
    private function checkClassroomCountAnomaly(SupervisorData $record, array $anomalyReasons): array
    {
        if ($record->getTotalClassroomCount() > 0 && $record->getNewClassroomCount() > $record->getTotalClassroomCount()) {
            $anomalyReasons[] = '新开班数超过总开班数';
        }

        return $anomalyReasons;
    }

    /**
     * 生成趋势分析.
     *
     * @return list<array<string, mixed>>
     */
    public function generateTrendAnalysis(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        ?string $supplierId = null,
    ): array {
        $data = $this->getSupervisorDataByDateRange($startDate, $endDate, $supplierId);

        // 按日期分组
        $dailyData = [];
        foreach ($data as $record) {
            $dateKey = $record->getDate()?->format('Y-m-d');
            if (!isset($dailyData[$dateKey])) {
                $dailyData[$dateKey] = [
                    'date' => $dateKey,
                    'total_classrooms' => 0,
                    'total_logins' => 0,
                    'total_learners' => 0,
                    'total_cheats' => 0,
                    'record_count' => 0,
                ];
            }

            $dailyData[$dateKey]['total_classrooms'] += $record->getTotalClassroomCount();
            $dailyData[$dateKey]['total_logins'] += $record->getDailyLoginCount();
            $dailyData[$dateKey]['total_learners'] += $record->getDailyLearnCount();
            $dailyData[$dateKey]['total_cheats'] += $record->getDailyCheatCount();
            ++$dailyData[$dateKey]['record_count'];
        }

        // 排序并计算趋势
        ksort($dailyData);
        $trendData = array_values($dailyData);

        // 计算环比增长
        for ($i = 1; $i < count($trendData); ++$i) {
            $current = $trendData[$i];
            $previous = $trendData[$i - 1];

            $trendData[$i]['logins_trend'] = $this->calculateTrendPercentage($current['total_logins'] ?? 0, $previous['total_logins'] ?? 0);
            $trendData[$i]['learners_trend'] = $this->calculateTrendPercentage($current['total_learners'] ?? 0, $previous['total_learners'] ?? 0);
            $trendData[$i]['cheats_trend'] = $this->calculateTrendPercentage($current['total_cheats'] ?? 0, $previous['total_cheats'] ?? 0);
        }

        return $trendData;
    }

    /**
     * 计算趋势百分比.
     *
     * @return array{value: float|int, direction: string}
     */
    private function calculateTrendPercentage(int $current, int $previous): array
    {
        if (0 === $previous) {
            return [
                'value' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'flat',
            ];
        }

        $percentage = (($current - $previous) / $previous) * 100;

        return [
            'value' => abs($percentage),
            'direction' => $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'flat'),
        ];
    }

    /**
     * 导出监督数据.
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportSupervisorData(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        ?string $supplierId = null,
    ): array {
        $data = $this->getSupervisorDataByDateRange($startDate ?? new \DateTime('-30 days'), $endDate ?? new \DateTime(), $supplierId);

        $exportData = [];
        foreach ($data as $record) {
            $exportData[] = [
                'id' => $record->getId(),
                'supplier_id' => $record->getSupplierId(),
                'date' => $record->getDate()?->format('Y-m-d'),
                'total_classroom_count' => $record->getTotalClassroomCount(),
                'new_classroom_count' => $record->getNewClassroomCount(),
                'daily_login_count' => $record->getDailyLoginCount(),
                'daily_learn_count' => $record->getDailyLearnCount(),
                'daily_cheat_count' => $record->getDailyCheatCount(),
                'face_detect_success_count' => $record->getFaceDetectSuccessCount(),
                'face_detect_fail_count' => $record->getFaceDetectFailCount(),
                'create_time' => $record->getCreateTime()?->format('Y-m-d H:i:s'),
                'update_time' => $record->getUpdateTime()?->format('Y-m-d H:i:s'),
            ];
        }

        return $exportData;
    }

    /**
     * 删除监督记录.
     */
    public function deleteSupervisorRecord(SupervisorData $supervisor): void
    {
        $this->entityManager->remove($supervisor);
        $this->entityManager->flush();
    }

    /**
     * 批量删除监督记录.
     *
     * @param array<int|string> $supervisorIds
     */
    public function batchDeleteSupervisorRecords(array $supervisorIds): int
    {
        $qb = $this->supervisorDataRepository->createQueryBuilder('s');
        $qb->delete()
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $supervisorIds)
        ;

        $result = $qb->getQuery()->execute();

        return is_int($result) ? $result : 0;
    }
}

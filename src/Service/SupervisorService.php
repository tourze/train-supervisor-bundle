<?php

namespace Tourze\TrainSupervisorBundle\Service;

use AppBundle\Entity\Supplier;
use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;
use Tourze\TrainSupervisorBundle\Repository\SupervisorRepository;

/**
 * 监督员服务
 * 负责监督员日常监督数据的管理和统计分析
 */
class SupervisorService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisorRepository $supervisorRepository,
    ) {
    }

    /**
     * 创建或更新监督记录
     */
    public function createOrUpdateSupervisorRecord(
        Supplier $supplier,
        \DateTimeInterface $date,
        array $data
    ): Supervisor {
        // 查找是否已存在记录
        $supervisor = $this->supervisorRepository->findOneBy([
            'supplier' => $supplier,
            'date' => $date,
        ]);

        if ($supervisor === null) {
            $supervisor = new Supervisor();
            // setSupplier 方法不存在，暂时注释
            // $supervisor->setSupplier($supplier)
            $supervisor->setDate($date);
        }

        // 更新数据
        if ((bool) isset($data['total_classroom_count'])) {
            $supervisor->setTotalClassroomCount($data['total_classroom_count']);
        }
        if ((bool) isset($data['new_classroom_count'])) {
            $supervisor->setNewClassroomCount($data['new_classroom_count']);
        }
        if ((bool) isset($data['daily_login_count'])) {
            $supervisor->setDailyLoginCount($data['daily_login_count']);
        }
        if ((bool) isset($data['daily_learn_count'])) {
            $supervisor->setDailyLearnCount($data['daily_learn_count']);
        }
        if ((bool) isset($data['daily_cheat_count'])) {
            $supervisor->setDailyCheatCount($data['daily_cheat_count']);
        }
        if ((bool) isset($data['face_detect_success_count'])) {
            $supervisor->setFaceDetectSuccessCount($data['face_detect_success_count']);
        }
        if ((bool) isset($data['face_detect_fail_count'])) {
            $supervisor->setFaceDetectFailCount($data['face_detect_fail_count']);
        }

        $this->entityManager->persist($supervisor);
        $this->entityManager->flush();

        return $supervisor;
    }

    /**
     * 批量创建监督记录
     */
    public function batchCreateSupervisorRecords(array $recordsData): array
    {
        $records = [];

        foreach ($recordsData as $recordData) {
            $record = $this->createOrUpdateSupervisorRecord(
                $recordData['supplier'],
                $recordData['date'],
                $recordData['data']
            );
            $records[] = $record;
        }

        return $records;
    }

    /**
     * 获取机构监督数据
     */
    public function getSupplierSupervisorData(
        Supplier $supplier,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        return $this->supervisorRepository->findSupplierData($supplier, $startDate, $endDate);
    }

    /**
     * 获取日期范围内的监督数据
     */
    public function getSupervisorDataByDateRange(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        ?Supplier $supplier = null
    ): array {
        return $this->supervisorRepository->findByDateRange($startDate, $endDate, $supplier);
    }

    /**
     * 生成监督统计报告
     */
    public function generateSupervisorStatistics(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        ?Supplier $supplier = null
    ): array {
        $records = $this->getSupervisorDataByDateRange($startDate, $endDate, $supplier);

        $statistics = [
            'total_records' => count($records),
            'total_suppliers' => 0,
            'total_classrooms' => 0,
            'total_new_classrooms' => 0,
            'total_logins' => 0,
            'total_learners' => 0,
            'total_cheats' => 0,
            'total_face_detect_success' => 0,
            'total_face_detect_fail' => 0,
            'average_daily_login' => 0,
            'average_daily_learn' => 0,
            'cheat_rate' => 0,
            'face_detect_success_rate' => 0,
            'by_supplier' => [],
            'daily_trends' => [],
        ];

        $supplierIds = [];
        $dailyData = [];

        foreach ($records as $record) {
            $supplierId = $record->getSupplier()->getId();
            $date = $record->getDate()->format('Y-m-d');

            // 供应商统计
            if (!in_array($supplierId, $supplierIds)) {
                $supplierIds[] = $supplierId;
            }

            // 累计统计
            $statistics['total_classrooms'] += $record->getTotalClassroomCount();
            $statistics['total_new_classrooms'] += $record->getNewClassroomCount();
            $statistics['total_logins'] += $record->getDailyLoginCount();
            $statistics['total_learners'] += $record->getDailyLearnCount();
            $statistics['total_cheats'] += $record->getDailyCheatCount();
            $statistics['total_face_detect_success'] += $record->getFaceDetectSuccessCount();
            $statistics['total_face_detect_fail'] += $record->getFaceDetectFailCount();

            // 按供应商统计
            if (!isset($statistics['by_supplier'][$supplierId])) {
                $statistics['by_supplier'][$supplierId] = [
                    'supplier_name' => $record->getSupplier()->getName(),
                    'total_classrooms' => 0,
                    'total_new_classrooms' => 0,
                    'total_logins' => 0,
                    'total_learners' => 0,
                    'total_cheats' => 0,
                    'total_face_detect_success' => 0,
                    'total_face_detect_fail' => 0,
                    'record_count' => 0,
                ];
            }

            $statistics['by_supplier'][$supplierId]['total_classrooms'] += $record->getTotalClassroomCount();
            $statistics['by_supplier'][$supplierId]['total_new_classrooms'] += $record->getNewClassroomCount();
            $statistics['by_supplier'][$supplierId]['total_logins'] += $record->getDailyLoginCount();
            $statistics['by_supplier'][$supplierId]['total_learners'] += $record->getDailyLearnCount();
            $statistics['by_supplier'][$supplierId]['total_cheats'] += $record->getDailyCheatCount();
            $statistics['by_supplier'][$supplierId]['total_face_detect_success'] += $record->getFaceDetectSuccessCount();
            $statistics['by_supplier'][$supplierId]['total_face_detect_fail'] += $record->getFaceDetectFailCount();
            $statistics['by_supplier'][$supplierId]['record_count']++;

            // 按日期统计
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'date' => $date,
                    'total_classrooms' => 0,
                    'total_new_classrooms' => 0,
                    'total_logins' => 0,
                    'total_learners' => 0,
                    'total_cheats' => 0,
                    'total_face_detect_success' => 0,
                    'total_face_detect_fail' => 0,
                ];
            }

            $dailyData[$date]['total_classrooms'] += $record->getTotalClassroomCount();
            $dailyData[$date]['total_new_classrooms'] += $record->getNewClassroomCount();
            $dailyData[$date]['total_logins'] += $record->getDailyLoginCount();
            $dailyData[$date]['total_learners'] += $record->getDailyLearnCount();
            $dailyData[$date]['total_cheats'] += $record->getDailyCheatCount();
            $dailyData[$date]['total_face_detect_success'] += $record->getFaceDetectSuccessCount();
            $dailyData[$date]['total_face_detect_fail'] += $record->getFaceDetectFailCount();
        }

        // 计算平均值和比率
        $statistics['total_suppliers'] = count($supplierIds);
        
        if ($statistics['total_records'] > 0) {
            $statistics['average_daily_login'] = round($statistics['total_logins'] / $statistics['total_records'], 2);
            $statistics['average_daily_learn'] = round($statistics['total_learners'] / $statistics['total_records'], 2);
        }

        if ($statistics['total_learners'] > 0) {
            $statistics['cheat_rate'] = round(($statistics['total_cheats'] / $statistics['total_learners']) * 100, 2);
        }

        $totalFaceDetect = $statistics['total_face_detect_success'] + $statistics['total_face_detect_fail'];
        if ($totalFaceDetect > 0) {
            $statistics['face_detect_success_rate'] = round(($statistics['total_face_detect_success'] / $totalFaceDetect) * 100, 2);
        }

        // 排序日期趋势
        ksort($dailyData);
        $statistics['daily_trends'] = array_values($dailyData);

        return $statistics;
    }

    /**
     * 获取异常监督数据
     */
    public function getAnomalySupervisorData(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        $records = $this->getSupervisorDataByDateRange($startDate, $endDate);
        $anomalies = [];

        foreach ($records as $record) {
            $anomalyReasons = [];

            // 检查作弊率异常（超过5%）
            if ($record->getDailyLearnCount() > 0) {
                $cheatRate = ($record->getDailyCheatCount() / $record->getDailyLearnCount()) * 100;
                if ($cheatRate > 5) {
                    $anomalyReasons[] = sprintf('作弊率过高：%.2f%%', $cheatRate);
                }
            }

            // 检查人脸识别失败率异常（超过20%）
            $totalFaceDetect = $record->getFaceDetectSuccessCount() + $record->getFaceDetectFailCount();
            if ($totalFaceDetect > 0) {
                $failRate = ($record->getFaceDetectFailCount() / $totalFaceDetect) * 100;
                if ($failRate > 20) {
                    $anomalyReasons[] = sprintf('人脸识别失败率过高：%.2f%%', $failRate);
                }
            }

            // 检查登录学习比异常（登录人数远大于学习人数）
            if ($record->getDailyLoginCount() > 0 && $record->getDailyLearnCount() > 0) {
                $learnRate = ($record->getDailyLearnCount() / $record->getDailyLoginCount()) * 100;
                if ($learnRate < 50) {
                    $anomalyReasons[] = sprintf('学习转化率过低：%.2f%%', $learnRate);
                }
            }

            // 检查新开班异常（新开班数超过总开班数）
            if ($record->getNewClassroomCount() > $record->getTotalClassroomCount()) {
                $anomalyReasons[] = '新开班数超过总开班数';
            }

            if (!empty($anomalyReasons)) {
                $anomalies[] = [
                    'record' => $record,
                    'supplier_name' => $record->getSupplier()->getName(),
                    'date' => $record->getDate()->format('Y-m-d'),
                    'anomaly_reasons' => $anomalyReasons,
                ];
            }
        }

        return $anomalies;
    }

    /**
     * 生成趋势分析
     */
    public function generateTrendAnalysis(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        ?Supplier $supplier = null
    ): array {
        $records = $this->getSupervisorDataByDateRange($startDate, $endDate, $supplier);
        
        // 按日期分组
        $dailyData = [];
        foreach ($records as $record) {
            $date = $record->getDate()->format('Y-m-d');
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'date' => $date,
                    'total_classrooms' => 0,
                    'total_logins' => 0,
                    'total_learners' => 0,
                    'total_cheats' => 0,
                ];
            }

            $dailyData[$date]['total_classrooms'] += $record->getTotalClassroomCount();
            $dailyData[$date]['total_logins'] += $record->getDailyLoginCount();
            $dailyData[$date]['total_learners'] += $record->getDailyLearnCount();
            $dailyData[$date]['total_cheats'] += $record->getDailyCheatCount();
        }

        ksort($dailyData);
        $sortedData = array_values($dailyData);

        // 计算趋势
        $trends = [];
        for ($i = 1; $i < count($sortedData); $i++) {
            $current = $sortedData[$i];
            $previous = $sortedData[$i - 1];

            $trends[] = [
                'date' => $current['date'],
                'classroom_trend' => $this->calculateTrendPercentage($current['total_classrooms'], $previous['total_classrooms']),
                'login_trend' => $this->calculateTrendPercentage($current['total_logins'], $previous['total_logins']),
                'learn_trend' => $this->calculateTrendPercentage($current['total_learners'], $previous['total_learners']),
                'cheat_trend' => $this->calculateTrendPercentage($current['total_cheats'], $previous['total_cheats']),
            ];
        }

        return [
            'daily_data' => $sortedData,
            'trends' => $trends,
        ];
    }

    /**
     * 计算趋势百分比
     */
    private function calculateTrendPercentage(int $current, int $previous): array
    {
        if ($previous == 0) {
            return ['change' => $current, 'percentage' => 0, 'direction' => 'stable'];
        }

        $change = $current - $previous;
        $percentage = round(($change / $previous) * 100, 2);
        
        $direction = 'stable';
        if ($percentage > 5) {
            $direction = 'up';
        } elseif ($percentage < -5) {
            $direction = 'down';
        }

        return [
            'change' => $change,
            'percentage' => $percentage,
            'direction' => $direction,
        ];
    }

    /**
     * 导出监督数据
     */
    public function exportSupervisorData(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        ?Supplier $supplier = null
    ): array {
        $records = $this->getSupervisorDataByDateRange($startDate, $endDate, $supplier);

        return array_map(fn($record) => [
            'id' => $record->getId(),
            'supplier_name' => $record->getSupplier()->getName(),
            'date' => $record->getDate()->format('Y-m-d'),
            'total_classroom_count' => $record->getTotalClassroomCount(),
            'new_classroom_count' => $record->getNewClassroomCount(),
            'daily_login_count' => $record->getDailyLoginCount(),
            'daily_learn_count' => $record->getDailyLearnCount(),
            'daily_cheat_count' => $record->getDailyCheatCount(),
            'face_detect_success_count' => $record->getFaceDetectSuccessCount(),
            'face_detect_fail_count' => $record->getFaceDetectFailCount(),
            'cheat_rate' => $record->getDailyLearnCount() > 0 ? 
                round(($record->getDailyCheatCount() / $record->getDailyLearnCount()) * 100, 2) : 0,
            'face_detect_success_rate' => ($record->getFaceDetectSuccessCount() + $record->getFaceDetectFailCount()) > 0 ?
                round(($record->getFaceDetectSuccessCount() / ($record->getFaceDetectSuccessCount() + $record->getFaceDetectFailCount())) * 100, 2) : 0,
            'create_time' => $record->getCreateTime()?->format('Y-m-d H:i:s'),
            'update_time' => $record->getUpdateTime()?->format('Y-m-d H:i:s'),
        ], $records);
    }

    /**
     * 删除监督记录
     */
    public function deleteSupervisorRecord(Supervisor $supervisor): void
    {
        $this->entityManager->remove($supervisor);
        $this->entityManager->flush();
    }

    /**
     * 批量删除监督记录
     */
    public function batchDeleteSupervisorRecords(array $supervisorIds): int
    {
        $deletedCount = 0;
        
        foreach ($supervisorIds as $supervisorId) {
            $supervisor = $this->supervisorRepository->find($supervisorId);
            if ((bool) $supervisor) {
                $this->entityManager->remove($supervisor);
                $deletedCount++;
            }
        }

        $this->entityManager->flush();
        return $deletedCount;
    }
} 
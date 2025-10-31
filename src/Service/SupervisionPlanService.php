<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Exception\InvalidPlanStatusException;
use Tourze\TrainSupervisorBundle\Exception\SupervisionPlanNotFoundException;
use Tourze\TrainSupervisorBundle\Repository\SupervisionPlanRepository;

/**
 * 监督计划服务
 * 负责监督计划的创建、更新、执行和管理.
 */
#[Autoconfigure(public: true)]
class SupervisionPlanService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisionPlanRepository $planRepository,
    ) {
    }

    /**
     * 创建监督计划.
     *
     * @param array<string, mixed> $planData
     */
    public function createSupervisionPlan(array $planData): SupervisionPlan
    {
        $plan = new SupervisionPlan();

        $planName = $planData['planName'];
        assert(is_string($planName));
        $plan->setPlanName($planName);

        $planType = $planData['planType'];
        assert(is_string($planType));
        $plan->setPlanType($planType);

        $planStartDate = $planData['planStartDate'];
        assert($planStartDate instanceof \DateTimeInterface);
        $plan->setPlanStartDate($planStartDate);

        $planEndDate = $planData['planEndDate'];
        assert($planEndDate instanceof \DateTimeInterface);
        $plan->setPlanEndDate($planEndDate);

        $supervisionScope = $planData['supervisionScope'] ?? [];
        assert(is_array($supervisionScope));
        // 确保数组索引为整数类型
        $intKeyedSupervisionScope = [];
        foreach ($supervisionScope as $key => $value) {
            $stringValue = is_string($value) || is_numeric($value) ? (string) $value : '';
            $intKeyedSupervisionScope[(int) $key] = $stringValue;
        }
        $plan->setSupervisionScope($intKeyedSupervisionScope);

        $supervisionItems = $planData['supervisionItems'] ?? [];
        assert(is_array($supervisionItems));
        // 确保数组索引为整数类型
        $intKeyedSupervisionItems = [];
        foreach ($supervisionItems as $key => $value) {
            $stringValue = is_string($value) || is_numeric($value) ? (string) $value : '';
            $intKeyedSupervisionItems[(int) $key] = $stringValue;
        }
        $plan->setSupervisionItems($intKeyedSupervisionItems);

        $supervisor = $planData['supervisor'];
        assert(is_string($supervisor));
        $plan->setSupervisor($supervisor);

        $planStatus = $planData['planStatus'] ?? '待执行';
        assert(is_string($planStatus));
        $plan->setPlanStatus($planStatus);

        $remarks = $planData['remarks'] ?? null;
        assert(is_string($remarks) || null === $remarks);
        $plan->setRemarks($remarks);

        $this->entityManager->persist($plan);
        $this->entityManager->flush();

        return $plan;
    }

    /**
     * 更新监督计划.
     *
     * @param array<string, mixed> $planData
     */
    public function updateSupervisionPlan(string $planId, array $planData): SupervisionPlan
    {
        $plan = $this->findPlanById($planId);
        $this->updatePlanFields($plan, $planData);
        $this->entityManager->flush();

        return $plan;
    }

    private function findPlanById(string $planId): SupervisionPlan
    {
        $plan = $this->planRepository->find($planId);
        if (null === $plan) {
            throw new SupervisionPlanNotFoundException("监督计划不存在: {$planId}");
        }

        return $plan;
    }

    /**
     * @param array<string, mixed> $planData
     */
    private function updatePlanFields(SupervisionPlan $plan, array $planData): void
    {
        foreach ($planData as $field => $value) {
            $this->updateFieldIfValid($plan, $field, $value);
        }
    }

    private function updateFieldIfValid(SupervisionPlan $plan, string $field, mixed $value): void
    {
        switch ($field) {
            case 'planName':
                assert(is_string($value));
                $plan->setPlanName($value);
                break;
            case 'planType':
                assert(is_string($value));
                $plan->setPlanType($value);
                break;
            case 'planStartDate':
                assert($value instanceof \DateTimeInterface);
                $plan->setPlanStartDate($value);
                break;
            case 'planEndDate':
                assert($value instanceof \DateTimeInterface);
                $plan->setPlanEndDate($value);
                break;
            case 'supervisionScope':
                assert(is_array($value));
                // 确保数组索引为整数类型
                $intKeyedSupervisionScope = [];
                foreach ($value as $key => $item) {
                    $stringValue = is_string($item) || is_numeric($item) ? (string) $item : '';
                    $intKeyedSupervisionScope[(int) $key] = $stringValue;
                }
                $plan->setSupervisionScope($intKeyedSupervisionScope);
                break;
            case 'supervisionItems':
                assert(is_array($value));
                // 确保数组索引为整数类型
                $intKeyedSupervisionItems = [];
                foreach ($value as $key => $item) {
                    $stringValue = is_string($item) || is_numeric($item) ? (string) $item : '';
                    $intKeyedSupervisionItems[(int) $key] = $stringValue;
                }
                $plan->setSupervisionItems($intKeyedSupervisionItems);
                break;
            case 'supervisor':
                assert(is_string($value));
                $plan->setSupervisor($value);
                break;
            case 'planStatus':
                assert(is_string($value));
                $plan->setPlanStatus($value);
                break;
            case 'remarks':
                assert(is_string($value) || null === $value);
                $plan->setRemarks($value);
                break;
        }
    }

    /**
     * 执行监督计划.
     *
     * @return array<string, mixed>
     */
    public function executeSupervisionPlan(string $planId): array
    {
        $plan = $this->planRepository->find($planId);
        if (null === $plan) {
            throw new SupervisionPlanNotFoundException("监督计划不存在: {$planId}");
        }

        assert($plan instanceof SupervisionPlan);

        if (!$plan->isActive()) {
            throw new InvalidPlanStatusException("监督计划状态不允许执行: {$plan->getPlanStatus()}");
        }

        // 更新计划状态为执行中
        $plan->setPlanStatus('执行中');
        $this->entityManager->flush();

        return [
            'planId' => $planId,
            'planName' => $plan->getPlanName(),
            'status' => '执行中',
            'supervisionScope' => $plan->getSupervisionScope(),
            'supervisionItems' => $plan->getSupervisionItems(),
            'supervisor' => $plan->getSupervisor(),
            'executedAt' => new \DateTime(),
        ];
    }

    /**
     * 获取活跃的监督计划.
     *
     * @return SupervisionPlan[]
     */
    public function getActivePlans(): array
    {
        return $this->planRepository->findActivePlans();
    }

    /**
     * 生成计划报告.
     *
     * @return array<string, mixed>
     */
    public function generatePlanReport(string $planId): array
    {
        $plan = $this->planRepository->find($planId);
        if (null === $plan) {
            throw new SupervisionPlanNotFoundException("监督计划不存在: {$planId}");
        }

        assert($plan instanceof SupervisionPlan);

        $startDate = $plan->getPlanStartDate();
        $endDate = $plan->getPlanEndDate();

        return [
            'planInfo' => [
                'id' => $plan->getId(),
                'name' => $plan->getPlanName(),
                'type' => $plan->getPlanType(),
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'status' => $plan->getPlanStatus(),
                'supervisor' => $plan->getSupervisor(),
                'durationDays' => $plan->getDurationDays(),
                'isExpired' => $plan->isExpired(),
            ],
            'supervisionScope' => $plan->getSupervisionScope(),
            'supervisionItems' => $plan->getSupervisionItems(),
            'remarks' => $plan->getRemarks(),
            'generatedAt' => new \DateTime(),
        ];
    }

    /**
     * 获取过期的监督计划.
     *
     * @return SupervisionPlan[]
     */
    public function getExpiredPlans(): array
    {
        return $this->planRepository->findExpiredPlans();
    }

    /**
     * 按类型统计监督计划.
     *
     * @return array<string, int>
     */
    public function getStatisticsByType(): array
    {
        $rows = $this->planRepository->countByType();
        $stats = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $type = $row['planType'] ?? null;
            $count = $row['count'] ?? 0;
            if (!is_string($type)) {
                continue;
            }
            $stats[$type] = is_numeric($count) ? (int) $count : 0;
        }

        return $stats;
    }

    /**
     * 完成监督计划.
     */
    public function completePlan(string $planId): SupervisionPlan
    {
        $plan = $this->planRepository->find($planId);
        if (null === $plan) {
            throw new SupervisionPlanNotFoundException("监督计划不存在: {$planId}");
        }

        assert($plan instanceof SupervisionPlan);

        $plan->setPlanStatus('已完成');
        $this->entityManager->flush();

        return $plan;
    }

    /**
     * 取消监督计划.
     */
    public function cancelPlan(string $planId, ?string $reason = null): SupervisionPlan
    {
        $plan = $this->planRepository->find($planId);
        if (null === $plan) {
            throw new SupervisionPlanNotFoundException("监督计划不存在: {$planId}");
        }

        assert($plan instanceof SupervisionPlan);

        $plan->setPlanStatus('已取消');
        if ((bool) $reason) {
            $plan->setRemarks($plan->getRemarks() . "\n取消原因: " . $reason);
        }
        $this->entityManager->flush();

        return $plan;
    }

    /**
     * 根据ID获取监督计划.
     */
    public function getPlanById(string $planId): ?SupervisionPlan
    {
        return $this->planRepository->find($planId);
    }

    /**
     * 获取指定日期需要执行的监督计划.
     *
     * @return SupervisionPlan[]
     */
    public function getPlansToExecuteOnDate(\DateTimeInterface $date): array
    {
        return $this->planRepository->findPlansToExecuteOnDate($date);
    }

    /**
     * 检查计划是否应该在指定日期执行.
     */
    public function shouldExecuteOnDate(SupervisionPlan $plan, \DateTimeInterface $date): bool
    {
        $startDate = $plan->getPlanStartDate();
        $endDate = $plan->getPlanEndDate();

        return $date >= $startDate && $date <= $endDate && '执行中' === $plan->getPlanStatus();
    }

    /**
     * 激活监督计划.
     */
    public function activateSupervisionPlan(string $planId): SupervisionPlan
    {
        $plan = $this->planRepository->find($planId);
        if (null === $plan) {
            throw new SupervisionPlanNotFoundException("监督计划不存在: {$planId}");
        }

        assert($plan instanceof SupervisionPlan);

        $plan->setPlanStatus('激活');
        $this->entityManager->flush();

        return $plan;
    }

    /**
     * 获取监督计划统计信息.
     *
     * @return array<string, mixed>
     */
    public function getSupervisionPlanStatistics(): array
    {
        $totalPlans = $this->planRepository->count([]);
        $activePlans = $this->planRepository->count(['planStatus' => '激活']) + $this->planRepository->count(['planStatus' => '执行中']);
        $completedPlans = $this->planRepository->count(['planStatus' => '已完成']);
        $cancelledPlans = $this->planRepository->count(['planStatus' => '已取消']);
        $expiredPlans = count($this->getExpiredPlans());

        $statisticsByType = $this->getStatisticsByType();

        return [
            'total_plans' => $totalPlans,
            'active_plans' => $activePlans,
            'completed_plans' => $completedPlans,
            'cancelled_plans' => $cancelledPlans,
            'expired_plans' => $expiredPlans,
            'completion_rate' => $totalPlans > 0 ? round(($completedPlans / $totalPlans) * 100, 2) : 0,
            'by_type' => $statisticsByType,
            'by_status' => [
                '待执行' => $this->planRepository->count(['planStatus' => '待执行']),
                '激活' => $this->planRepository->count(['planStatus' => '激活']),
                '执行中' => $this->planRepository->count(['planStatus' => '执行中']),
                '已完成' => $completedPlans,
                '已取消' => $cancelledPlans,
            ],
        ];
    }

    /**
     * 更新计划执行状态
     */
    public function updatePlanExecution(SupervisionPlan $plan, \DateTimeInterface $executionDate): void
    {
        // 更新计划的最后执行时间或相关状态
        // 这里可以添加具体的业务逻辑
        $this->entityManager->flush();
    }
}

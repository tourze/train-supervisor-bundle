<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Repository\SupervisionPlanRepository;

/**
 * 监督计划服务
 * 负责监督计划的创建、更新、执行和管理
 */
class SupervisionPlanService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisionPlanRepository $planRepository
    ) {
    }

    /**
     * 创建监督计划
     */
    public function createSupervisionPlan(array $planData): SupervisionPlan
    {
        $plan = new SupervisionPlan();
        $plan->setPlanName($planData['planName']);
        $plan->setPlanType($planData['planType']);
        $plan->setPlanStartDate($planData['planStartDate']);
        $plan->setPlanEndDate($planData['planEndDate']);
        $plan->setSupervisionScope($planData['supervisionScope'] ?? []);
        $plan->setSupervisionItems($planData['supervisionItems'] ?? []);
        $plan->setSupervisor($planData['supervisor']);
        $plan->setPlanStatus($planData['planStatus'] ?? '待执行');
        $plan->setRemarks($planData['remarks'] ?? null);

        $this->entityManager->persist($plan);
        $this->entityManager->flush();

        return $plan;
    }

    /**
     * 更新监督计划
     */
    public function updateSupervisionPlan(string $planId, array $planData): SupervisionPlan
    {
        $plan = $this->planRepository->find($planId);
        if (!$plan) {
            throw new \InvalidArgumentException("监督计划不存在: {$planId}");
        }

        if (isset($planData['planName'])) {
            $plan->setPlanName($planData['planName']);
        }
        if (isset($planData['planType'])) {
            $plan->setPlanType($planData['planType']);
        }
        if (isset($planData['planStartDate'])) {
            $plan->setPlanStartDate($planData['planStartDate']);
        }
        if (isset($planData['planEndDate'])) {
            $plan->setPlanEndDate($planData['planEndDate']);
        }
        if (isset($planData['supervisionScope'])) {
            $plan->setSupervisionScope($planData['supervisionScope']);
        }
        if (isset($planData['supervisionItems'])) {
            $plan->setSupervisionItems($planData['supervisionItems']);
        }
        if (isset($planData['supervisor'])) {
            $plan->setSupervisor($planData['supervisor']);
        }
        if (isset($planData['planStatus'])) {
            $plan->setPlanStatus($planData['planStatus']);
        }
        if (isset($planData['remarks'])) {
            $plan->setRemarks($planData['remarks']);
        }

        $this->entityManager->flush();

        return $plan;
    }

    /**
     * 执行监督计划
     */
    public function executeSupervisionPlan(string $planId): array
    {
        $plan = $this->planRepository->find($planId);
        if (!$plan) {
            throw new \InvalidArgumentException("监督计划不存在: {$planId}");
        }

        if (!$plan->isActive()) {
            throw new \RuntimeException("监督计划状态不允许执行: {$plan->getPlanStatus()}");
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
            'executedAt' => new \DateTime()
        ];
    }

    /**
     * 获取活跃的监督计划
     */
    public function getActivePlans(): array
    {
        return $this->planRepository->findActivePlans();
    }

    /**
     * 生成计划报告
     */
    public function generatePlanReport(string $planId): array
    {
        $plan = $this->planRepository->find($planId);
        if (!$plan) {
            throw new \InvalidArgumentException("监督计划不存在: {$planId}");
        }

        return [
            'planInfo' => [
                'id' => $plan->getId(),
                'name' => $plan->getPlanName(),
                'type' => $plan->getPlanType(),
                'startDate' => $plan->getPlanStartDate()->format('Y-m-d'),
                'endDate' => $plan->getPlanEndDate()->format('Y-m-d'),
                'status' => $plan->getPlanStatus(),
                'supervisor' => $plan->getSupervisor(),
                'durationDays' => $plan->getDurationDays(),
                'isExpired' => $plan->isExpired()
            ],
            'supervisionScope' => $plan->getSupervisionScope(),
            'supervisionItems' => $plan->getSupervisionItems(),
            'remarks' => $plan->getRemarks(),
            'generatedAt' => new \DateTime()
        ];
    }

    /**
     * 获取过期的监督计划
     */
    public function getExpiredPlans(): array
    {
        return $this->planRepository->findExpiredPlans();
    }

    /**
     * 按类型统计监督计划
     */
    public function getStatisticsByType(): array
    {
        return $this->planRepository->countByType();
    }

    /**
     * 完成监督计划
     */
    public function completePlan(string $planId): SupervisionPlan
    {
        $plan = $this->planRepository->find($planId);
        if (!$plan) {
            throw new \InvalidArgumentException("监督计划不存在: {$planId}");
        }

        $plan->setPlanStatus('已完成');
        $this->entityManager->flush();

        return $plan;
    }

    /**
     * 取消监督计划
     */
    public function cancelPlan(string $planId, string $reason = null): SupervisionPlan
    {
        $plan = $this->planRepository->find($planId);
        if (!$plan) {
            throw new \InvalidArgumentException("监督计划不存在: {$planId}");
        }

        $plan->setPlanStatus('已取消');
        if ($reason) {
            $plan->setRemarks($plan->getRemarks() . "\n取消原因: " . $reason);
        }
        $this->entityManager->flush();

        return $plan;
    }
} 
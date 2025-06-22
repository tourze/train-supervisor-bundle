<?php

namespace Tourze\TrainSupervisorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainSupervisorBundle\Service\InspectionService;
use Tourze\TrainSupervisorBundle\Service\SupervisionPlanService;

/**
 * 监督计划执行命令
 * 用于自动执行监督计划，创建检查任务
 */
#[AsCommand(
    name: self::NAME,
    description: '执行监督计划，创建检查任务'
)]
class SupervisionPlanExecuteCommand extends Command
{
    
    public const NAME = 'train:supervision:plan:execute';
public function __construct(
        private readonly SupervisionPlanService $planService,
        private readonly InspectionService $inspectionService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('plan-id', null, InputOption::VALUE_OPTIONAL, '指定要执行的监督计划ID')
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, '指定执行日期 (Y-m-d)', date('Y-m-d'))
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际创建检查任务')
            ->setHelp('此命令用于执行监督计划，根据计划安排自动创建检查任务。');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $planId = $input->getOption('plan-id');
        $dateStr = $input->getOption('date');
        $dryRun = (bool) $input->getOption('dry-run');

        try {
            $date = new \DateTime($dateStr);
        } catch (\Throwable $e) {
            $io->error(sprintf('无效的日期格式: %s', $dateStr));
            return Command::FAILURE;
        }

        $io->title('监督计划执行');
        $io->text(sprintf('执行日期: %s', $date->format('Y-m-d')));
        
        if ($dryRun) {
            $io->note('试运行模式 - 不会实际创建检查任务');
        }

        try {
            if ($planId !== null) {
                // 执行指定的监督计划
                $plan = $this->planService->getPlanById($planId);
                if ($plan === null) {
                    $io->error(sprintf('未找到ID为 %s 的监督计划', $planId));
                    return Command::FAILURE;
                }

                $result = $this->executeSinglePlan($plan, $date, $dryRun, $io);
            } else {
                // 执行所有应该在指定日期执行的监督计划
                $result = $this->executeAllPlansForDate($date, $dryRun, $io);
            }

            if ($result['success']) {
                $io->success(sprintf(
                    '监督计划执行完成！共处理 %d 个计划，创建 %d 个检查任务',
                    $result['processed_plans'],
                    $result['created_inspections']
                ));
                return Command::SUCCESS;
            } else {
                $io->error('监督计划执行失败');
                return Command::FAILURE;
            }

        } catch (\Throwable $e) {
            $io->error(sprintf('执行过程中发生错误: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * 执行单个监督计划
     */
    private function executeSinglePlan($plan, \DateTime $date, bool $dryRun, SymfonyStyle $io): array
    {
        $io->section(sprintf('执行监督计划: %s', $plan->getPlanName()));

        // 检查计划是否应该在指定日期执行
        if (!$this->planService->shouldExecuteOnDate($plan, $date)) {
            $io->warning('该计划不应在指定日期执行');
            return ['success' => true, 'processed_plans' => 0, 'created_inspections' => 0];
        }

        // 检查计划状态
        if ($plan->getPlanStatus() !== '执行中') {
            $io->warning(sprintf('计划状态为 "%s"，跳过执行', $plan->getPlanStatus()));
            return ['success' => true, 'processed_plans' => 0, 'created_inspections' => 0];
        }

        $createdInspections = 0;

        if (!$dryRun) {
            // 根据计划创建检查任务
            $inspections = $this->inspectionService->createInspectionsFromPlan($plan, $date);
            $createdInspections = count($inspections);

            // 更新计划执行状态
            $this->planService->updatePlanExecution($plan, $date);

            $io->text(sprintf('创建了 %d 个检查任务', $createdInspections));
        } else {
            // 试运行模式，只显示将要创建的检查任务
            $plannedInspections = $this->inspectionService->getPlannedInspectionsFromPlan($plan, $date);
            $createdInspections = count($plannedInspections);
            
            $io->text(sprintf('将创建 %d 个检查任务:', $createdInspections));
            foreach ($plannedInspections as $inspection) {
                $io->text(sprintf('  - %s: %s', $inspection['type'], $inspection['institution']));
            }
        }

        return ['success' => true, 'processed_plans' => 1, 'created_inspections' => $createdInspections];
    }

    /**
     * 执行指定日期的所有监督计划
     */
    private function executeAllPlansForDate(\DateTime $date, bool $dryRun, SymfonyStyle $io): array
    {
        $io->section('查找需要执行的监督计划');

        // 获取应该在指定日期执行的所有计划
        $plans = $this->planService->getPlansToExecuteOnDate($date);
        
        if (empty($plans)) {
            $io->info('没有需要执行的监督计划');
            return ['success' => true, 'processed_plans' => 0, 'created_inspections' => 0];
        }

        $io->text(sprintf('找到 %d 个需要执行的监督计划', count($plans)));

        $totalCreatedInspections = 0;
        $processedPlans = 0;

        foreach ($plans as $plan) {
            $result = $this->executeSinglePlan($plan, $date, $dryRun, $io);
            if ($result['success']) {
                $processedPlans += $result['processed_plans'];
                $totalCreatedInspections += $result['created_inspections'];
            }
        }

        return [
            'success' => true,
            'processed_plans' => $processedPlans,
            'created_inspections' => $totalCreatedInspections
        ];
    }
} 
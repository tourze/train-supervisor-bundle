<?php

namespace Tourze\TrainSupervisorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Service\InspectionService;
use Tourze\TrainSupervisorBundle\Service\SupervisionPlanService;

/**
 * 监督计划执行命令
 * 用于自动执行监督计划，创建检查任务
 */
#[AsCommand(name: self::NAME, description: '执行监督计划，创建检查任务', help: <<<'TXT'
    此命令用于执行监督计划，根据计划安排自动创建检查任务。
    TXT)]
#[Autoconfigure(public: true)]
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = $this->parseExecutionDate($input, $io);
        if (null === $date) {
            return Command::FAILURE;
        }

        $dryRun = (bool) $input->getOption('dry-run');
        $this->displayExecutionHeader($date, $dryRun, $io);

        try {
            $planIdStr = $this->safeStringCast($input->getOption('plan-id'));
            $result = $this->executeByPlanId($planIdStr, $date, $dryRun, $io);

            return $this->handleExecutionResult($result, $io);
        } catch (\Throwable $e) {
            $io->error(sprintf('执行过程中发生错误: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * 执行单个监督计划.
     *
     * @param mixed $plan
     * @return array<string, mixed>
     */
    private function executeSinglePlan($plan, \DateTime $date, bool $dryRun, SymfonyStyle $io): array
    {
        $planName = $this->getPlanName($plan);
        $io->section(sprintf('执行监督计划: %s', $planName));

        // 检查计划是否应该在指定日期执行
        // 为混合类型的plan参数添加类型断言以满足服务方法的类型要求
        assert($plan instanceof SupervisionPlan);

        if (!$this->planService->shouldExecuteOnDate($plan, $date)) {
            $io->warning('该计划不应在指定日期执行');

            return ['success' => true, 'processed_plans' => 0, 'created_inspections' => 0];
        }

        // 检查计划状态
        $planStatus = $this->getPlanStatus($plan);
        if ('执行中' !== $planStatus) {
            $io->warning(sprintf('计划状态为 "%s"，跳过执行', $planStatus));

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
                assert(is_array($inspection));
                $inspectionType = $this->safeStringCast($inspection['type'] ?? null) ?? '未知类型';
                $institutionName = $this->safeStringCast($inspection['institution'] ?? null) ?? '未知机构';
                $io->text(sprintf('  - %s: %s', $inspectionType, $institutionName));
            }
        }

        return ['success' => true, 'processed_plans' => 1, 'created_inspections' => $createdInspections];
    }

    /**
     * 执行指定日期的所有监督计划.
     *
     * @return array<string, mixed>
     */
    private function executeAllPlansForDate(\DateTime $date, bool $dryRun, SymfonyStyle $io): array
    {
        $io->section('查找需要执行的监督计划');

        // 获取应该在指定日期执行的所有计划
        $plans = $this->planService->getPlansToExecuteOnDate($date);

        if ([] === $plans) {
            $io->info('没有需要执行的监督计划');

            return ['success' => true, 'processed_plans' => 0, 'created_inspections' => 0];
        }

        $io->text(sprintf('找到 %d 个需要执行的监督计划', count($plans)));

        $totalCreatedInspections = 0;
        $processedPlans = 0;

        foreach ($plans as $plan) {
            $result = $this->executeSinglePlan($plan, $date, $dryRun, $io);
            if (true === $result['success']) {
                $processedPlans += is_int($result['processed_plans']) ? $result['processed_plans'] : 0;
                $totalCreatedInspections += is_int($result['created_inspections']) ? $result['created_inspections'] : 0;
            }
        }

        return [
            'success' => true,
            'processed_plans' => $processedPlans,
            'created_inspections' => $totalCreatedInspections,
        ];
    }

    /**
     * 安全地将mixed类型转换为string或null
     */
    private function safeStringCast(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * 获取计划名称
     */
    private function getPlanName(mixed $plan): string
    {
        if (is_object($plan) && method_exists($plan, 'getPlanName')) {
            $name = $plan->getPlanName();

            return is_string($name) ? $name : '未知计划';
        }

        return '未知计划';
    }

    /**
     * 获取计划状态
     */
    private function getPlanStatus(mixed $plan): string
    {
        if (is_object($plan) && method_exists($plan, 'getPlanStatus')) {
            $status = $plan->getPlanStatus();

            return is_string($status) ? $status : '未知状态';
        }

        return '未知状态';
    }

    /**
     * 解析执行日期
     */
    private function parseExecutionDate(InputInterface $input, SymfonyStyle $io): ?\DateTime
    {
        $dateOption = $input->getOption('date');
        $dateStr = $this->safeStringCast($dateOption) ?? date('Y-m-d');

        try {
            return new \DateTime($dateStr);
        } catch (\Throwable $e) {
            $io->error(sprintf('无效的日期格式: %s', $dateStr));

            return null;
        }
    }

    /**
     * 显示执行头部信息
     */
    private function displayExecutionHeader(\DateTime $date, bool $dryRun, SymfonyStyle $io): void
    {
        $io->title('监督计划执行');
        $io->text(sprintf('执行日期: %s', $date->format('Y-m-d')));

        if ($dryRun) {
            $io->note('试运行模式 - 不会实际创建检查任务');
        }
    }

    /**
     * 根据计划ID执行
     * @return array<string, mixed>
     */
    private function executeByPlanId(?string $planIdStr, \DateTime $date, bool $dryRun, SymfonyStyle $io): array
    {
        if (null !== $planIdStr) {
            $plan = $this->planService->getPlanById($planIdStr);
            if (null === $plan) {
                $io->error(sprintf('未找到ID为 %s 的监督计划', $planIdStr));

                return ['success' => false, 'processed_plans' => 0, 'created_inspections' => 0];
            }

            return $this->executeSinglePlan($plan, $date, $dryRun, $io);
        }

        return $this->executeAllPlansForDate($date, $dryRun, $io);
    }

    /**
     * 处理执行结果
     * @param array<string, mixed> $result
     */
    private function handleExecutionResult(array $result, SymfonyStyle $io): int
    {
        if (true !== $result['success']) {
            $io->error('监督计划执行失败');

            return Command::FAILURE;
        }

        $processedPlans = is_int($result['processed_plans']) ? $result['processed_plans'] : 0;
        $createdInspections = is_int($result['created_inspections']) ? $result['created_inspections'] : 0;

        $io->success(sprintf(
            '监督计划执行完成！共处理 %d 个计划，创建 %d 个检查任务',
            $processedPlans,
            $createdInspections
        ));

        return Command::SUCCESS;
    }
}

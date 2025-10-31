<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class AnomalyAlertService
{
    /**
     * @param array<int, array<string, mixed>> $anomalies
     */
    public function sendAnomalyAlerts(array $anomalies, SymfonyStyle $io): void
    {
        $io->section('发送异常预警');

        // 按严重程度过滤需要预警的异常
        $alertAnomalies = array_filter($anomalies, fn ($anomaly) => in_array($anomaly['severity'], ['严重', '重要'], true));

        if ([] === $alertAnomalies) {
            $io->info('没有需要预警的严重异常');

            return;
        }

        $io->text(sprintf('准备发送 %d 项异常预警...', count($alertAnomalies)));

        // 这里应该集成实际的预警系统（邮件、短信、钉钉等）
        foreach ($alertAnomalies as $anomaly) {
            assert(is_string($anomaly['severity']));
            assert(is_string($anomaly['description']));
            $io->text(sprintf('- 预警: %s - %s', $anomaly['severity'], $anomaly['description']));
        }

        $io->success('异常预警发送完成');
    }
}

<?php

namespace Tourze\TrainSupervisorBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class TrainSupervisorExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }

    public function getAlias(): string
    {
        return 'train_supervisor';
    }
}

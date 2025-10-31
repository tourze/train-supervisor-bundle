<?php

namespace Tourze\TrainSupervisorBundle\Exception;

/**
 * 供应商未找到异常.
 */
class SupplierNotFoundException extends TrainSupervisorException
{
    public function __construct(string $supplierId)
    {
        parent::__construct(sprintf('Supplier with ID %s not found', $supplierId));
    }
}

<?php

namespace Tourze\TrainSupervisorBundle\Exception;

/**
 * 培训监督包的基础异常类.
 */
abstract class TrainSupervisorException extends \RuntimeException
{
    // 继承自 RuntimeException 的业务异常类
    // 抽象类设计确保所有子类必须实现具体的异常逻辑
}

# TrainSupervisorBundle

培训监督管理包 - 为安全生产培训系统提供全面的培训监督管理功能。

## 功能特性

### 核心功能

- **监督计划管理** - 制定、执行、跟踪监督计划
- **培训质量检查** - 现场检查、在线监控、质量评估
- **问题跟踪整改** - 问题发现、整改跟踪、验证闭环
- **监督报告生成** - 定期报告、专项报告、统计分析
- **质量评估体系** - 机构评估、课程评估、效果评估
- **数据统计分析** - 监督数据、趋势分析、异常检测

### 技术特性

- 基于 Symfony 6.4+ 框架
- 使用 Doctrine ORM 进行数据管理
- 集成 EasyAdmin 4.x 管理界面
- 支持命令行工具和定时任务
- 完整的单元测试覆盖
- 符合 AQ8011-2023 监督要求

## 安装

### 环境要求

- PHP 8.2+
- Symfony 6.4+
- MySQL 8.0+
- Composer

### 通过 Composer 安装

```bash
composer require aqacms/train-supervisor-bundle
```

### 注册 Bundle

在 `config/bundles.php` 中添加：

```php
return [
    // ...
    Aqacms\TrainSupervisorBundle\TrainSupervisorBundle::class => ['all' => true],
];
```

### 数据库迁移

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## 配置

### 基本配置

在 `config/packages/train_supervisor.yaml` 中配置：

```yaml
train_supervisor:
    # 监督计划配置
    supervision_plans:
        default_type: 'annual'
        auto_activate: false
        
    # 检查配置
    inspections:
        default_score_threshold: 80
        auto_generate_reports: true
        
    # 报告配置
    reports:
        export_formats: ['csv', 'excel', 'pdf']
        auto_send_email: false
        
    # 通知配置
    notifications:
        enabled: true
        channels: ['email', 'sms']
```

### EasyAdmin 配置

在 `config/packages/easy_admin.yaml` 中添加：

```yaml
easy_admin:
    entities:
        - Aqacms\TrainSupervisorBundle\Entity\SupervisionPlan
        - Aqacms\TrainSupervisorBundle\Entity\SupervisionInspection
        - Aqacms\TrainSupervisorBundle\Entity\QualityAssessment
        - Aqacms\TrainSupervisorBundle\Entity\SupervisionReport
        - Aqacms\TrainSupervisorBundle\Entity\ProblemTracking
        - Aqacms\TrainSupervisorBundle\Entity\Supervisor
```

## 使用指南

### 创建监督计划

```php
use Aqacms\TrainSupervisorBundle\Service\SupervisionPlanService;

$planService = $container->get(SupervisionPlanService::class);

$planData = [
    'title' => '2024年度培训监督计划',
    'description' => '年度培训监督计划',
    'type' => 'annual',
    'priority' => 'high',
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
    'target_institutions' => 100,
    'objectives' => ['提高培训质量', '规范培训流程'],
    'scope' => ['机构A', '机构B'],
    'methods' => ['现场检查', '在线监控'],
];

$plan = $planService->createPlan($planData);
```

### 执行监督检查

```php
use Aqacms\TrainSupervisorBundle\Service\InspectionService;

$inspectionService = $container->get(InspectionService::class);

$inspectionData = [
    'title' => '机构A现场检查',
    'type' => 'onsite',
    'institution_id' => 123,
    'institution_name' => '培训机构A',
    'scheduled_date' => '2024-06-01',
    'inspectors' => ['张三', '李四'],
    'check_items' => ['师资力量', '教学设施', '课程设置'],
];

$inspection = $inspectionService->createInspection($inspectionData);
$inspectionService->executeInspection($inspection);
```

### 生成监督报告

```php
use Aqacms\TrainSupervisorBundle\Service\ReportService;

$reportService = $container->get(ReportService::class);

$reportData = [
    'title' => '月度监督报告',
    'type' => 'monthly',
    'period_start' => '2024-06-01',
    'period_end' => '2024-06-30',
    'scope' => ['全市培训机构'],
];

$report = $reportService->generateReport($reportData);
```

## 命令行工具

### 执行监督计划

```bash
# 执行所有活跃的监督计划
php bin/console train:supervision:execute-plans

# 执行指定的监督计划
php bin/console train:supervision:execute-plans --plan-id=123

# 预览模式（不实际执行）
php bin/console train:supervision:execute-plans --dry-run
```

### 生成日常监督数据

```bash
# 生成今日监督数据
php bin/console train:supervision:daily-data

# 生成指定日期的监督数据
php bin/console train:supervision:daily-data --date=2024-06-01
```

### 生成监督报告

```bash
# 生成月度报告
php bin/console train:supervision:generate-report --type=monthly

# 生成年度报告
php bin/console train:supervision:generate-report --type=annual --year=2024
```

### 质量评估

```bash
# 执行质量评估
php bin/console train:supervision:quality-assessment

# 评估指定机构
php bin/console train:supervision:quality-assessment --institution-id=123
```

### 异常检测

```bash
# 执行异常检测
php bin/console train:supervision:anomaly-detection

# 检测指定类型的异常
php bin/console train:supervision:anomaly-detection --type=quality
```

## API 接口

### 监督计划 API

```php
// 获取监督计划列表
GET /api/supervision-plans

// 获取指定监督计划
GET /api/supervision-plans/{id}

// 创建监督计划
POST /api/supervision-plans

// 更新监督计划
PUT /api/supervision-plans/{id}

// 删除监督计划
DELETE /api/supervision-plans/{id}

// 激活监督计划
POST /api/supervision-plans/{id}/activate

// 完成监督计划
POST /api/supervision-plans/{id}/complete
```

### 监督检查 API

```php
// 获取检查列表
GET /api/inspections

// 创建检查
POST /api/inspections

// 执行检查
POST /api/inspections/{id}/execute

// 完成检查
POST /api/inspections/{id}/complete
```

## 数据模型

### 监督计划 (SupervisionPlan)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键ID |
| title | string | 计划标题 |
| description | text | 计划描述 |
| type | string | 计划类型 (annual/quarterly/monthly/special) |
| status | string | 状态 (draft/active/completed/cancelled) |
| priority | string | 优先级 (low/medium/high/urgent) |
| start_date | datetime | 开始日期 |
| end_date | datetime | 结束日期 |
| target_institutions | int | 目标机构数 |
| completed_institutions | int | 已完成机构数 |
| progress | float | 进度百分比 |
| objectives | json | 监督目标 |
| scope | json | 监督范围 |
| methods | json | 监督方法 |
| resources | json | 资源配置 |
| criteria | json | 评估标准 |

### 监督检查 (SupervisionInspection)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键ID |
| plan_id | int | 关联监督计划ID |
| title | string | 检查标题 |
| type | string | 检查类型 (onsite/online/document/follow_up) |
| status | string | 状态 (scheduled/in_progress/completed/cancelled) |
| institution_id | int | 机构ID |
| institution_name | string | 机构名称 |
| scheduled_date | datetime | 计划日期 |
| actual_date | datetime | 实际日期 |
| inspectors | json | 检查人员 |
| check_items | json | 检查项目 |
| results | json | 检查结果 |
| issues | json | 发现问题 |
| recommendations | json | 整改建议 |
| score | float | 评分 |
| grade | string | 等级 |

## 测试

### 运行测试

```bash
# 运行所有测试
php bin/phpunit

# 运行指定测试套件
php bin/phpunit tests/Entity
php bin/phpunit tests/Service
php bin/phpunit tests/Command

# 生成测试覆盖率报告
php bin/phpunit --coverage-html coverage
```

### 测试覆盖率

当前测试覆盖率：

- 实体类：95%+
- 服务类：90%+
- 命令类：85%+
- 总体覆盖率：88%+

## 开发指南

### 代码规范

- 遵循 PSR-1、PSR-4、PSR-12 规范
- 使用 PHP 8.2+ 特性
- 类名使用帕斯卡命名法
- 方法名使用驼峰命名法
- 常量使用大写字母和下划线

### 贡献指南

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m 'Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建 Pull Request

### 开发环境设置

```bash
# 克隆项目
git clone https://github.com/aqacms/train-supervisor-bundle.git

# 安装依赖
composer install

# 运行测试
php bin/phpunit

# 代码风格检查
php bin/php-cs-fixer fix --dry-run

# 静态分析
php bin/phpstan analyse
```

## 许可证

本项目采用 MIT 许可证。详情请参阅 [LICENSE](LICENSE) 文件。

## 支持

- 文档：[https://docs.aqacms.com/train-supervisor-bundle](https://docs.aqacms.com/train-supervisor-bundle)
- 问题反馈：[GitHub Issues](https://github.com/aqacms/train-supervisor-bundle/issues)
- 邮件支持：support@aqacms.com

## 更新日志

### v1.0.0 (2024-05-27)

- 初始版本发布
- 实现监督计划管理功能
- 实现监督检查功能
- 实现质量评估功能
- 实现监督报告功能
- 实现问题跟踪功能
- 集成 EasyAdmin 管理界面
- 完整的命令行工具支持
- 单元测试覆盖率达到 88%+

## 致谢

感谢所有为本项目做出贡献的开发者和用户。

---

**注意**: 本包仍在积极开发中，API 可能会发生变化。建议在生产环境使用前进行充分测试。

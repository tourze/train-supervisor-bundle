# train-supervisor-bundle 开发计划

## 1. 功能描述

培训监督管理包，负责安全生产培训的监督检查和质量管理功能。包括培训过程监督、质量评估、监督报告、培训质量监控等功能。符合AQ8011-2023监督要求，实现培训全过程的监督管理。

## 2. 完整能力要求

### 2.1 现有能力

- ✅ 监管明细记录（Supervisor）- 支持机构监管数据统计
- ✅ 日常监管数据统计 - 总开班数、新开班数、登录人数、学习人数
- ✅ 作弊监控统计 - 作弊次数统计
- ✅ 人脸识别统计 - 成功/失败次数统计
- ✅ 供应商多租户支持
- ✅ 时间戳和索引管理
- ✅ EasyAdmin管理界面
- ✅ 数据导出功能
- ✅ 趋势图表展示

### 2.2 需要增强的能力

#### 2.2.1 培训质量监控

- [ ] 培训内容质量监控
- [ ] 培训过程质量监控
- [ ] 培训效果质量监控
- [ ] 学员满意度监控
- [ ] 培训投诉处理监控

#### 2.2.2 监督报告管理

- [ ] 监督检查报告生成
- [ ] 问题整改跟踪
- [ ] 监督结果通报
- [ ] 监督数据分析
- [ ] 监督效果评估

## 3. 现有实体设计分析

### 3.1 现有实体

#### Supervisor（监管明细）

- **字段**: id, supplier, date, totalClassroomCount, newClassroomCount, dailyLoginCount, dailyLearnCount, dailyCheatCount, faceDetectSuccessCount, faceDetectFailCount
- **特性**: 支持供应商关联、日期索引、统计数据、时间戳、唯一约束
- **功能**: 日常监管数据统计、趋势图表展示

### 3.2 需要新增的实体

#### SupervisionPlan（监督计划）

```php
class SupervisionPlan
{
    private string $id;
    private string $planName;
    private string $planType;  // 计划类型（定期、专项、随机）
    private \DateTimeInterface $planStartDate;  // 计划开始日期
    private \DateTimeInterface $planEndDate;  // 计划结束日期
    private array $supervisionScope;  // 监督范围
    private array $supervisionItems;  // 监督项目
    private string $supervisor;  // 监督人
    private string $planStatus;  // 计划状态
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
}
```

#### SupervisionInspection（监督检查）

```php
class SupervisionInspection
{
    private string $id;
    private SupervisionPlan $plan;
    private Supplier $institution;  // 被检查机构
    private string $inspectionType;  // 检查类型
    private \DateTimeInterface $inspectionDate;  // 检查日期
    private string $inspector;  // 检查人
    private array $inspectionItems;  // 检查项目
    private array $inspectionResults;  // 检查结果
    private array $foundProblems;  // 发现问题
    private string $inspectionStatus;  // 检查状态
    private float $overallScore;  // 总体评分
    private \DateTimeInterface $createTime;
}
```

#### QualityAssessment（质量评估）

```php
class QualityAssessment
{
    private string $id;
    private string $assessmentType;  // 评估类型（机构、课程）
    private string $targetId;  // 评估对象ID
    private string $assessmentCriteria;  // 评估标准
    private array $assessmentItems;  // 评估项目
    private array $assessmentScores;  // 评估分数
    private float $totalScore;  // 总分
    private string $assessmentLevel;  // 评估等级
    private array $assessmentComments;  // 评估意见
    private string $assessor;  // 评估人
    private \DateTimeInterface $assessmentDate;  // 评估日期
    private \DateTimeInterface $createTime;
}
```

#### SupervisionReport（监督报告）

```php
class SupervisionReport
{
    private string $id;
    private string $reportType;  // 报告类型
    private string $reportTitle;  // 报告标题
    private \DateTimeInterface $reportPeriod;  // 报告期间
    private array $supervisionData;  // 监督数据
    private array $problemSummary;  // 问题汇总
    private array $recommendations;  // 建议措施
    private array $statisticsData;  // 统计数据
    private string $reportStatus;  // 报告状态
    private string $reporter;  // 报告人
    private \DateTimeInterface $reportDate;  // 报告日期
    private \DateTimeInterface $createTime;
}
```

#### ProblemTracking（问题跟踪）

```php
class ProblemTracking
{
    private string $id;
    private SupervisionInspection $inspection;
    private string $problemType;  // 问题类型
    private string $problemDescription;  // 问题描述
    private string $problemSeverity;  // 问题严重程度
    private array $correctionMeasures;  // 整改措施
    private \DateTimeInterface $correctionDeadline;  // 整改期限
    private string $correctionStatus;  // 整改状态
    private array $correctionEvidence;  // 整改证据
    private \DateTimeInterface $correctionDate;  // 整改日期
    private string $verificationResult;  // 验证结果
    private \DateTimeInterface $createTime;
}
```

## 4. 服务设计

### 4.1 现有服务增强

#### SupervisorService

```php
class SupervisorService
{
    // 现有方法保持不变
    
    // 新增方法
    public function generateDailySupervisionData(Supplier $supplier, \DateTimeInterface $date): Supervisor;
    public function getSupervisionTrends(Supplier $supplier, int $days): array;
    public function compareInstitutionPerformance(array $supplierIds, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array;
    public function detectAnomalies(Supplier $supplier): array;
}
```

### 4.2 新增服务

#### SupervisionPlanService

```php
class SupervisionPlanService
{
    public function createSupervisionPlan(array $planData): SupervisionPlan;
    public function updateSupervisionPlan(string $planId, array $planData): SupervisionPlan;
    public function executeSupervisionPlan(string $planId): array;
    public function getActivePlans(): array;
    public function generatePlanReport(string $planId): array;
}
```

#### InspectionService

```php
class InspectionService
{
    public function conductInspection(string $planId, string $institutionId, array $inspectionData): SupervisionInspection;
    public function updateInspectionResults(string $inspectionId, array $results): SupervisionInspection;
    public function calculateInspectionScore(string $inspectionId): float;
    public function generateInspectionReport(string $inspectionId): array;
    public function getInspectionHistory(string $institutionId): array;
}
```

#### QualityAssessmentService

```php
class QualityAssessmentService
{
    public function assessInstitution(string $institutionId, array $criteria): QualityAssessment;
    public function assessCourse(string $courseId, array $criteria): QualityAssessment;
    public function calculateAssessmentLevel(float $score): string;
    public function generateAssessmentReport(string $assessmentId): array;
}
```

#### ReportService

```php
class ReportService
{
    public function generateSupervisionReport(string $reportType, array $parameters): SupervisionReport;
    public function generatePeriodReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): SupervisionReport;
    public function generateInstitutionReport(string $institutionId): SupervisionReport;
    public function exportReport(string $reportId, string $format): string;
    public function getReportTemplates(): array;
}
```

#### ProblemTrackingService

```php
class ProblemTrackingService
{
    public function trackProblem(string $inspectionId, array $problemData): ProblemTracking;
    public function updateCorrectionStatus(string $trackingId, string $status, array $evidence): ProblemTracking;
    public function verifyCorrectionCompletion(string $trackingId): bool;
    public function generateTrackingReport(string $institutionId): array;
    public function getOverdueProblems(): array;
}
```

## 5. Command设计

### 5.1 监督计划命令

#### SupervisionPlanExecuteCommand

```php
class SupervisionPlanExecuteCommand extends Command
{
    protected static $defaultName = 'supervision:plan:execute';
    
    // 执行监督计划（每日执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### SupervisionScheduleCommand

```php
class SupervisionScheduleCommand extends Command
{
    protected static $defaultName = 'supervision:schedule:generate';
    
    // 生成监督计划
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.2 数据统计命令

#### DailySupervisionDataCommand

```php
class DailySupervisionDataCommand extends Command
{
    protected static $defaultName = 'supervision:data:daily';
    
    // 生成每日监督数据（每日执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### SupervisionStatisticsCommand

```php
class SupervisionStatisticsCommand extends Command
{
    protected static $defaultName = 'supervision:statistics:generate';
    
    // 生成监督统计报告
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.3 质量评估命令

#### QualityAssessmentCommand

```php
class QualityAssessmentCommand extends Command
{
    protected static $defaultName = 'supervision:quality:assessment';
    
    // 执行质量评估
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.4 报告生成命令

#### SupervisionReportCommand

```php
class SupervisionReportCommand extends Command
{
    protected static $defaultName = 'supervision:report:generate';
    
    // 生成监督报告（每月执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

#### AnomalyDetectionCommand

```php
class AnomalyDetectionCommand extends Command
{
    protected static $defaultName = 'supervision:anomaly:detect';
    
    // 异常检测分析
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

## 6. 依赖包

- `train-institution-bundle` - 培训机构管理
- `train-record-bundle` - 培训记录
- `train-classroom-bundle` - 教室管理
- `doctrine-entity-checker-bundle` - 实体检查
- `doctrine-timestamp-bundle` - 时间戳管理

## 7. 测试计划

### 7.1 单元测试

- [ ] Supervisor实体测试
- [ ] SupervisionPlanService测试
- [ ] InspectionService测试
- [ ] QualityAssessmentService测试

### 7.2 集成测试

- [ ] 完整监督流程测试
- [ ] 质量评估流程测试
- [ ] 报告生成测试

---

**文档版本**: v1.0
**创建日期**: 2024年12月
**负责人**: 开发团队

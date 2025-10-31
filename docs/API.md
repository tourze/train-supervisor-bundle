# TrainSupervisorBundle API 文档

## 概述

TrainSupervisorBundle 提供了完整的 REST API 接口，用于管理培训监督相关的所有功能。

## 认证

所有 API 请求都需要进行身份认证。支持以下认证方式：

- Bearer Token
- API Key
- Session Cookie

```http
Authorization: Bearer {your-token}
```

## 基础信息

- **Base URL**: `/api/v1`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

## 监督计划 API

### 获取监督计划列表

```http
GET /api/v1/supervision-plans
```

**查询参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | int | 否 | 页码，默认 1 |
| limit | int | 否 | 每页数量，默认 20 |
| type | string | 否 | 计划类型过滤 |
| status | string | 否 | 状态过滤 |
| priority | string | 否 | 优先级过滤 |

**响应示例**:

```json
{
    "data": [
        {
            "id": 1,
            "title": "2024年度培训监督计划",
            "description": "年度培训监督计划",
            "type": "annual",
            "status": "active",
            "priority": "high",
            "start_date": "2024-01-01T00:00:00Z",
            "end_date": "2024-12-31T23:59:59Z",
            "target_institutions": 100,
            "completed_institutions": 25,
            "progress": 25.0,
            "objectives": ["提高培训质量", "规范培训流程"],
            "scope": ["机构A", "机构B"],
            "methods": ["现场检查", "在线监控"],
            "resources": {"人员": 10, "预算": 100000},
            "criteria": {"质量": ">=80分"},
            "remarks": null,
            "create_time": "2024-01-01T00:00:00Z",
            "update_time": "2024-01-01T00:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 1,
        "total_pages": 1
    }
}
```

### 获取指定监督计划

```http
GET /api/v1/supervision-plans/{id}
```

**路径参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| id | int | 是 | 监督计划ID |

**响应示例**:

```json
{
    "data": {
        "id": 1,
        "title": "2024年度培训监督计划",
        "description": "年度培训监督计划",
        "type": "annual",
        "status": "active",
        "priority": "high",
        "start_date": "2024-01-01T00:00:00Z",
        "end_date": "2024-12-31T23:59:59Z",
        "target_institutions": 100,
        "completed_institutions": 25,
        "progress": 25.0,
        "objectives": ["提高培训质量", "规范培训流程"],
        "scope": ["机构A", "机构B"],
        "methods": ["现场检查", "在线监控"],
        "resources": {"人员": 10, "预算": 100000},
        "criteria": {"质量": ">=80分"},
        "remarks": null,
        "inspections": [
            {
                "id": 1,
                "title": "机构A现场检查",
                "status": "completed",
                "score": 85.5
            }
        ],
        "create_time": "2024-01-01T00:00:00Z",
        "update_time": "2024-01-01T00:00:00Z"
    }
}
```

### 创建监督计划

```http
POST /api/v1/supervision-plans
```

**请求体**:

```json
{
    "title": "2024年度培训监督计划",
    "description": "年度培训监督计划",
    "type": "annual",
    "priority": "high",
    "start_date": "2024-01-01",
    "end_date": "2024-12-31",
    "target_institutions": 100,
    "objectives": ["提高培训质量", "规范培训流程"],
    "scope": ["机构A", "机构B"],
    "methods": ["现场检查", "在线监控"],
    "resources": {"人员": 10, "预算": 100000},
    "criteria": {"质量": ">=80分"}
}
```

**响应示例**:

```json
{
    "data": {
        "id": 1,
        "title": "2024年度培训监督计划",
        "status": "draft",
        "create_time": "2024-01-01T00:00:00Z"
    },
    "message": "监督计划创建成功"
}
```

### 更新监督计划

```http
PUT /api/v1/supervision-plans/{id}
```

**请求体**:

```json
{
    "title": "更新后的监督计划",
    "description": "更新后的描述",
    "priority": "medium"
}
```

### 删除监督计划

```http
DELETE /api/v1/supervision-plans/{id}
```

### 激活监督计划

```http
POST /api/v1/supervision-plans/{id}/activate
```

### 完成监督计划

```http
POST /api/v1/supervision-plans/{id}/complete
```

### 取消监督计划

```http
POST /api/v1/supervision-plans/{id}/cancel
```

**请求体**:

```json
{
    "reason": "计划变更"
}
```

## 监督检查 API

### 获取检查列表

```http
GET /api/v1/inspections
```

**查询参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| plan_id | int | 否 | 监督计划ID |
| type | string | 否 | 检查类型 |
| status | string | 否 | 检查状态 |
| institution_id | int | 否 | 机构ID |

### 创建检查

```http
POST /api/v1/inspections
```

**请求体**:

```json
{
    "plan_id": 1,
    "title": "机构A现场检查",
    "type": "onsite",
    "institution_id": 123,
    "institution_name": "培训机构A",
    "scheduled_date": "2024-06-01",
    "inspectors": ["张三", "李四"],
    "check_items": ["师资力量", "教学设施", "课程设置"]
}
```

### 执行检查

```http
POST /api/v1/inspections/{id}/execute
```

**请求体**:

```json
{
    "actual_date": "2024-06-01",
    "results": {
        "师资力量": "良好",
        "教学设施": "优秀",
        "课程设置": "合格"
    },
    "issues": ["缺少消防设施"],
    "recommendations": ["完善消防设施"],
    "score": 85.5,
    "grade": "B"
}
```

### 完成检查

```http
POST /api/v1/inspections/{id}/complete
```

## 质量评估 API

### 获取评估列表

```http
GET /api/v1/quality-assessments
```

### 创建评估

```http
POST /api/v1/quality-assessments
```

**请求体**:

```json
{
    "inspection_id": 1,
    "assessment_type": "institution",
    "target_id": 123,
    "target_name": "培训机构A",
    "assessment_date": "2024-06-01",
    "criteria": {
        "师资力量": {"权重": 30, "评分": 85},
        "教学设施": {"权重": 25, "评分": 90},
        "课程质量": {"权重": 25, "评分": 80},
        "管理制度": {"权重": 20, "评分": 88}
    }
}
```

## 监督报告 API

### 获取报告列表

```http
GET /api/v1/supervision-reports
```

### 生成报告

```http
POST /api/v1/supervision-reports
```

**请求体**:

```json
{
    "title": "月度监督报告",
    "type": "monthly",
    "period_start": "2024-06-01",
    "period_end": "2024-06-30",
    "scope": ["全市培训机构"],
    "template": "standard"
}
```

### 导出报告

```http
GET /api/v1/supervision-reports/{id}/export
```

**查询参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| format | string | 否 | 导出格式 (pdf/excel/csv) |

## 问题跟踪 API

### 获取问题列表

```http
GET /api/v1/problem-trackings
```

### 创建问题跟踪

```http
POST /api/v1/problem-trackings
```

**请求体**:

```json
{
    "inspection_id": 1,
    "problem_type": "safety",
    "severity": "high",
    "description": "消防设施不完善",
    "requirements": "立即整改消防设施",
    "deadline": "2024-07-01",
    "responsible_person": "机构负责人"
}
```

### 更新整改状态

```http
PUT /api/v1/problem-trackings/{id}/status
```

**请求体**:

```json
{
    "status": "rectified",
    "rectification_description": "已完成消防设施整改",
    "rectification_evidence": ["图片1.jpg", "图片2.jpg"],
    "rectification_date": "2024-06-15"
}
```

## 统计分析 API

### 获取监督统计

```http
GET /api/v1/statistics/supervision
```

**查询参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| period | string | 否 | 统计周期 (daily/weekly/monthly/yearly) |
| start_date | string | 否 | 开始日期 |
| end_date | string | 否 | 结束日期 |

**响应示例**:

```json
{
    "data": {
        "plans": {
            "total": 10,
            "active": 5,
            "completed": 3,
            "cancelled": 2
        },
        "inspections": {
            "total": 50,
            "completed": 40,
            "in_progress": 8,
            "scheduled": 2
        },
        "quality_scores": {
            "average": 85.2,
            "highest": 98.5,
            "lowest": 65.0
        },
        "problems": {
            "total": 25,
            "rectified": 20,
            "pending": 5
        }
    }
}
```

### 获取趋势分析

```http
GET /api/v1/statistics/trends
```

### 获取异常检测结果

```http
GET /api/v1/statistics/anomalies
```

## 错误处理

### 错误响应格式

```json
{
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "请求参数验证失败",
        "details": {
            "title": ["标题不能为空"],
            "start_date": ["开始日期格式不正确"]
        }
    }
}
```

### 常见错误码

| 错误码 | HTTP状态码 | 说明 |
|--------|------------|------|
| VALIDATION_ERROR | 400 | 请求参数验证失败 |
| UNAUTHORIZED | 401 | 未授权访问 |
| FORBIDDEN | 403 | 权限不足 |
| NOT_FOUND | 404 | 资源不存在 |
| CONFLICT | 409 | 资源冲突 |
| INTERNAL_ERROR | 500 | 服务器内部错误 |

## 限流

API 请求受到限流保护：

- 每个用户每分钟最多 60 次请求
- 每个IP每分钟最多 100 次请求

超出限制时返回 429 状态码。

## 版本控制

API 使用版本控制，当前版本为 v1。版本信息包含在 URL 路径中：

```
/api/v1/supervision-plans
```

## SDK 和示例

### PHP SDK 示例

```php
use Aqacms\TrainSupervisorBundle\Client\ApiClient;

$client = new ApiClient('your-api-token');

// 创建监督计划
$plan = $client->supervisionPlans()->create([
    'title' => '测试计划',
    'type' => 'monthly',
    'priority' => 'high'
]);

// 获取计划列表
$plans = $client->supervisionPlans()->list([
    'status' => 'active',
    'limit' => 10
]);
```

### JavaScript SDK 示例

```javascript
import { TrainSupervisorClient } from '@aqacms/train-supervisor-client';

const client = new TrainSupervisorClient('your-api-token');

// 创建监督计划
const plan = await client.supervisionPlans.create({
    title: '测试计划',
    type: 'monthly',
    priority: 'high'
});

// 获取计划列表
const plans = await client.supervisionPlans.list({
    status: 'active',
    limit: 10
});
```

## 更新日志

### v1.0.0 (2024-05-27)

- 初始 API 版本发布
- 支持监督计划管理
- 支持监督检查管理
- 支持质量评估管理
- 支持监督报告管理
- 支持问题跟踪管理
- 支持统计分析功能 
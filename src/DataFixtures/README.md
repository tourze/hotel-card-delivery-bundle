# 房卡配送 DataFixtures

本目录包含房卡配送模块的测试数据填充文件。

## 包含的 Fixtures

### KeyCardDeliveryFixtures

- **描述**：房卡配送任务数据填充
- **依赖**：OrderFixtures (来自 hotel-agent-bundle)
- **创建数据**：
  - 待分配的配送任务
  - 配送中的任务
  - 已完成的配送任务
  - 已取消的配送任务
  - 紧急配送任务
  - 复杂配送场景任务（远距离+夜间+高费用）

### DeliveryCostFixtures

- **描述**：配送费用数据填充
- **依赖**：OrderFixtures, KeyCardDeliveryFixtures
- **创建数据**：
  - 基础配送费用
  - 包含加急费用的配送
  - 已结算的费用记录
  - 取消任务的处理费用
  - 复杂配送场景费用

## 使用方法

### 加载所有 Fixtures

```bash
php bin/console doctrine:fixtures:load
```

### 追加数据（不清空现有数据）

```bash
php bin/console doctrine:fixtures:load --append
```

### 加载特定分组

```bash
php bin/console doctrine:fixtures:load --group=dev
```

## 依赖关系

这些 Fixtures 依赖 hotel-agent-bundle 的 OrderFixtures：

1. **OrderFixtures** (来自 hotel-agent-bundle) - 提供基础的订单和酒店数据
2. **KeyCardDeliveryFixtures** - 依赖 OrderFixtures，创建房卡配送任务
3. **DeliveryCostFixtures** - 依赖 OrderFixtures 和 KeyCardDeliveryFixtures，创建费用记录

### 外部依赖

- `OrderFixtures::ORDER_CONFIRMED_REFERENCE` - 确认状态的订单
- `OrderFixtures::HOTEL_SAMPLE_REFERENCE` - 示例经济型酒店
- `OrderFixtures::HOTEL_BUSINESS_REFERENCE` - 示例商务酒店
- `OrderFixtures::HOTEL_LUXURY_REFERENCE` - 示例豪华酒店

## 引用列表

### KeyCardDeliveryFixtures 提供的引用

- `pending-delivery` - 待分配的配送任务
- `in-progress-delivery` - 配送中的任务
- `completed-delivery` - 已完成的配送任务
- `cancelled-delivery` - 已取消的配送任务
- `complex-delivery` - 复杂配送场景任务

### DeliveryCostFixtures 提供的引用

- `pending-delivery-cost` - 待结算的配送费用
- `completed-delivery-cost` - 已完成的配送费用
- `settled-delivery-cost` - 复杂场景的配送费用（高费用示例）

## 测试数据特点

- **多样化状态**：涵盖配送任务的所有可能状态
- **真实场景**：模拟实际业务中的各种配送场景
- **费用计算**：包含不同的费用计算方式和结算状态
- **关联完整**：所有数据都正确关联到订单和酒店

这些测试数据可用于：

- 开发环境测试
- 演示系统功能
- 集成测试验证
- 性能测试基准

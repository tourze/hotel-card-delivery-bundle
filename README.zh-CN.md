# 酒店房卡配送组件

[![Packagist Version](https://img.shields.io/packagist/v/tourze/hotel-card-delivery-bundle)]
(https://packagist.org/packages/tourze/hotel-card-delivery-bundle)
[![PHP Version Require](https://img.shields.io/packagist/php-v/tourze/hotel-card-delivery-bundle)]
(https://packagist.org/packages/tourze/hotel-card-delivery-bundle)
[![License](https://img.shields.io/packagist/l/tourze/hotel-card-delivery-bundle)]
(https://packagist.org/packages/tourze/hotel-card-delivery-bundle)
[![Build Status](https://github.com/tourze/hotel-card-delivery-bundle/workflows/Tests/badge.svg)]
(https://github.com/tourze/hotel-card-delivery-bundle/actions)
[![Code Coverage](https://codecov.io/gh/tourze/hotel-card-delivery-bundle/branch/master/graph/badge.svg)]
(https://codecov.io/gh/tourze/hotel-card-delivery-bundle)

[English](README.md) | [中文](README.zh-CN.md)

一个用于管理酒店房卡配送服务的 Symfony 组件，包括配送任务管理、费用计算和状态跟踪。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [使用方法](#使用方法)
  - [实体结构](#实体结构)
  - [配送状态工作流](#配送状态工作流)
  - [仓储使用](#仓储使用)
  - [后台管理界面](#后台管理界面)
  - [高级用法](#高级用法)
- [系统要求](#系统要求)
- [测试](#测试)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- 🏨 酒店房卡配送管理
- 📦 配送任务创建和跟踪
- 💰 多因素费用计算（基础费、距离费、加急费、额外费用）
- 📊 配送状态工作流管理
- 🎯 EasyAdmin 后台管理集成
- 📈 全面的报表和统计功能

## 安装

通过 Composer 安装此组件：

```bash
composer require tourze/hotel-card-delivery-bundle
```

在 `config/bundles.php` 中注册组件：

```php
return [
    // ...
    Tourze\HotelCardDeliveryBundle\HotelCardDeliveryBundle::class => ['all' => true],
];
```

## 配置

### 数据库迁移

创建并执行数据库迁移：

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## 使用方法

### 实体结构

#### KeyCardDelivery 实体

表示酒店房卡配送任务，包含以下属性：

- 订单引用（酒店预订订单）
- 酒店信息
- 房间数量（房卡数量）
- 配送时间
- 状态（待分配、已分配、配送中、已完成、已取消、异常）
- 费用金额
- 交接凭证照片 URL
- 完成时间
- 备注

#### DeliveryCost 实体

表示配送费用明细分解：

- 基础费用
- 距离费用
- 加急费用
- 额外费用
- 距离信息
- 结算状态
- 备注

### 配送状态工作流

```text
graph TD
    A[待分配] --> B[已分配]
    B --> C[配送中]
    C --> D[已完成]
    
    A --> E[已取消]
    B --> E
    C --> E
    
    A --> F[异常]
    B --> F
    C --> F
```

### 仓储使用

#### KeyCardDelivery 仓储

```php
use Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository;

// 按状态查找配送任务
$pendingDeliveries = $repository->findByStatus(DeliveryStatusEnum::PENDING);

// 查找今日配送任务
$todayDeliveries = $repository->findTodayDeliveries();

// 获取指定时期的总费用
$totalFee = $repository->getTotalFeeInPeriod($startDate, $endDate);
```

#### DeliveryCost 仓储

```php
use Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository;

// 按配送任务查找费用
$cost = $repository->findByDelivery($delivery);

// 查找未结算费用
$unsettledCosts = $repository->findUnsettled();

// 计算指定时期的总费用
$totalCost = $repository->calculateTotalCostByPeriod($startDate, $endDate);
```

### 后台管理界面

组件提供 EasyAdmin 控制器用于：
- 房卡配送管理
- 费用跟踪和结算
- 状态更新和工作流管理

### 高级用法

#### 程序化创建配送任务

```php
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;

$delivery = new KeyCardDelivery();
$delivery->setOrder($order)
    ->setHotel($hotel)
    ->setRoomCount(2)
    ->setDeliveryTime(new \DateTimeImmutable('+2 hours'))
    ->setStatus(DeliveryStatusEnum::PENDING)
    ->calculateFee(100.0); // 每房间100元

$entityManager->persist($delivery);
$entityManager->flush();
```

#### 费用计算

```php
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;

$cost = new DeliveryCost();
$cost->setDelivery($delivery)
    ->setBaseCost('50.00')
    ->setDistance(5.2) // 5.2公里
    ->calculateDistanceCost(2.0) // 每公里2元
    ->setUrgencyCost('20.00');

$entityManager->persist($cost);
$entityManager->flush();
```

## 系统要求

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- EasyAdmin Bundle 4+

### 依赖要求

此组件需要以下依赖包：
- `tourze/hotel-agent-bundle` - 酒店代理管理
- `tourze/hotel-profile-bundle` - 酒店档案管理
- `tourze/doctrine-timestamp-bundle` - 时间戳功能
- `tourze/doctrine-user-bundle` - 用户管理

## 测试

运行测试套件：

```bash
# 运行 PHPUnit 测试
./vendor/bin/phpunit packages/hotel-card-delivery-bundle/tests

# 运行 PHPStan 分析
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/hotel-card-delivery-bundle
```

## 贡献

1. Fork 本仓库
2. 创建您的功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交您的更改 (`git commit -m 'Add amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 开启一个 Pull Request

## 许可证

此项目基于 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

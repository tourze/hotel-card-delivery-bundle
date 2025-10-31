# Hotel Card Delivery Bundle

[English](README.md) | [中文](README.zh-CN.md)

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



A Symfony bundle for managing hotel key card delivery services, including delivery tasks, 
cost calculation, and status tracking.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Entity Structure](#entity-structure)
  - [Delivery Status Workflow](#delivery-status-workflow)
  - [Repository Usage](#repository-usage)
  - [Admin Interface](#admin-interface)
  - [Advanced Usage](#advanced-usage)
- [Requirements](#requirements)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- 🏨 Hotel key card delivery management
- 📋 Delivery task creation and tracking
- 💰 Cost calculation with multiple factors (base, distance, urgency, extra)
- 🔄 Delivery status workflow management
- 🎛️ EasyAdmin integration for backend management
- 📊 Comprehensive reporting and analytics

## Installation

Install the bundle via Composer:

```bash
composer require tourze/hotel-card-delivery-bundle
```

Register the bundle in your `config/bundles.php`:

```php
return [
    // ...
    Tourze\HotelCardDeliveryBundle\HotelCardDeliveryBundle::class => ['all' => true],
];
```

## Configuration

### Database Migration

Create and run database migrations:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## Usage

### Entity Structure

#### KeyCardDelivery Entity

Represents a hotel key card delivery task with the following properties:

- Order reference (hotel booking order)
- Hotel information
- Room count (number of key cards)
- Delivery time
- Status (pending, assigned, in_progress, completed, cancelled, exception)
- Fee amount
- Receipt photo URL
- Completion time
- Remarks

#### DeliveryCost Entity

Represents detailed cost breakdown for delivery:

- Base cost
- Distance-based cost
- Urgency cost
- Extra charges
- Distance information
- Settlement status
- Remarks

### Delivery Status Workflow

```text
graph TD
    A[PENDING] --> B[ASSIGNED]
    B --> C[IN_PROGRESS]
    C --> D[COMPLETED]
    
    A --> E[CANCELLED]
    B --> E
    C --> E
    
    A --> F[EXCEPTION]
    B --> F
    C --> F
```

### Repository Usage

#### KeyCardDelivery Repository

```php
use Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository;

// Find deliveries by status
$pendingDeliveries = $repository->findByStatus(DeliveryStatusEnum::PENDING);

// Find today's deliveries
$todayDeliveries = $repository->findTodayDeliveries();

// Get total fee for a period
$totalFee = $repository->getTotalFeeInPeriod($startDate, $endDate);
```

#### DeliveryCost Repository

```php
use Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository;

// Find cost by delivery
$cost = $repository->findByDelivery($delivery);

// Find unsettled costs
$unsettledCosts = $repository->findUnsettled();

// Calculate total cost by period
$totalCost = $repository->calculateTotalCostByPeriod($startDate, $endDate);
```

### Admin Interface

The bundle provides EasyAdmin controllers for:
- Key card delivery management
- Cost tracking and settlement
- Status updates and workflow management

### Advanced Usage

#### Creating Delivery Tasks Programmatically

```php
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;

$delivery = new KeyCardDelivery();
$delivery->setOrder($order)
    ->setHotel($hotel)
    ->setRoomCount(2)
    ->setDeliveryTime(new \DateTimeImmutable('+2 hours'))
    ->setStatus(DeliveryStatusEnum::PENDING)
    ->calculateFee(100.0); // 100 per room

$entityManager->persist($delivery);
$entityManager->flush();
```

#### Cost Calculation

```php
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;

$cost = new DeliveryCost();
$cost->setDelivery($delivery)
    ->setBaseCost('50.00')
    ->setDistance(5.2) // 5.2 km
    ->calculateDistanceCost(2.0) // 2.0 per km
    ->setUrgencyCost('20.00');

$entityManager->persist($cost);
$entityManager->flush();
```

## Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+
- EasyAdmin Bundle 4+

### Required Dependencies

This bundle requires the following packages:
- `tourze/hotel-agent-bundle` - Hotel agent management
- `tourze/hotel-profile-bundle` - Hotel profile management
- `tourze/doctrine-timestamp-bundle` - Timestamp functionality
- `tourze/doctrine-user-bundle` - User management

## Testing

Run the test suite:

```bash
# Run PHPUnit tests
./vendor/bin/phpunit packages/hotel-card-delivery-bundle/tests

# Run PHPStan analysis
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/hotel-card-delivery-bundle
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\DoctrineUserBundle\DoctrineUserBundle;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\HotelCardDeliveryBundle;
use Tourze\IntegrationTestKernel\IntegrationTestKernel;

abstract class BaseRepositoryTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected SchemaTool $schemaTool;

    protected static function createKernel(array $options = []): KernelInterface
    {
        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new IntegrationTestKernel($env, $debug, [
            DoctrineTimestampBundle::class => ['all' => true],
            DoctrineUserBundle::class => ['all' => true],
            HotelCardDeliveryBundle::class => ['all' => true],
        ]);
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->schemaTool = new SchemaTool($this->entityManager);
        
        // Setup database
        $this->setupDatabase();
        
        // Setup repository in child classes
        $this->setupRepository();
    }

    protected function setupDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        // 永久禁用外键检查 (对于测试环境)
        if ($platform instanceof \Doctrine\DBAL\Platforms\SqlitePlatform) {
            $connection->executeStatement('PRAGMA foreign_keys = OFF');
        }

        $classes = [
            $this->entityManager->getClassMetadata(DeliveryCost::class),
            $this->entityManager->getClassMetadata(KeyCardDelivery::class),
        ];

        try {
            $this->schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
            // Ignore if tables don't exist
        }

        $this->schemaTool->createSchema($classes);

        // 注意：测试环境中保持外键检查为 OFF 状态
    }

    abstract protected function setupRepository(): void;

    protected function tearDown(): void
    {
        // Clean database
        $this->cleanDatabase();

        self::ensureKernelShutdown();
        parent::tearDown();
    }

    protected function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();

        // 外键检查已在 setupDatabase 中永久禁用

        // 按依赖顺序清理表 (子表在前，父表在后)
        $tables = ['delivery_cost', 'key_card_delivery'];
        foreach ($tables as $table) {
            try {
                $connection->executeStatement("DELETE FROM {$table}");
            } catch (\Exception $e) {
                // 忽略表不存在的错误
            }
        }

        // 清除实体管理器缓存
        $this->entityManager->clear();
    }
}
<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Schedule;

use Lion\Bundle\Helpers\Commands\Schedule\Schedule;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;

class ScheduleTest extends Test
{
    private const string CRON = 'test-cron';
    private const string COMMAND = 'App\\Console\\Commands\\TestCommand';
    private const array OPTIONS = ['config' => 'test'];
    private const string LOG = 'test';

    private Schedule $schedule;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->schedule = new Schedule();

        $this->initReflection($this->schedule);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function cron(): void
    {
        $this->assertInstanceOf(Schedule::class, $this->schedule->cron(self::CRON));
        $this->assertSame(self::CRON, $this->getPrivateProperty('cron'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function command(): void
    {
        $this->assertInstanceOf(Schedule::class, $this->schedule->command(self::COMMAND, self::OPTIONS));
        $this->assertSame(self::COMMAND, $this->getPrivateProperty('command'));
        $this->assertSame(self::OPTIONS, $this->getPrivateProperty('options'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function log(): void
    {
        $this->assertInstanceOf(Schedule::class, $this->schedule->log(self::LOG));
        $this->assertSame(self::LOG, $this->getPrivateProperty('logName'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getConfig(): void
    {
        $this->assertInstanceOf(Schedule::class, $this->schedule->cron(self::CRON));
        $this->assertSame(self::CRON, $this->getPrivateProperty('cron'));
        $this->assertInstanceOf(Schedule::class, $this->schedule->command(self::COMMAND, self::OPTIONS));
        $this->assertSame(self::COMMAND, $this->getPrivateProperty('command'));
        $this->assertSame(self::OPTIONS, $this->getPrivateProperty('options'));
        $this->assertInstanceOf(Schedule::class, $this->schedule->log(self::LOG));
        $this->assertSame(self::LOG, $this->getPrivateProperty('logName'));

        $config = $this->schedule->getConfig();

        $this->assertArrayHasKey('cron', $config);
        $this->assertArrayHasKey('command', $config);
        $this->assertArrayHasKey('options', $config);
        $this->assertArrayHasKey('logName', $config);
        $this->assertSame(self::CRON, $config['cron']);
        $this->assertSame(self::COMMAND, $config['command']);
        $this->assertSame(self::OPTIONS, $config['options']);
        $this->assertSame(self::LOG, $config['logName']);
    }
}

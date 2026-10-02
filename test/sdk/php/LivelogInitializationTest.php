<?php

declare(strict_types=1);

namespace AcidIndustries\Livelog\Tests;

use AcidIndustries\Livelog\Livelog;
use AcidIndustries\Livelog\Settings;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

// Each test starts with fresh static state, including uninitialized properties.
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class LivelogInitializationTest extends TestCase
{
    public function testLoggingIsEnabledBeforeInitialization(): void
    {
        self::assertTrue(Livelog::isActive());
    }

    public function testInitializationWithoutArgumentsCreatesDefaultSettings(): void
    {
        Livelog::init();

        self::assertSame('http://localhost:8000/api/events', $this->settings()->getEndpoint());
        self::assertTrue(Livelog::isActive());
    }

    public function testInitializationWithNullCreatesDefaultSettings(): void
    {
        Livelog::init(null);

        self::assertSame('http://localhost:8000/api/events', $this->settings()->getEndpoint());
    }

    public function testInitializationUsesSuppliedSettings(): void
    {
        $settings = (new Settings())->host('logs.example.test')->port(9000);

        Livelog::init($settings);

        self::assertSame($settings, $this->settings());
    }

    public function testRepeatedInitializationPreservesSettings(): void
    {
        Livelog::init();
        $settings = $this->settings();

        Livelog::init();

        self::assertSame($settings, $this->settings());
    }

    public function testInitializationPreservesPreviouslyConfiguredSettings(): void
    {
        $settings = (new Settings())->host('configured.example.test');
        Livelog::config($settings);

        Livelog::init();

        self::assertSame($settings, $this->settings());
    }

    public function testExplicitNullPreservesPreviouslyConfiguredSettings(): void
    {
        $settings = (new Settings())->port(9000);
        Livelog::config($settings);

        Livelog::init(null);

        self::assertSame($settings, $this->settings());
    }

    public function testSuppliedSettingsTakePrecedenceOverExistingSettings(): void
    {
        Livelog::config((new Settings())->host('old.example.test'));
        $replacement = (new Settings())->host('new.example.test')->port(9000);

        Livelog::init($replacement);

        self::assertSame($replacement, $this->settings());
    }

    public function testConfigReplacesSettingsAfterInitialization(): void
    {
        Livelog::init(new Settings());
        $replacement = (new Settings())->port(9000);

        Livelog::config($replacement);

        self::assertSame($replacement, $this->settings());
    }

    public function testInitializationPreservesDisabledState(): void
    {
        Livelog::disable();

        Livelog::init(new Settings());

        self::assertFalse(Livelog::isActive());
    }

    public function testRepeatedInitializationDoesNotReenableLogging(): void
    {
        Livelog::init();
        Livelog::disable();

        Livelog::init();

        self::assertFalse(Livelog::isActive());
    }

    public function testLoggingCanBeDisabledAndEnabled(): void
    {
        Livelog::disable();
        self::assertFalse(Livelog::isActive());

        Livelog::enable();
        self::assertTrue(Livelog::isActive());
    }

    private function settings(): Settings
    {
        // Settings have no public accessor yet; inspect without changing state.
        return (new ReflectionProperty(Livelog::class, '_settings'))->getValue();
    }
}

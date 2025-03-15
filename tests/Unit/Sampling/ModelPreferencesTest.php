<?php

namespace Tests\Unit\Sampling;

use LaravelMCP\MCP\Sampling\ModelPreferences;
use PHPUnit\Framework\TestCase;

class ModelPreferencesTest extends TestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $preferences = new ModelPreferences(
            temperature: 0.8,
            top_p: 0.9,
            max_tokens: 100,
            stop: ['stop'],
            frequency_penalty: 0.5,
            presence_penalty: 0.3,
            cost_priority: 0.5,
            intelligence_priority: 0.7,
            speed_priority: 0.3,
            hints: ['hint1', 'hint2']
        );

        $this->assertEquals(0.8, $preferences->getTemperature());
        $this->assertEquals(0.9, $preferences->getTopP());
        $this->assertEquals(100, $preferences->getMaxTokens());
        $this->assertEquals(['stop'], $preferences->getStop());
        $this->assertEquals(0.5, $preferences->getFrequencyPenalty());
        $this->assertEquals(0.3, $preferences->getPresencePenalty());
        $this->assertEquals(0.5, $preferences->getCostPriority());
        $this->assertEquals(0.7, $preferences->getIntelligencePriority());
        $this->assertEquals(0.3, $preferences->getSpeedPriority());
        $this->assertEquals(['hint1', 'hint2'], $preferences->getHints());
    }

    public function testConstructorWithDefaultValues(): void
    {
        $preferences = new ModelPreferences();

        $this->assertEquals(0.8, $preferences->getTemperature());
        $this->assertEquals(1.0, $preferences->getTopP());
        $this->assertNull($preferences->getMaxTokens());
        $this->assertEquals([], $preferences->getStop());
        $this->assertEquals(0.0, $preferences->getFrequencyPenalty());
        $this->assertEquals(0.0, $preferences->getPresencePenalty());
        $this->assertEquals(0.0, $preferences->getCostPriority());
        $this->assertEquals(0.0, $preferences->getIntelligencePriority());
        $this->assertEquals(0.0, $preferences->getSpeedPriority());
        $this->assertEquals([], $preferences->getHints());
    }

    public function testConstructorWithPartialParameters(): void
    {
        $preferences = new ModelPreferences(
            temperature: 0.5,
            cost_priority: 0.5
        );

        $this->assertEquals(0.5, $preferences->getTemperature());
        $this->assertEquals(1.0, $preferences->getTopP());
        $this->assertNull($preferences->getMaxTokens());
        $this->assertEquals([], $preferences->getStop());
        $this->assertEquals(0.0, $preferences->getFrequencyPenalty());
        $this->assertEquals(0.0, $preferences->getPresencePenalty());
        $this->assertEquals(0.5, $preferences->getCostPriority());
        $this->assertEquals(0.0, $preferences->getIntelligencePriority());
        $this->assertEquals(0.0, $preferences->getSpeedPriority());
        $this->assertEquals([], $preferences->getHints());
    }

    public function testCreateFromArray(): void
    {
        $preferences = ModelPreferences::create([
            'temperature' => 0.8,
            'top_p' => 0.9,
            'max_tokens' => 100,
            'stop' => ['stop'],
            'frequency_penalty' => 0.5,
            'presence_penalty' => 0.3,
            'cost_priority' => 0.5,
            'intelligence_priority' => 0.7,
            'speed_priority' => 0.3,
            'hints' => ['hint1', 'hint2'],
        ]);

        $this->assertEquals(0.8, $preferences->getTemperature());
        $this->assertEquals(0.9, $preferences->getTopP());
        $this->assertEquals(100, $preferences->getMaxTokens());
        $this->assertEquals(['stop'], $preferences->getStop());
        $this->assertEquals(0.5, $preferences->getFrequencyPenalty());
        $this->assertEquals(0.3, $preferences->getPresencePenalty());
        $this->assertEquals(0.5, $preferences->getCostPriority());
        $this->assertEquals(0.7, $preferences->getIntelligencePriority());
        $this->assertEquals(0.3, $preferences->getSpeedPriority());
        $this->assertEquals(['hint1', 'hint2'], $preferences->getHints());
    }

    public function testCreateFromEmptyArray(): void
    {
        $preferences = ModelPreferences::create([]);

        $this->assertEquals(0.8, $preferences->getTemperature());
        $this->assertEquals(1.0, $preferences->getTopP());
        $this->assertNull($preferences->getMaxTokens());
        $this->assertEquals([], $preferences->getStop());
        $this->assertEquals(0.0, $preferences->getFrequencyPenalty());
        $this->assertEquals(0.0, $preferences->getPresencePenalty());
        $this->assertEquals(0.0, $preferences->getCostPriority());
        $this->assertEquals(0.0, $preferences->getIntelligencePriority());
        $this->assertEquals(0.0, $preferences->getSpeedPriority());
        $this->assertEquals([], $preferences->getHints());
    }

    public function testCreateFromPartialArray(): void
    {
        $preferences = ModelPreferences::create([
            'temperature' => 0.5,
            'cost_priority' => 0.5,
        ]);

        $this->assertEquals(0.5, $preferences->getTemperature());
        $this->assertEquals(1.0, $preferences->getTopP());
        $this->assertNull($preferences->getMaxTokens());
        $this->assertEquals([], $preferences->getStop());
        $this->assertEquals(0.0, $preferences->getFrequencyPenalty());
        $this->assertEquals(0.0, $preferences->getPresencePenalty());
        $this->assertEquals(0.5, $preferences->getCostPriority());
        $this->assertEquals(0.0, $preferences->getIntelligencePriority());
        $this->assertEquals(0.0, $preferences->getSpeedPriority());
        $this->assertEquals([], $preferences->getHints());
    }
}

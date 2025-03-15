<?php

namespace LaravelMCP\MCP\Sampling;

/**
 * Configuration class for model sampling preferences in the MCP system.
 *
 * This class encapsulates various parameters that control how language models
 * generate responses. It allows fine-tuning of the sampling process to achieve
 * different types of outputs, from deterministic to highly creative.
 *
 * Preferences can control:
 * - Temperature (randomness)
 * - Top-p sampling
 * - Maximum tokens
 * - Stop sequences
 * - Frequency penalties
 * - Presence penalties
 *
 * @package LaravelMCP\MCP\Sampling
 */
class ModelPreferences
{
    /**
     * @var float The sampling temperature (0.0 to 2.0)
     */
    private float $temperature = 0.8;

    /**
     * @var float The nucleus sampling threshold (0.0 to 1.0)
     */
    private float $top_p = 1.0;

    /**
     * @var int|null Maximum number of tokens to generate
     */
    private ?int $max_tokens = null;

    /**
     * @var array<string> Sequences that will stop generation
     */
    private array $stop = [];

    /**
     * @var float Penalty for token frequency (0.0 to 2.0)
     */
    private float $frequency_penalty = 0.0;

    /**
     * @var float Penalty for token presence (0.0 to 2.0)
     */
    private float $presence_penalty = 0.0;

    /**
     * @var float Priority for cost optimization (0.0 to 1.0)
     */
    private float $cost_priority = 0.0;

    /**
     * @var float Priority for intelligence optimization (0.0 to 1.0)
     */
    private float $intelligence_priority = 0.0;

    /**
     * @var float Priority for speed optimization (0.0 to 1.0)
     */
    private float $speed_priority = 0.0;

    /**
     * @var array<string, mixed> Hints for the model
     */
    private array $hints = [];

    /**
     * Create a new model preferences instance.
     *
     * @param float|null $temperature Controls randomness in generation (0.0 to 2.0)
     * @param float|null $top_p Controls diversity via nucleus sampling (0.0 to 1.0)
     * @param int|null $max_tokens Maximum tokens to generate, null for no limit
     * @param array|null $stop Sequences that will stop generation
     * @param float|null $frequency_penalty Penalty for frequent tokens (0.0 to 2.0)
     * @param float|null $presence_penalty Penalty for token presence (0.0 to 2.0)
     * @param float|null $cost_priority Priority for cost optimization (0.0 to 1.0)
     * @param float|null $intelligence_priority Priority for intelligence optimization (0.0 to 1.0)
     * @param float|null $speed_priority Priority for speed optimization (0.0 to 1.0)
     * @param array|null $hints Hints for the model
     */
    public function __construct(
        ?float $temperature = null,
        ?float $top_p = null,
        ?int $max_tokens = null,
        ?array $stop = null,
        ?float $frequency_penalty = null,
        ?float $presence_penalty = null,
        ?float $cost_priority = null,
        ?float $intelligence_priority = null,
        ?float $speed_priority = null,
        ?array $hints = null
    ) {
        $this->temperature = $temperature ?? $this->temperature;
        $this->top_p = $top_p ?? $this->top_p;
        $this->max_tokens = $max_tokens ?? $this->max_tokens;
        $this->stop = $stop ?? $this->stop;
        $this->frequency_penalty = $frequency_penalty ?? $this->frequency_penalty;
        $this->presence_penalty = $presence_penalty ?? $this->presence_penalty;
        $this->cost_priority = $cost_priority ?? $this->cost_priority;
        $this->intelligence_priority = $intelligence_priority ?? $this->intelligence_priority;
        $this->speed_priority = $speed_priority ?? $this->speed_priority;
        $this->hints = $hints ?? $this->hints;
    }

    /**
     * Get the sampling temperature.
     *
     * Higher values make the output more random, while lower values
     * make it more deterministic.
     *
     * @return float The temperature value (0.0 to 2.0)
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }

    /**
     * Get the nucleus sampling threshold.
     *
     * Controls diversity via nucleus sampling. A value of 1.0 considers
     * all tokens, while lower values restrict to more likely tokens.
     *
     * @return float The top-p value (0.0 to 1.0)
     */
    public function getTopP(): float
    {
        return $this->top_p;
    }

    /**
     * Get the maximum number of tokens to generate.
     *
     * @return int|null The maximum tokens, or null for no limit
     */
    public function getMaxTokens(): ?int
    {
        return $this->max_tokens;
    }

    /**
     * Get the stop sequences.
     *
     * These sequences will cause the model to stop generating
     * when encountered.
     *
     * @return array<string> The stop sequences
     */
    public function getStop(): array
    {
        return $this->stop;
    }

    /**
     * Get the frequency penalty.
     *
     * Higher values reduce the likelihood of the model repeating
     * the same tokens frequently.
     *
     * @return float The frequency penalty (0.0 to 2.0)
     */
    public function getFrequencyPenalty(): float
    {
        return $this->frequency_penalty;
    }

    /**
     * Get the presence penalty.
     *
     * Higher values reduce the likelihood of the model repeating
     * any token that has appeared in the text so far.
     *
     * @return float The presence penalty (0.0 to 2.0)
     */
    public function getPresencePenalty(): float
    {
        return $this->presence_penalty;
    }

    /**
     * Get the hints for the model.
     *
     * @return array<string, mixed> The hints for the model
     */
    public function getHints(): array
    {
        return $this->hints;
    }

    /**
     * Set the hints for the model.
     *
     * @param array<string, mixed> $hints The new hints for the model
     */
    public function setHints(array $hints): void
    {
        $this->hints = $hints;
    }

    /**
     * Get the cost priority.
     *
     * @return float The cost priority (0.0 to 1.0)
     */
    public function getCostPriority(): float
    {
        return $this->cost_priority;
    }

    /**
     * Get the intelligence priority.
     *
     * @return float The intelligence priority (0.0 to 1.0)
     */
    public function getIntelligencePriority(): float
    {
        return $this->intelligence_priority;
    }

    /**
     * Get the speed priority.
     *
     * @return float The speed priority (0.0 to 1.0)
     */
    public function getSpeedPriority(): float
    {
        return $this->speed_priority;
    }

    /**
     * Convert the preferences to an array format.
     *
     * @return array The preferences as a key-value array
     */
    public function toArray(): array
    {
        return [
            'temperature' => $this->temperature,
            'top_p' => $this->top_p,
            'max_tokens' => $this->max_tokens,
            'stop' => $this->stop,
            'frequency_penalty' => $this->frequency_penalty,
            'presence_penalty' => $this->presence_penalty,
            'cost_priority' => $this->cost_priority,
            'intelligence_priority' => $this->intelligence_priority,
            'speed_priority' => $this->speed_priority,
            'hints' => $this->hints,
        ];
    }

    /**
     * Create a new instance from an array of data.
     *
     * @param array $values The values to create the instance from
     * @return static A new ModelPreferences instance
     */
    public static function create(array $values = []): static
    {
        return new static(
            $values['temperature'] ?? null,
            $values['top_p'] ?? null,
            $values['max_tokens'] ?? null,
            $values['stop'] ?? null,
            $values['frequency_penalty'] ?? null,
            $values['presence_penalty'] ?? null,
            $values['cost_priority'] ?? null,
            $values['intelligence_priority'] ?? null,
            $values['speed_priority'] ?? null,
            $values['hints'] ?? null
        );
    }
}

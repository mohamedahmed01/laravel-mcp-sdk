# ModelPreferences

Namespace: ``

Configuration class for model sampling preferences in the MCP system.

This class encapsulates various parameters that control how language models
generate responses. It allows fine-tuning of the sampling process to achieve
different types of outputs, from deterministic to highly creative.

Preferences can control:
- Temperature (randomness)
- Top-p sampling
- Maximum tokens
- Stop sequences
- Frequency penalties
- Presence penalties

@package LaravelMCP\MCP\Sampling

## Methods

### __construct

Create a new model preferences instance.

@param float|null $temperature Controls randomness in generation (0.0 to 2.0)
@param float|null $top_p Controls diversity via nucleus sampling (0.0 to 1.0)
@param int|null $max_tokens Maximum tokens to generate, null for no limit
@param array|null $stop Sequences that will stop generation
@param float|null $frequency_penalty Penalty for frequent tokens (0.0 to 2.0)
@param float|null $presence_penalty Penalty for token presence (0.0 to 2.0)
@param float|null $cost_priority Priority for cost optimization (0.0 to 1.0)
@param float|null $intelligence_priority Priority for intelligence optimization (0.0 to 1.0)
@param float|null $speed_priority Priority for speed optimization (0.0 to 1.0)
@param array|null $hints Hints for the model

### getTemperature

Get the sampling temperature.

Higher values make the output more random, while lower values
make it more deterministic.

@return float The temperature value (0.0 to 2.0)

### getTopP

Get the nucleus sampling threshold.

Controls diversity via nucleus sampling. A value of 1.0 considers
all tokens, while lower values restrict to more likely tokens.

@return float The top-p value (0.0 to 1.0)

### getMaxTokens

Get the maximum number of tokens to generate.

@return int|null The maximum tokens, or null for no limit

### getStop

Get the stop sequences.

These sequences will cause the model to stop generating
when encountered.

@return array<string> The stop sequences

### getFrequencyPenalty

Get the frequency penalty.

Higher values reduce the likelihood of the model repeating
the same tokens frequently.

@return float The frequency penalty (0.0 to 2.0)

### getPresencePenalty

Get the presence penalty.

Higher values reduce the likelihood of the model repeating
any token that has appeared in the text so far.

@return float The presence penalty (0.0 to 2.0)

### getHints

Get the hints for the model.

@return array<string, mixed> The hints for the model

### setHints

Set the hints for the model.

@param array<string, mixed> $hints The new hints for the model

### getCostPriority

Get the cost priority.

@return float The cost priority (0.0 to 1.0)

### getIntelligencePriority

Get the intelligence priority.

@return float The intelligence priority (0.0 to 1.0)

### getSpeedPriority

Get the speed priority.

@return float The speed priority (0.0 to 1.0)

### toArray

Convert the preferences to an array format.

@return array The preferences as a key-value array

### create

Create a new instance from an array of data.

@param array $values The values to create the instance from
@return static A new ModelPreferences instance


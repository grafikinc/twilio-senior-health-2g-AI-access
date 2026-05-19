<?php
/**
 * LLMClient — Talks to any OpenAI-compatible chat completions endpoint.
 * Works with: DeepSeek, OpenAI, OpenRouter, Ollama, LM Studio, etc.
 */
class LLMClient {

    private string $endpoint;
    private string $apiKey;
    private string $model;

    public function __construct(string $endpoint, string $apiKey, string $model) {
        $this->endpoint = $endpoint;
        $this->apiKey   = $apiKey;
        $this->model    = $model;
    }

    /**
     * Send a chat completion request and return the assistant's reply.
     * Returns null on failure.
     */
    public function chat(array $messages, float $temperature = 0.7, int $maxTokens = 150): ?string {
        $payload = json_encode([
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
        ]);

        $headers = ['Content-Type: application/json'];
        if ($this->apiKey) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 14,  // Twilio cuts off at ~15s
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("[sms-companion] cURL error: {$error}");
            return null;
        }

        if ($httpCode !== 200) {
            error_log("[sms-companion] LLM API returned HTTP {$httpCode}: " . substr($response, 0, 300));
            return null;
        }

        $decoded = json_decode($response, true);
        return $decoded['choices'][0]['message']['content'] ?? null;
    }
}

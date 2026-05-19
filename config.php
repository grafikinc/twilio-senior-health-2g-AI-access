<?php
/**
 * Configuration — reads from .env file or environment variables.
 * Copy .env.example → .env and fill in your values.
 */

// Load .env if it exists (no Composer dependency needed)
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            // Strip surrounding quotes
            $value = trim($value, '"\'');
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// ── LLM Provider ───────────────────────────────────────────
// Any OpenAI-compatible endpoint works: DeepSeek, OpenRouter,
// Ollama (http://localhost:11434/v1/chat/completions), etc.
define('LLM_ENDPOINT', $_ENV['LLM_ENDPOINT'] ?? 'https://api.deepseek.com/v1/chat/completions');
define('LLM_API_KEY',  $_ENV['LLM_API_KEY']  ?? '');
define('LLM_MODEL',    $_ENV['LLM_MODEL']    ?? 'deepseek-chat');

// ── SMS Gateway ────────────────────────────────────────────
// Supported: 'twilio', 'vonage', 'generic'
// 'generic' expects POST with 'From' and 'Body' fields, returns plain text.
define('GATEWAY_TYPE', $_ENV['GATEWAY_TYPE'] ?? 'twilio');

// ── Conversation Storage ───────────────────────────────────
define('STORE_PATH',       __DIR__ . '/data');
define('CONVERSATION_TTL', (int)($_ENV['CONVERSATION_TTL'] ?? 3600)); // seconds
define('MAX_HISTORY',      (int)($_ENV['MAX_HISTORY']      ?? 20));   // message pairs

// ── Profiles ───────────────────────────────────────────────
define('PROFILES_PATH', __DIR__ . '/profiles');

// ── Defaults ───────────────────────────────────────────────
define('DEFAULT_PERSONA',   $_ENV['DEFAULT_PERSONA']   ?? 'an empathetic, patient, and warm care companion');
define('DEFAULT_MAX_CHARS', (int)($_ENV['DEFAULT_MAX_CHARS'] ?? 300));

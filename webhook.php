<?php
/**
 * SMS AI Companion — Webhook Entry Point
 *
 * Receives inbound SMS via any gateway webhook, routes through
 * an LLM, and returns a reply in the gateway's expected format.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/LLMClient.php';
require_once __DIR__ . '/src/ConversationStore.php';
require_once __DIR__ . '/src/Gateway.php';

// ── Bootstrap ──────────────────────────────────────────────
$gateway = new Gateway(GATEWAY_TYPE);
$store   = new ConversationStore(STORE_PATH, CONVERSATION_TTL, MAX_HISTORY);
$llm     = new LLMClient(LLM_ENDPOINT, LLM_API_KEY, LLM_MODEL);

// ── Parse inbound message ──────────────────────────────────
$from = $gateway->getSender($_POST);
$body = $gateway->getBody($_POST);

if (!$from || trim($body) === '') {
    $gateway->reply('Sorry, I didn\'t catch that. Could you try again?');
}

// ── Hard reset ─────────────────────────────────────────────
if (in_array(strtolower(trim($body)), ['reset', 'start over', 'clear'])) {
    $store->clear($from);
    $gateway->reply("Session reset. Text me anytime to start a new conversation.");
}

// ── Load patient profile ───────────────────────────────────
$profile     = loadProfile($from);
$systemPrompt = buildSystemPrompt($profile);

// ── Build conversation ─────────────────────────────────────
$history  = $store->load($from);
$messages = [['role' => 'system', 'content' => $systemPrompt]];
foreach ($history as $msg) {
    $messages[] = $msg;
}
$messages[] = ['role' => 'user', 'content' => $body];

// ── Call LLM ───────────────────────────────────────────────
$aiReply = $llm->chat($messages);

if (!$aiReply) {
    $gateway->reply("I'm having a little trouble right now. Try again in a moment?");
}

// ── Persist & respond ──────────────────────────────────────
$store->append($from, 'user', $body);
$store->append($from, 'assistant', $aiReply);

$gateway->reply($aiReply);


// ═══════════════════════════════════════════════════════════
// Helpers
// ═══════════════════════════════════════════════════════════

/**
 * Load a patient/user profile by phone number.
 * Falls back to a generic companion if no profile exists.
 */
function loadProfile(string $phone): array {
    $file = PROFILES_PATH . '/' . preg_replace('/[^0-9+]/', '', $phone) . '.json';

    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true) ?? [];
    }

    // Fallback: generic companion (no patient-specific data)
    return [
        'name'        => 'Friend',
        'context'     => '',
        'persona'     => DEFAULT_PERSONA,
        'max_chars'   => DEFAULT_MAX_CHARS,
    ];
}

/**
 * Build the system prompt from a profile array.
 */
function buildSystemPrompt(array $p): string {
    $persona  = $p['persona']    ?? DEFAULT_PERSONA;
    $maxChars = $p['max_chars']  ?? DEFAULT_MAX_CHARS;
    $name     = $p['name']       ?? 'Friend';
    $context  = $p['context']    ?? '';

    $prompt = <<<PROMPT
You are {$persona}.
Keep responses short (max {$maxChars} characters) — these are SMS messages on basic phones.
Address the user as {$name}. Ask warm, engaging follow-up questions.
If the user mentions severe depression, self-harm, or suicide, immediately provide the 988 Suicide & Crisis Lifeline (call or text 988).
PROMPT;

    if ($context) {
        $prompt .= "\n\nUSER CONTEXT:\n{$context}\nUse this context naturally — don't recite it, weave it into conversation when relevant.";
    }

    return $prompt;
}

<?php
/**
 * ConversationStore — File-based conversation history keyed by phone number.
 *
 * Each sender gets a JSON file in the data/ directory.
 * No database required — works on any shared hosting.
 */
class ConversationStore {

    private string $dir;
    private int    $ttl;
    private int    $maxPairs;

    public function __construct(string $directory, int $ttlSeconds = 3600, int $maxPairs = 20) {
        $this->dir      = rtrim($directory, '/');
        $this->ttl      = $ttlSeconds;
        $this->maxPairs = $maxPairs;

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0750, true);
        }
    }

    /**
     * Load conversation history for a sender. Returns empty array if
     * the conversation has expired or doesn't exist.
     */
    public function load(string $sender): array {
        $file = $this->path($sender);

        if (!file_exists($file)) {
            return [];
        }

        $data = json_decode(file_get_contents($file), true);

        if (!$data || !isset($data['updated_at'])) {
            return [];
        }

        // TTL check
        if ((time() - $data['updated_at']) > $this->ttl) {
            $this->clear($sender);
            return [];
        }

        return $data['messages'] ?? [];
    }

    /**
     * Append a message to the sender's history.
     */
    public function append(string $sender, string $role, string $content): void {
        $history = $this->load($sender);
        $history[] = ['role' => $role, 'content' => $content];

        // Trim to max pairs (keep system-relevant recent context)
        $maxMessages = $this->maxPairs * 2;
        if (count($history) > $maxMessages) {
            $history = array_slice($history, -$maxMessages);
        }

        $data = [
            'updated_at' => time(),
            'messages'   => array_values($history),
        ];

        file_put_contents($this->path($sender), json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    }

    /**
     * Clear conversation for a sender.
     */
    public function clear(string $sender): void {
        $file = $this->path($sender);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Sanitize phone number into a safe filename.
     */
    private function path(string $sender): string {
        $safe = preg_replace('/[^0-9+]/', '', $sender);
        return $this->dir . '/' . $safe . '.json';
    }
}

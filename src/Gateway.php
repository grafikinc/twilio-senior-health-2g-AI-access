<?php
/**
 * Gateway — Abstracts SMS provider differences.
 *
 * Handles parsing inbound webhooks and formatting replies for:
 *   - Twilio   (TwiML response)
 *   - Vonage   (JSON response)
 *   - Generic  (plain POST in, plain text out — for DIY gateways, Gammu, etc.)
 */
class Gateway {

    private string $type;

    public function __construct(string $type = 'twilio') {
        $this->type = strtolower($type);
    }

    /**
     * Extract the sender's phone number from the inbound POST data.
     */
    public function getSender(array $post): ?string {
        return match ($this->type) {
            'twilio'  => $post['From'] ?? null,
            'vonage'  => $post['msisdn'] ?? null,
            'generic' => $post['from'] ?? $post['From'] ?? null,
            default   => $post['From'] ?? null,
        };
    }

    /**
     * Extract the message body from the inbound POST data.
     */
    public function getBody(array $post): string {
        return match ($this->type) {
            'twilio'  => trim($post['Body'] ?? ''),
            'vonage'  => trim($post['text'] ?? ''),
            'generic' => trim($post['body'] ?? $post['Body'] ?? ''),
            default   => trim($post['Body'] ?? ''),
        };
    }

    /**
     * Send the reply in the format the gateway expects, then exit.
     */
    public function reply(string $message): never {
        match ($this->type) {
            'twilio'  => $this->replyTwiML($message),
            'vonage'  => $this->replyJSON($message),
            'generic' => $this->replyPlain($message),
            default   => $this->replyPlain($message),
        };
        exit;
    }

    private function replyTwiML(string $message): void {
        header('Content-Type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<Response><Message>' . htmlspecialchars($message) . '</Message></Response>';
    }

    private function replyJSON(string $message): void {
        header('Content-Type: application/json');
        echo json_encode(['message' => $message]);
    }

    private function replyPlain(string $message): void {
        header('Content-Type: text/plain');
        echo $message;
    }
}

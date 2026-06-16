# twilio-senior-health-2g-AI-access

**AI companionship for older adults, delivered over SMS to any phone.**

A lightweight gateway that connects basic phones to modern LLMs, purpose-built for the senior isolation crisis. No smartphone. No app. No internet. Just text.

---

## The Problem

Social isolation among older adults is a public health epidemic. The [U.S. Surgeon General's 2023 advisory](https://www.hhs.gov/about/news/2023/05/03/new-surgeon-general-advisory-raises-alarm-about-devastating-impact-epidemic-loneliness-isolation-united-states.html) found that lacking social connection increases risk of premature death by more than 60%, comparable to smoking 15 cigarettes a day. For older adults specifically:

- **50%** increased risk of developing dementia
- **29%** increased risk of heart disease
- **32%** increased risk of stroke
- **$406 billion** annual cost to the U.S. economy (CDC estimate)
- **$6.7 billion** per year in Medicare spending on socially isolated seniors

90% of seniors live with chronic conditions. 40% report feeling isolated. Older adults have the highest suicide rate of any age group in the United States.

The demographic math compounds the crisis: 10,000 Americans turn 65 every day, community infrastructure is thinning, and the caregiver-to-senior ratio is shrinking.

> "The CDC estimates that loneliness costs the U.S. economy an estimated $406 billion a year, in addition to the estimated $6.7 billion a year in Medicare costs for socially isolated older adults."
> (New York State Office for the Aging)

## What's Being Done

**[NYSOFA + ElliQ](https://aging.ny.gov/)** (New York State) deployed roughly 900 AI companion robots to older adults and saw a 95% reduction in loneliness, with 30+ daily interactions sustained over time. The results validated the core thesis: AI companionship works for seniors. But hardware distribution, county-by-county rollout, and state budget constraints create a hard ceiling on reach.

**[Sailor Health](https://sailorhealth.com/)** is building the AI-native health system for aging, covering virtual mental health, care navigation, and AI coaches, and is doubling every 75 days across 43 states. But like all telehealth, it assumes a smartphone, an internet connection, and digital literacy.

The gap: roughly 30 million U.S. adults over 65 don't own a smartphone. They all have phones. They all know how to text. Neither a robot in a box nor an app in a store can reach them.

## What This Is

A PHP gateway that turns any SMS-capable phone into an AI companion for an older adult. A caregiver, case manager, or Area Agency on Aging creates a simple profile (name, context, preferences), points an SMS number at the webhook, and the person can text back and forth with a warm, context-aware AI companion that knows their name, asks about their garden, and remembers they take Lisinopril in the morning.

```
┌──────────┐     SMS      ┌──────────────┐    HTTP     ┌─────────┐
│  Any     │ ──────────►  │  SMS Gateway  │ ─────────► │ webhook │
│  Phone   │ ◄──────────  │  (any provider)│ ◄───────── │   .php  │
└──────────┘              └──────────────┘             └────┬────┘
                                                           │
                                              ┌────────────┼────────────┐
                                              ▼            ▼            ▼
                                         ┌────────┐  ┌─────────┐  ┌────────┐
                                         │ Profile │  │ History │  │  LLM   │
                                         │  .json  │  │  Store  │  │  API   │
                                         └────────┘  └─────────┘  └────────┘
```

1. Senior sends a text; the SMS gateway forwards it as an HTTP POST
2. `webhook.php` identifies the sender by phone number
3. Loads their profile (name, health context, preferences) and conversation history
4. Sends the full context to any OpenAI-compatible LLM
5. Returns a short, warm reply as SMS

The AI knows who it's talking to. It asks follow-up questions. It weaves in context naturally: not "your file says you have arthritis," but "how are your hands feeling today? Good enough for some knitting?"

If the person mentions severe depression, self-harm, or suicidal thoughts, the system immediately provides the 988 Suicide & Crisis Lifeline.

## Origin

This project is a direct branch of [AgroFutures](https://github.com/grafikinc/africas-talking-agtech/), an AI advisory system built for smallholder farmers and fishermen on feature phones in East Africa, delivered over USSD and voice through Africa's Talking.

Same thesis, two continents: **the people most excluded from AI are the ones who need it most.** In coastal Kenya, that's a fisherman on a $15 Nokia who needs to know if it's safe to go out. In New York, that's a 78-year-old widow on a flip phone who hasn't spoken to anyone in three days.

The architecture is deliberately simple (PHP, flat files, no frameworks) because the deployment targets are the same: underfunded organizations running on shared hosting, serving populations that the app-store economy has written off.

## Quick Start

### Requirements

- PHP 7.4+ with cURL (any shared hosting works)
- An SMS gateway account (Twilio, Vonage, or any webhook-capable provider)
- An LLM API key (DeepSeek, OpenAI, OpenRouter, or self-hosted Ollama)

### Setup

```bash
git clone https://github.com/grafikinc/twilio-senior-health-2g-AI-access.git
cd twilio-senior-health-2g-AI-access

cp .env.example .env
# Edit .env with your LLM key and gateway choice

mkdir -p data && chmod 750 data

# Optional: create a profile for your first user
cp profiles/example.json profiles/+15551234567.json
```

### Point Your Gateway

Set your SMS provider's webhook URL to:

```
https://your-server.com/webhook.php
```

| Provider | Where to Set It |
|----------|----------------|
| Twilio   | Phone Number > Messaging > "A message comes in" > Webhook |
| Vonage   | Applications > Your App > Inbound URL |
| Generic  | POST `from` and `body` fields to the webhook |

### Test Without a Real SMS Number

You can test the full flow locally using `curl` before touching any gateway config:

**Twilio format:**
```bash
curl -X POST http://localhost/webhook.php \
  -d "From=%2B15551234567&Body=Hello%2C+how+are+you+today"
```

**Vonage format:**
```bash
curl -X POST http://localhost/webhook.php \
  -d "msisdn=15551234567&text=Hello%2C+how+are+you+today"
```

**Generic format:**
```bash
curl -X POST http://localhost/webhook.php \
  -d "from=%2B15551234567&body=Hello%2C+how+are+you+today"
```

A successful response returns TwiML XML (or JSON/plain text depending on `GATEWAY_TYPE`). Test the reset command with `Body=reset`. To simulate a profile lookup, name your profile file `+15551234567.json` and use `From=%2B15551234567`.

### Test With a Real Number

Text your number. You should get a reply in a few seconds. Text `reset` to clear conversation history.

## Configuration

All config lives in `.env` (never committed):

| Variable | Description | Default |
|----------|-------------|---------|
| `LLM_ENDPOINT` | Any OpenAI-compatible URL | DeepSeek |
| `LLM_API_KEY` | Your API key | — |
| `LLM_MODEL` | Model identifier | `deepseek-chat` |
| `GATEWAY_TYPE` | `twilio`, `vonage`, or `generic` | `twilio` |
| `CONVERSATION_TTL` | Session timeout (seconds) | `3600` |
| `MAX_HISTORY` | Message pairs to keep | `20` |
| `DEFAULT_PERSONA` | Companion personality | Warm care companion |
| `DEFAULT_MAX_CHARS` | Reply length cap | `300` |

## User Profiles

JSON files in `profiles/` named by phone number (e.g., `+15551234567.json`):

```json
{
    "name": "Shirley",
    "persona": "an empathetic, warm companion who feels like a trusted friend",
    "max_chars": 280,
    "context": "Age: 78\nLives alone in NYC, recent widow\nMedications: Lisinopril (morning)\nHobbies: Gardening, knitting\nNotes: Feels lonely in the evenings"
}
```

No profile? The companion still works, just without personal context.

## Project Structure

```
├── webhook.php              # Entry point
├── config.php               # Reads .env
├── src/
│   ├── LLMClient.php        # OpenAI-compatible API client
│   ├── ConversationStore.php # File-based history (keyed by phone number)
│   └── Gateway.php          # SMS provider abstraction
├── profiles/                # Per-user context (*.json)
├── data/                    # Conversation history (gitignored)
└── .env.example
```

## Who This Is For

- **Area Agencies on Aging** — the local arms of state aging programs (like NYSOFA), serving seniors who won't use apps but will text
- **Home health aides and caregivers** — a companion that checks in between visits
- **Senior centers and faith communities** — low-cost outreach for homebound members
- **Developers building for underserved populations** — fork it, adapt the profiles, deploy for your community

## Roadmap

### Voice Callbacks (blocked — needs A2P registration)

The natural next step is voice delivery: an automated call that reads the AI's response aloud, the same way [AgroFutures delivers advisories](https://github.com/grafikinc/africas-talking-agtech/) to farmers in East Africa. Many seniors would prefer a phone call to a text, and voice eliminates the literacy and vision barrier entirely.

The architecture for this already exists in the AgroFutures codebase (TTS callbacks via Africa's Talking). Porting it to Twilio or Vonage voice APIs is straightforward.

**What's blocking it:** U.S. carriers require [A2P 10DLC registration](https://www.twilio.com/docs/messaging/guides/10dlc) for any application-to-person messaging or calling. Without a registered campaign (which requires a verified business, use-case approval, and carrier vetting), outbound messages and calls get silently filtered as spam. This registration process is designed for enterprises, not open-source projects or small nonprofits, which is ironic given that the people this project serves are exactly the ones the carrier ecosystem can't reach.

If you have a registered A2P campaign, or work at an organization that does (an Area Agency on Aging, a health system, a senior services nonprofit), you could add voice delivery in a weekend. PRs welcome.

### Other planned features

- **Proactive check-ins** — scheduled outbound messages ("Good morning Shirley, did you take your Lisinopril?") rather than waiting for the senior to initiate
- **Caregiver dashboard** — a simple web view so a family member or case manager can see conversation summaries and flag concerns
- **Multi-language support** — Spanish, Mandarin, Cantonese for the populations that need it most
- **Medication and appointment reminders** — profile-driven scheduled messages

## Limitations

- **SMS carrier regulations:** U.S. carriers enforce A2P 10DLC registration for application-to-person messaging. Register your number properly with your gateway provider or messages will be filtered as spam.
- **SMS is not encrypted:** Don't store sensitive medical data in profiles unless you understand the HIPAA implications for your use case.
- **Latency:** LLM response + gateway round-trip = 3-8 seconds. Acceptable for SMS, where expectations are already asynchronous.
- **Flat-file storage:** Conversation history uses JSON files. For production at scale, swap `ConversationStore` for a database-backed implementation.

## Related Work

- [AgroFutures USSD/Voice](https://github.com/grafikinc/africas-talking-agtech/) — the East Africa branch of this architecture (feature phones, USSD, voice callbacks)
- [NYSOFA + ElliQ Report (2023)](https://aging.ny.gov/system/files/documents/2023/08/nysofa-and-elliq-engagement-report-july-2023.pdf) — validation that AI companionship reduces senior loneliness by 95%
- [NYSOFA + ElliQ Report (2026)](http://aging.ny.gov/transforming-care-older-adults-three-years-success-ny-older-adults-living-elliq) — three-year outcomes data
- [Surgeon General Advisory on Social Isolation (2023)](https://www.hhs.gov/about/news/2023/05/03/new-surgeon-general-advisory-raises-alarm-about-devastating-impact-epidemic-loneliness-isolation-united-states.html)

## License

MIT — see [LICENSE](LICENSE)

---

For partnerships, technical questions, or production deployment support:

- Email: [jason@mcguiness.design](mailto:jason@mcguiness.design)
- Consulting: [grafikinc.com](https://grafikinc.com)
- Portfolio: [mcguiness.design](https://mcguiness.design)

*"The people most excluded from AI are the ones who need it most."*

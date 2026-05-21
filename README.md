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

**[NYSOFA + ElliQ](https://aging.ny.gov/)** (New York State) deployed ~900 AI companion robots to older adults, demonstrating a 95% reduction in loneliness and 30+ daily interactions sustained over time. The results validated the core thesis: AI companionship works for seniors. But hardware distribution, county-by-county rollout, and state budget constraints create a hard ceiling on reach.

**[Sailor Health](https://sailorhealth.com/)** is building the AI-native health system for aging (virtual mental health, care navigation, AI coaches), doubling every 75 days across 43 states. But like all telehealth, it assumes a smartphone, an internet connection, and digital literacy.

**The gap:** roughly 30 million U.S. adults over 65 don't own a smartphone. They all have phones. They all know how to text. Neither a robot in a box nor an app in a store can reach them.

## What This Is

A PHP gateway that turns any SMS-capable phone into an AI companion for an older adult. A caregiver, case manager, or Area Agency on Aging creates a simple profile (name, context, preferences), points an SMS number at the webhook, and the person can text back and forth with a warm, context-aware AI companion that knows their name, asks about their garden, and remembers they take Lisinopril in the morning.

<?php

namespace App\Services;

use App\Models\CrabSpecies;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CrabInfoChatService
{
    public function answer(string $message): array
    {
        $message = trim($message);
        if ($message === '') throw new RuntimeException('Please enter a crab question.');

        if (! $this->isCrabQuestion($message)) {
            return [
                'answer' => 'I can only help with crab species, crab identification, habitats, visual traits, handling cautions, and crab-related research information.',
                'provider' => 'guardrail',
                'model' => 'crab-only',
                'suggestions' => $this->suggestions(),
            ];
        }

        $errors = [];
        foreach (array_filter(config('services.ai.provider_order', []), fn ($provider) => $provider !== 'local') as $provider) {
            try {
                return match ($provider) {
                    'gemini' => $this->withGemini($message),
                    'anthropic' => $this->withAnthropic($message),
                    'groq' => $this->withOpenAiCompatible($message, 'groq', 'https://api.groq.com/openai/v1/chat/completions'),
                    'openrouter' => $this->withOpenAiCompatible($message, 'openrouter', 'https://openrouter.ai/api/v1/chat/completions'),
                    'cohere' => $this->withCohere($message),
                    'wisdomgate' => $this->withOpenAiCompatible($message, 'wisdomgate', 'https://api.wisdomgate.ai/v1/chat/completions'),
                    default => throw new RuntimeException("Unknown AI provider [{$provider}]."),
                };
            } catch (Throwable $e) {
                report($e);
                $errors[] = "{$provider}: {$e->getMessage()}";
            }
        }

        throw new RuntimeException('Crab chatbot providers are unavailable. '.implode(' | ', $errors));
    }

    private function withGemini(string $message): array
    {
        $key = (string) config('services.ai.providers.gemini.key');
        $model = (string) config('services.ai.providers.gemini.model');
        if ($key === '' || $model === '') throw new RuntimeException('Gemini is not configured.');

        $response = $this->client()
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}", [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $this->prompt($message)]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'response_mime_type' => 'application/json',
                ],
            ]);

        return $this->normalize(data_get($this->json($response), 'candidates.0.content.parts.0.text'), 'gemini', $model);
    }

    private function withAnthropic(string $message): array
    {
        $key = (string) config('services.ai.providers.anthropic.key');
        $model = (string) config('services.ai.providers.anthropic.model');
        if ($key === '' || $model === '') throw new RuntimeException('Anthropic is not configured.');

        $response = $this->client()
            ->withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => (string) config('services.ai.providers.anthropic.version', '2023-06-01'),
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 900,
                'temperature' => 0.2,
                'messages' => [[
                    'role' => 'user',
                    'content' => [['type' => 'text', 'text' => $this->prompt($message)]],
                ]],
            ]);

        return $this->normalize(data_get($this->json($response), 'content.0.text'), 'anthropic', $model);
    }

    private function withOpenAiCompatible(string $message, string $provider, string $url): array
    {
        $key = (string) config("services.ai.providers.{$provider}.key");
        $model = (string) config("services.ai.providers.{$provider}.model");
        if ($key === '' || $model === '') throw new RuntimeException("{$provider} is not configured.");

        $headers = ['Authorization' => "Bearer {$key}"];
        if ($provider === 'openrouter') {
            $headers += ['HTTP-Referer' => config('app.url'), 'X-Title' => config('app.name')];
        }

        $response = $this->client()
            ->withHeaders($headers)
            ->post($url, [
                'model' => $model,
                'temperature' => 0.2,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

        return $this->normalize(data_get($this->json($response), 'choices.0.message.content'), $provider, $model);
    }

    private function withCohere(string $message): array
    {
        $key = (string) config('services.ai.providers.cohere.key');
        $model = (string) config('services.ai.providers.cohere.model');
        if ($key === '' || $model === '') throw new RuntimeException('Cohere is not configured.');

        $response = $this->client()
            ->withToken($key)
            ->post('https://api.cohere.com/v2/chat', [
                'model' => $model,
                'temperature' => 0.2,
                'messages' => [[
                    'role' => 'user',
                    'content' => [['type' => 'text', 'text' => $this->prompt($message)]],
                ]],
            ]);

        $payload = $this->json($response);

        return $this->normalize(data_get($payload, 'message.content.0.text') ?? data_get($payload, 'text'), 'cohere', $model);
    }

    private function normalize(?string $text, string $provider, string $model): array
    {
        $data = $this->decodeJsonText($text);
        $answer = trim((string) ($data['answer'] ?? ''));
        if ($answer === '') throw new RuntimeException('Provider returned an empty crab answer.');

        return [
            'answer' => $answer,
            'provider' => $provider,
            'model' => $model,
            'suggestions' => array_slice(array_values(array_filter((array) ($data['suggestions'] ?? []))), 0, 3) ?: $this->suggestions(),
        ];
    }

    private function client(): PendingRequest
    {
        return Http::timeout((int) config('services.ai.timeout', 60))->retry(1, 250)->acceptJson();
    }

    private function json($response): array
    {
        if (! $response->successful()) {
            throw new RuntimeException('Provider request failed with HTTP '.$response->status().'.');
        }

        $payload = $response->json();
        if (! is_array($payload)) throw new RuntimeException('Provider returned a non-JSON response.');

        return $payload;
    }

    private function decodeJsonText(?string $text): array
    {
        if (! is_string($text) || trim($text) === '') throw new RuntimeException('Provider returned an empty answer.');

        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $clean) ?: $clean;
        if (! str_starts_with($clean, '{')) {
            preg_match('/\{.*\}/s', $clean, $match);
            $clean = $match[0] ?? $clean;
        }

        $data = json_decode($clean, true);
        if (! is_array($data)) throw new RuntimeException('Provider did not return valid chatbot JSON.');

        return $data;
    }

    private function isCrabQuestion(string $message): bool
    {
        $haystack = strtolower($message.' '.$this->speciesContext());
        $messageKey = strtolower($message);
        $keywords = ['crab', 'crabs', 'mud crab', 'swimming crab', 'species', 'carapace', 'claw', 'claws', 'habitat', 'molt', 'molting', 'taxonomy', 'brachyura', 'portunidae', 'scylla', 'portunus', 'callinectes', 'cancer magister', 'carcinus', 'paralithodes', 'charybdis'];

        foreach ($keywords as $keyword) {
            if (str_contains($messageKey, $keyword)) return true;
        }

        return CrabSpecies::query()
            ->where('is_active', true)
            ->get(['common_name', 'scientific_name'])
            ->contains(fn (CrabSpecies $species) => str_contains($messageKey, strtolower($species->common_name)) || str_contains($messageKey, strtolower($species->scientific_name)));
    }

    private function prompt(string $message): string
    {
        return $this->systemPrompt()."\n\nUser question: {$message}";
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
You are CrabAI Chat, a crab-only information assistant. Answer only questions about crabs: crab species, identification, anatomy, habitat, behavior, visual traits, taxonomy, fisheries, handling cautions, and food-safety caveats. If the user asks about anything unrelated to crabs, politely refuse and redirect to crab information.

Use this local reference library when relevant:
{$this->speciesContext()}

Return only valid JSON with this exact shape:
{
  "answer": "concise helpful answer focused only on crab information",
  "suggestions": ["short follow-up crab question", "short follow-up crab question", "short follow-up crab question"]
}
Do not provide legal, medical, food-safety, or scientific certification. Keep answers practical and clear for mobile users.
PROMPT;
    }

    private function speciesContext(): string
    {
        return CrabSpecies::query()
            ->where('is_active', true)
            ->orderBy('common_name')
            ->get(['common_name', 'scientific_name', 'family', 'habitat', 'visual_characteristics'])
            ->map(fn (CrabSpecies $species) => "- {$species->common_name} ({$species->scientific_name}), {$species->family}: {$species->visual_characteristics} Habitat: {$species->habitat}")
            ->implode("\n");
    }

    private function suggestions(): array
    {
        return [
            'How can I identify a mud crab?',
            'What habitats do swimming crabs prefer?',
            'What crab traits should I photograph?',
        ];
    }
}

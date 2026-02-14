<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'subject',
        'greeting',
        'body',
        'action_text',
        'action_url',
        'outro',
        'available_variables',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'available_variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get a template by its key, or null if not found/inactive.
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->where('is_active', true)->first();
    }

    /**
     * Render a text string by replacing {{variable}} placeholders with values.
     */
    public static function render(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', (string) $value, $text);
        }

        return $text;
    }

    /**
     * Get the rendered subject with variables replaced.
     */
    public function renderSubject(array $variables): string
    {
        return static::render($this->subject, $variables);
    }

    /**
     * Get the rendered greeting with variables replaced.
     */
    public function renderGreeting(array $variables): string
    {
        return static::render($this->greeting ?? 'Hello!', $variables);
    }

    /**
     * Get the rendered body lines with variables replaced.
     * Body is stored as newline-separated lines.
     */
    public function renderBodyLines(array $variables): array
    {
        return collect(explode("\n", $this->body))
            ->map(fn ($line) => static::render(trim($line), $variables))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->toArray();
    }

    /**
     * Get the rendered action text.
     */
    public function renderActionText(array $variables): ?string
    {
        return $this->action_text ? static::render($this->action_text, $variables) : null;
    }

    /**
     * Get the rendered action URL.
     */
    public function renderActionUrl(array $variables): ?string
    {
        return $this->action_url ? static::render($this->action_url, $variables) : null;
    }

    /**
     * Get the rendered outro with variables replaced.
     */
    public function renderOutro(array $variables): ?string
    {
        return $this->outro ? static::render($this->outro, $variables) : null;
    }
}

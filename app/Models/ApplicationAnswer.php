<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationAnswer extends Model
{
    protected $guarded = [];

    protected $casts = ['answer' => 'array'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function getAnswerPlainAttribute(): string
    {
        $a = $this->answer;

        if (is_null($a)) return '—';
        if (is_string($a)) return $a;

        if (array_key_exists('value', $a)) {
            return is_array($a['value'])
                ? implode(', ', array_map(
                    fn($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE),
                    $a['value']
                ))
                : (string) $a['value'];
        }

        if (isset($a['text']))   return (string) $a['text'];
        if (isset($a['number'])) return (string) $a['number'];

        return is_array($a)
            ? implode(', ', array_map(
                fn($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE),
                array_values($a)
            ))
            : (string) $a;
    }
}

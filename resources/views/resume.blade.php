<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Rezyume — {{ $app->full_name }}</title>
    <style>
        @page { margin: 20mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; line-height: 1.5; }
        .topbar { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .topbar .left { font-size: 11px; color: #777; }
        .topbar .right { text-align: right; }
        .topbar img { width: 45px; height: 45px; border-radius: 50%; }
        h1 { font-size: 22px; font-weight: 700; border-bottom: 2px dotted #d33; margin-bottom: 8px; padding-bottom: 4px; }
        h2 { font-size: 14px; color: #d33; margin-top: 24px; border-bottom: 1px solid #ddd; padding-bottom: 2px; }
        h3 { font-size: 13px; font-weight: bold; margin-bottom: 3px; }
        h4 { font-size: 13px; font-weight: bold; margin: 10px 0 6px; }
        .info div { margin-bottom: 4px; }
        .muted { color: #777; }
        ul { margin: 4px 0 0 20px; }
        li { margin-bottom: 3px; }
        hr { border: 0; border-top: 1px solid #eee; margin: 10px 0; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="right">
        <div>{{ now()->format('d F Y') }}</div>
        <img src="{{ public_path('logo.png') }}" alt="Logo">
    </div>
</div>

<h1>{{ $app->full_name }}</h1>

<div class="info">
    {{ \App\Enum\Gender::name((int)$app->sex) }},
    tug‘ilgan sana:
    {{ $app->birth_date ? \Carbon\Carbon::parse($app->birth_date)->format('d.m.Y') : '—' }}<br>
    {{ $app->phone }}<br>
    Yashash joyi: {{ $app->region?->name ?? '—' }}, {{ $app->district?->name ?? '—' }}<br>
    Millati: {{ \App\Enum\Natinalities::name((int)$app->nationality) }}<br>
</div>

@if($app->experiences->count())
    <h2>Ish tajribasi</h2>
    @foreach($app->experiences as $exp)
        <h3>{{ $exp->where_work ?? '—' }}</h3>
        <div class="muted">{{ $exp->duration ?? '—' }}</div>
        <div><b>{{ $exp->what_job ?? '—' }}</b></div>
        <hr>
    @endforeach
@endif

@if($app->educations->count())
    <h2>Ta’lim</h2>
    @foreach($app->educations as $edu)
        <div><b>Daraja:</b> {{ $edu->study_level ?? '—' }}</div>
        <div><b>Yo‘nalish:</b> {{ $edu->direction ?? '—' }}</div>
        <div><b>O‘qigan joyi:</b> {{ $edu->where_study ?? '—' }}</div>
        <hr>
    @endforeach
@endif

@if($app->languages->count())
    <h2>Ko‘nikmalar</h2>
    <div><b>Tillarni bilish:</b></div>
    <ul>
        @foreach($app->languages as $languageItem)
            <li>
                {{ \App\Enum\Lang::name($languageItem->name) }}
                — {{ \App\Enum\LanguageLevel::name($languageItem->level) }}
            </li>
        @endforeach
    </ul>
@endif
@php
    $printAnswer = function ($a) {
        if ($a === null || $a === '') return '—';

        // If JSON string → decode
        if (is_string($a)) {
            $t = trim($a);
            if ($t !== '' && ($t[0] === '{' || $t[0] === '[')) {
                $decoded = json_decode($a, true);
                if (json_last_error() === JSON_ERROR_NONE) $a = $decoded;
            } else {
                return $a; // plain text
            }
        }

        if (is_array($a)) {
            // common shape: {"value": ...}
            if (array_key_exists('value', $a)) {
                $v = $a['value'];
                if (is_array($v)) return implode(', ', array_filter($v));
                if ($v === true) return 'Ha';
                if ($v === false) return 'Yo‘q';
                return (string) $v;
            }
            // numeric list
            if (array_keys($a) === range(0, count($a) - 1)) {
                return implode(', ', array_map(fn($v) => is_scalar($v) ? (string)$v : '', $a));
            }
            // assoc map
            return collect($a)->map(function ($v, $k) {
                if (is_array($v)) $v = implode(', ', array_filter($v));
                elseif ($v === true) $v = 'Ha';
                elseif ($v === false) $v = 'Yo‘q';
                return is_scalar($v) ? "$k: $v" : null;
            })->filter()->implode('; ');
        }

        if (is_bool($a)) return $a ? 'Ha' : 'Yo‘q';
        return (string) $a;
    };
@endphp

@if($answerGroups->isNotEmpty())
    <h2>Qo‘shimcha ma’lumotlar</h2>
    @foreach($answerGroups as $occupationName => $items)
        <h4>{{ $occupationName }}</h4>
        <ul>
            @foreach($items as $row)
                <li><b>{{ $row['q'] }}:</b> {{ $printAnswer($row['a']) }}</li>
            @endforeach
        </ul>
    @endforeach
@endif

</body>
</html>

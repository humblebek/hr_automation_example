<?php

// app/Http/Controllers/Admin/ApplicationResumeController.php
namespace App\Http\Controllers;

use App\Models\Application;
use App\Enum\Gender;
use App\Enum\Natinalities;
use App\Enum\LangCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;


class ApplicationResumeController extends Controller
{
    public function download(Application $application): Response
    {
        $lang = LangCode::UZ->value; // or detect per app

        // Eager-load everything needed (avoid N+1)
        $application->load([
            'languages',
            'educations',
            'experiences',
            'answers.question.questionTranslations' => fn ($q) => $q->where('lang_code', $lang),
            'answers.question.occupation.occupationTranslations' => fn ($q) => $q->where('lang_code', $lang),
        ]);

        // Pretty-print/normalize any answer shape
        $format = function ($raw): string {
            if ($raw === null || $raw === '') return '—';

            if (is_array($raw)) {
                if (array_key_exists('value', $raw)) {
                    $v = $raw['value'];
                    if (is_array($v)) return implode(', ', array_filter($v));
                    if (is_bool($v))  return $v ? 'Ha' : 'Yo‘q';
                    return (string) $v;
                }

                $pairs = [];
                foreach ($raw as $k => $v) {
                    if (is_array($v)) $v = implode(', ', array_filter($v));
                    elseif (is_bool($v)) $v = $v ? 'Ha' : 'Yo‘q';
                    elseif (!is_scalar($v)) continue;
                    $pairs[] = "{$k}: {$v}";
                }
                return $pairs ? implode('; ', $pairs) : '—';
            }

            return (string) $raw;
        };

        // Build grouped structure:  "Occupation => [ ['q' => ..., 'a' => ...], ... ]"
        $answerGroups = $application->answers
            ->sortBy(fn ($row) => [
                $row->question?->occupation_id ?? 0,
                $row->question?->order ?? 0,
            ])
            ->groupBy(function ($row) {
                $occ = $row->question?->occupation;
                return $occ?->occupationTranslations->first()->title ?? 'Umumiy';
            })
            ->map(function ($group) use ($format) {
                return $group->map(function ($row) use ($format) {
                    return [
                        'q' => $row->question?->questionTranslations->first()?->text ?? '—',
                        'a' => $format($row->answer),
                    ];
                })->values();
            });

        $pdf = Pdf::loadView('resume', [
            'app'           => $application,
            'sex'           => Gender::name((int) $application->sex),
            'nat'           => Natinalities::name((int) $application->nationality),
            'lang'          => $lang,
            'answerGroups'  => $answerGroups,
        ])->setPaper('a4');

        return $pdf->download($application->full_name . '.pdf');
    }
}


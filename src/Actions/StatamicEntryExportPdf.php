<?php

declare(strict_types=1);

namespace Beatwiz\StatamicEntryExportPdf\Actions;

use Statamic\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Statamic\Auth\User;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Fields\LabeledValue;
use Statamic\Forms\Submission;

class StatamicEntryExportPdf extends Action
{

    public static function title()
    {
        return __('Export PDF');
    }

    public function visibleTo($item)
    {
        return (
            ($item instanceof Entry &&
                isset($item->collection) &&
                isset($item->collection->handle) &&
                !in_array(
                    $item->collection->handle,
                    config('statamic-entry-export-pdf.excluded_collections', [])
                ))
            ||
            ($item instanceof Submission &&
                isset($item->form) &&
                isset($item->form->handle) &&
                !in_array(
                    $item->form->handle,
                    config('statamic-entry-export-pdf.excluded_forms', [])
                ))
        );
    }

    /**
     * The run method
     *
     * @return void
     */
    public function run($items, $values)
    {
        $maxItems = (int) config('statamic-entry-export-pdf.max_items', 100);
        if ($maxItems > 0) {
            $items = $items->take($maxItems);
        }

        if ($items->isEmpty()) {
            return;
        }

        $firstEntry = $items->first();
        $entryFields = $firstEntry->blueprint()
            ->tabs()
            ->flatMap(fn ($section) => $section->fields()->all())
            ->filter(fn (Field $field) => $this->shouldFieldBeIncluded($field));

        $headings = $entryFields->values();

        $entries = $items->map(function ($entry) use ($headings) {
            return $headings->mapWithKeys(function ($heading) use ($entry) {
                $value = $entry->augmentedValue($heading->handle());
                $resolved = $this->toString($value);
                return [
                    $heading->handle() =>
                    [
                        'name' => $heading->display(),
                        'value' => $resolved['html'] ?? e($resolved['text']),
                    ]
                ];
            });
        });

        $source = $firstEntry instanceof Entry ? $firstEntry->collection : $firstEntry->form;

        $pdf = Pdf::loadView('statamic-entry-export-pdf::pdf', [
            'collection' => $source,
            'entries' => $entries,
        ])->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->download('export_' . $source->handle . '_' . date('Y_m_d_H:i') . '.pdf');
    }

    /**
     * Whether this specific field should be included in the export
     *
     * @param Field $field
     * @return bool
     */
    private function shouldFieldBeIncluded(Field $field): bool
    {
        return !in_array($field->type(), config('statamic-entry-export-pdf.excluded_field_types', [])) && !in_array($field->handle(), config('statamic-entry-export-pdf.excluded_field_names', []));
    }

    private function toString($value): array
    {
        $isRichText = false;
        if ($value instanceof Value) {
            $fieldtype = $value->fieldtype();
            $isRichText = $fieldtype && in_array($fieldtype->handle(), ['bard', 'markdown', 'textarea']);
            $value = $value->value();
        }

        if ($isRichText && is_string($value)) {
            return ['html' => strip_tags($value, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><blockquote><code><pre><table><thead><tbody><tr><th><td>')];
        }

        if ($value instanceof Carbon) {
            return ['text' => $value->format('d-m-Y H:i')];
        }

        if ($value instanceof Entry) {
            return ['text' => $value->get('title')];
        }

        if ($value instanceof User) {
            return ['text' => $value->name()];
        }

        if ($value instanceof Term) {
            return ['text' => $value->title()];
        }

        if ($value instanceof LabeledValue) {
            return ['text' => $value->label()];
        }

        if ($value instanceof Asset) {
            return ['html' => '<img width="100%" src="' . htmlspecialchars(url($value->url()), ENT_QUOTES, 'UTF-8') . '">'];
        }

        if (empty($value)) {
            return ['text' => null];
        }

        if (is_array($value)) {
            return ['text' => json_encode($value)];
        }

        return ['text' => $value];
    }
}

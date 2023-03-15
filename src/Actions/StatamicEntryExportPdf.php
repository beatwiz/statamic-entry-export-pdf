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
        return ($item instanceof Entry &&
            isset($item->collection) &&
            isset($item->collection->handle) &&
            !in_array(
                $item->collection->handle,
                config('statamic-entry-export-pdf.excluded_collections', [])
            )
            ||
            $item instanceof Submission &&
            isset($item->form) &&
            isset($item->form->handle) &&
            !in_array(
                $item->form->handle,
                config('statamic-entry-export-pdf.excluded_collections', [])
            )
        );
    }

    /**
     * The run method
     *
     * @return void
     */
    public function run($items, $values)
    {
        $firstEntry = $items->first();
        $entryFields = $firstEntry->blueprint()
            ->sections()
            ->flatMap(fn ($section) => $section->fields()->all())
            ->filter(fn (Field $field) => $this->shouldFieldBeIncluded($field));

        $headings = $entryFields->values();

        $entries = $items->map(function ($entry) use ($headings) {
            return $headings->mapWithKeys(function ($heading) use ($entry) {
                $value = $entry->augmentedValue($heading->handle());
                return [
                    $heading->handle() =>
                    [
                        'name' => $heading->display(),
                        'value' => $this->toString($value)
                    ]
                ];
            });
        });

        if ($firstEntry instanceof Entry) {
            $pdf = Pdf::loadView('statamic-entry-export-pdf::pdf', [
                'collection' => $firstEntry->collection,
                'entries' => $entries,
            ])->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
            return $pdf->download('export_' . $firstEntry->collection->handle . '_' . date('Y_m_d_H:i') . '.pdf');
        } else if ($firstEntry instanceof Submission) {
            $pdf = Pdf::loadView('statamic-entry-export-pdf::pdf', [
                'collection' => $firstEntry->form,
                'entries' => $entries,
            ])->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
            return $pdf->download('export_' . $firstEntry->form->handle . '_' . date('Y_m_d_H:i') . '.pdf');
        }
    }

    /**
     * Whether this specific field should be included in the export
     *
     * @param Field $field
     * @return bool
     */
    protected function shouldFieldBeIncluded(Field $field): bool
    {
        return !in_array($field->type(), config('statamic-entry-export-pdf.excluded_field_types')) && !in_array($field->handle(), config('statamic-entry-export-pdf.excluded_field_names'));
    }

    private function toString($value)
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if ($value instanceof Carbon) {
            return $value->format('d-m-Y H:i');
        }

        if ($value instanceof Entry) {
            return $value->get('title');
        }

        if ($value instanceof User) {
            return $value->name();
        }

        if ($value instanceof Term) {
            return $value->title();
        }

        if ($value instanceof LabeledValue) {
            return $value->label();
        }

        if ($value instanceof Asset) {
            return '<img width="100%" src="' . url($value->url()) . '">';
        }

        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}

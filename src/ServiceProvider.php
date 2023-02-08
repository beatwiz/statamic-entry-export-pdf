<?php

namespace Beatwiz\StatamicEntryExportPdf;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\StatamicEntryExportPdf::class,
    ];
    public function bootAddon()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/statamic-entry-export-pdf.php', 'statamic-entry-export-pdf');

        $this->publishes([
            __DIR__ . '/../config/statamic-entry-export-pdf.php' => config_path('statamic-entry-export-pdf.php'),
        ], 'statamic-entry-export-pdf-config');
    }
}


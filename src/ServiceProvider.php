<?php

namespace beatwiz\statamic-entry-export-pdf;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/statamic-entry-export-pdf.php', 'statamic-entry-export-pdf');

        $this->publishes([
            __DIR__ . '/../config/statamic-entry-export-pdf.php' => config_path('statamic-entry-export-pdf.php'),
        ], 'statamic-entry-export-pdf-config');

        EntryExportPDF::register();
    }
}

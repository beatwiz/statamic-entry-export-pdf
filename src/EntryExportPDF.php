<?php

namespace beatwiz\statamic-entry-export-pdf;

use Statamic\Actions\Action;

class EntryExportPDF extends Action
{
    /**
     * Title
     */
    public static function title()
    {
        return __('Export PDF');
    }
}
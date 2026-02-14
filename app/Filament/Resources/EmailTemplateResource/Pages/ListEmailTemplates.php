<?php

namespace App\Filament\Resources\EmailTemplateResource\Pages;

use App\Filament\Resources\EmailTemplateResource;
use App\Filament\Resources\EmailTemplateResource\Widgets\AdminEmailWidget;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AdminEmailWidget::class,
        ];
    }
}

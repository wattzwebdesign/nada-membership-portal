<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ClinicalLogEntry extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'clinical_log_id',
        'date',
        'location',
        'protocol',
        'hours',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'hours' => 'decimal:2',
        ];
    }

    public function clinicalLog(): BelongsTo
    {
        return $this->belongsTo(ClinicalLog::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('entry_attachments')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }
}

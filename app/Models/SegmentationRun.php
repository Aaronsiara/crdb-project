<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SegmentationRun extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'run_uid',
        'user_id',
        'original_filename',
        'k',
        'features',
        'status',
        'total_records',
        'num_segments',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Absolute filesystem path (under storage/app/segmentation/<run_uid>/)
     * where this run's input file and generated outputs live.
     */
    public function storagePath(): string
    {
        return Storage::disk('local')->path('segmentation/' . $this->run_uid);
    }

    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETE;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}

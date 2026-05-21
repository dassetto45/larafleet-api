<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;


#[Fillable(['vehicle_id', 'user_id', 'description', 'scheduled_at', 'completed_at'])]
class Maintenance extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'date',
            'completed_at' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

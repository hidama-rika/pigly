<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // 追加

class WeightTarget extends Model
{
    use HasFactory;

    protected $table = 'weight_target';

    protected $fillable = [
        'user_id',
        'target_weight',
    ];

    /**
     * この目標が属するユーザーを取得します。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

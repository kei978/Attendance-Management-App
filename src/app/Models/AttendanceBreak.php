<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
        'break_minutes',
    ];

    protected $casts = [
        'break_start' => 'string',
        'break_end'   => 'string',
    ];

    /**
     * 勤怠に紐づく Attendance モデルを取得する
     *
     * @return BelongsTo
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
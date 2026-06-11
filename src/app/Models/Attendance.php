<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_work_minutes',
        'total_break_minutes',
        'status',
    ];

    protected $casts = [
        'date'       => 'date',
        'clock_in'   => 'datetime',
        'clock_out'  => 'datetime',
    ];


    /**
     * ユーザー
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩
     *
     * @return HasMany
     */
    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    /**
     * 修正申請
     *
     * @return HasMany
     */
    public function requests(): HasMany
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    /**
     * 合計休憩時間（分）の計算
     *
     */
    public function calculateBreakMinutes()
    {
        $total = 0;

        foreach ($this->breaks as $break) {
            if ($break->break_start && $break->break_end) {
                $start = Carbon::parse($break->break_start);
                $end   = Carbon::parse($break->break_end);
                $total += $end->diffInMinutes($start);
            }
        }

        return $total;
    }

    /**
     * 実働時間（分）の計算
     *
     */
    public function calculateWorkMinutes()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $in  = Carbon::parse($this->clock_in);
        $out = Carbon::parse($this->clock_out);

        $work = $out->diffInMinutes($in);

        // 休憩を引く
        $work -= $this->calculateBreakMinutes();

        return max($work, 0);
    }

    /**
     * 一覧・CSV 用：合計休憩
     *
     */
    public function getDisplayBreakMinutesAttribute()
    {
        return $this->calculateBreakMinutes();
    }

    /**
     * 一覧・CSV 用：合計勤務
     *
     */
    public function getDisplayWorkMinutesAttribute()
    {
        return $this->calculateWorkMinutes();
    }
}

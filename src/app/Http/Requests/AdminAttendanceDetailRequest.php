<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AdminAttendanceDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in'      => ['nullable', 'date_format:H:i'],
            'clock_out'     => ['nullable', 'date_format:H:i'],
            'break_start'   => ['array'],
            'break_start.*' => ['nullable', 'date_format:H:i'],
            'break_end'     => ['array'],
            'break_end.*'   => ['nullable', 'date_format:H:i'],
            'note'          => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $starts   = $this->input('break_start', []);
            $ends     = $this->input('break_end', []);

            // Carbon 変換
            $in  = $clockIn  ? Carbon::createFromFormat('H:i', $clockIn)  : null;
            $out = $clockOut ? Carbon::createFromFormat('H:i', $clockOut) : null;

            // 出勤 > 退勤 / 退勤 < 出勤
            if ($in && $out && $in->gte($out)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ($starts as $i => $s) {
                $e = $ends[$i] ?? null;

                $bs = $s ? Carbon::createFromFormat('H:i', $s) : null;
                $be = $e ? Carbon::createFromFormat('H:i', $e) : null;

                // 完全に空の行はスキップ
                if (!$bs && !$be) {
                    continue;
                }

                // 休憩開始が出勤前 or 退勤後
                if ($bs) {
                    if ($in && $bs->lt($in)) {
                        $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    }
                    if ($out && $bs->gt($out)) {
                        $validator->errors()->add("break_start.$i", '休憩時間が不適切な値です');
                    }
                }

                // 休憩終了が退勤後
                if ($be && $out && $be->gt($out)) {
                    $validator->errors()->add("break_end.$i", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WeightLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 認証ミドルウェアでチェック済みのため、ここでは常に true を返します
        return true;
    }

    /**
     * リクエストデータをバリデーション前に処理
     * exercise_time (H:i 形式) をデータベースの time 型 (H:i:s) に合わせるため、秒を '00' に固定
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $exerciseTime = $this->input('exercise_time');

        // H:i 形式が入力されていることを前提に、秒を :00 として付加
        if ($exerciseTime && preg_match('/^\d{2}:\d{2}$/', $exerciseTime)) {
            $this->merge([
                'exercise_time' => $exerciseTime . ':00',
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 日付: 必須
            'date' => ['required', 'date'],

            // 体重: 必須、数値、4桁までの数字（小数点1桁までを許容する）
            // 999.9までを許容するため、max:999.9とし、regexで桁数を制御します
            'weight' => [
                'required',
                'numeric',
                'max:999.9', // 4桁（3桁.1桁）までの上限
                'regex:/^\d{1,3}(\.\d{1})?$/' // 整数1~3桁、または整数1~3桁＋小数点1桁
            ],

            // 摂取カロリー: 必須、数値、整数
            'calories' => ['required', 'integer', 'min:0'],

            // 運動時間: 必須のみをチェック。00:00形式チェックルールはエラーメッセージを出さないために削除。
            'exercise_time' => ['required', 'string'],

            // 運動内容/メモ: 必須ではない、最大120文字
            'exercise_content' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * バリデーションエラーメッセージをカスタマイズ。
     * フィールド名.ルール名 => メッセージ の形式で定義。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // 日付
            'date.required' => '日付を入力してください',

            // 体重
            'weight.required' => '体重を入力してください',
            'weight.numeric' => '数字で入力してください',
            // max:999.9で4桁までのチェックを兼ねます
            'weight.max' => '4桁までの数字で入力してください',
            'weight.regex' => '小数点は1桁で入力してください',

            // 摂取カロリー
            'calories.required' => '摂取カロリーを入力してください',
            'calories.integer' => '数字で入力してください',

            // 運動時間
            'exercise_time.required' => '運動時間を入力してください',

            // 運動内容
            'exercise_content.max' => '120文字以内で入力してください',
        ];
    }
}

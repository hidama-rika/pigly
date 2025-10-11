<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TargetRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 認証済みのユーザーであれば、目標設定を許可する
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // weight_logsテーブル: weight (decimal(4,1))
        // weight_targetテーブル: target_weight (decimal(4,1))
        return [
            'weight' => [
                'required',
                'numeric',
                'max:999.9', // ★修正: 最大値を明示的に追加 (numericエラーとして処理される)
                'regex:/^\d{1,3}(\.\d{1})?$/', // 整数部3桁まで (全体で4桁まで) かつ 小数点以下1桁
            ],
            'target_weight' => [
                'required',
                'numeric',
                'max:999.9', // ★修正: 最大値を明示的に追加
                'regex:/^\d{1,3}(\.\d{1})?$/', // 整数部3桁まで (全体で4桁まで) かつ 小数点以下1桁
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // --- 現在の体重 (weight) ---
            'weight.required' => '現在の体重を入力してください',
            // numeric (数値以外) または max (999.9超え) のエラーメッセージとして使用
            'weight.numeric' => '4桁までの数字で入力してください',
            'weight.max' => '4桁までの数字で入力してください', // maxルールのエラーメッセージを定義
            // 小数点2桁以上 (例: 50.12) で regex エラーが発生する
            'weight.regex' => '小数点は1桁で入力してください',

            // --- 目標の体重 (target_weight) ---
            'target_weight.required' => '目標の体重を入力してください',
            // numeric (数値以外) または max (999.9超え) のエラーメッセージとして使用
            'target_weight.numeric' => '4桁までの数字で入力してください',
            'target_weight.max' => '4桁までの数字で入力してください',
            // 小数点2桁以上 (例: 50.12) で regex エラーが発生する
            'target_weight.regex' => '小数点は1桁で入力してください',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalSettingRequest extends FormRequest
{
    /**
     * バリデーション失敗時にリダイレクトするパスを明示的に指定
     *
     * 【重要】目標設定フォームのURLパスを正確に記述してください。
     * WeightLogControllerのgoalSettingメソッドに対応するURL（例: /weight_logs/goal_setting）を指定します。
     * back()の不具合回避策です。
     *
     * @var string
     */
    protected $redirect = '/weight_logs/goal_setting';

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

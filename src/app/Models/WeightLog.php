<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightLog extends Model
{
    use HasFactory;

    protected $table = 'weight_logs';

    protected $fillable = [
        'user_id',
        'date',
        'weight',
        'calories',
        'exercise_time',
        'exercise_content',
    ];

    /**
     * モデルの属性に対する型キャスト。
     * user_id を integer に強制することで、読み込みエラーを防ぎます。
     * date は date 型として扱うことで、Carbonインスタンスとして利用できます。
     */
    protected $casts = [
        'user_id' => 'integer',         // ★ user_id を integer に強制キャスト
        'date' => 'date',               // date も date 型としてキャスト
        'weight' => 'float',            // weight も float 型としてキャスト
        'calories' => 'integer',
        // exercise_time は time 型なので、stringのままにしておきます
    ];


    /**
     * DBに保存する前のミューテータ (setWeightAttribute)
     * 入力された値 (例: '70' または '70.23') を数値に変換し、常に小数点以下1桁に丸めてから保存します。
     * これにより、DBには 70.0 や 70.2 という正確な数値が渡されます。
     */
    public function setWeightAttribute($value)
    {
        // 入力値を float にキャストし、小数点以下1桁に丸めます。
        // 例: '70' -> 70.0 (float), '70.23' -> 70.2 (float)
        $this->attributes['weight'] = round((float) $value, 1);
    }

    /**
     * DBから値を取得した後のアクセサ
     * DBに小数として保存されている値を、常に小数点以下1桁でフォーマットして返します。
     * 仮に DBに 70 が保存されてしまったとしても、 '70.0' になる。
     */
    public function getWeightAttribute($value)
    {
        // 小数点以下1桁の表示 (0.0 の形式) を保証
        return number_format((float) $value, 1);
    }

    /**
     * 日本語整形された日付を取得するアクセサ (DBから取得時)
     * date カラムの値 (`$this->date`) を取得し、'YYYY年M月D日' 形式に整形して返します。
     * 元の $weightLog->date は 'YYYY-MM-DD' のまま保持される。モーダルや更新画面など、日本語形式が必要な場所でのみ、新しく定義した属性 ($weightLog->jp_date) を使用。
     *
     * @return string 整形された日付文字列 (例: "2025年10月7日")
     */
    public function getJpDateAttribute(): string
    {
        // Carbonインスタンス（$this->attributes['date']または$this->dateで取得可能）
        $date = $this->date;

        if (is_null($date)) {
            return '';
        }

        // Carbonインスタンスが渡されるので、formatメソッドで整形します。
        // 'Y年n月j日' は、月の前に '0' をつけず、日の前にも '0' をつけない形式です。
        return $date->format('Y年n月j日');
    }

    /**
     * 運動時間を取得するアクセサ (DBから取得時)
     * DBに time 型 (HH:MM:SS) の文字列で保存されている値を「時:分」形式 (HH:MM) に整形して返します。
     *
     * @param string $value DBに保存された値 (例: "00:49:36" や "01:28:30")
     * @return string 整形された時間文字列 (例: "00:49" や "01:28")
     */
    public function getExerciseTimeAttribute($value): string
    {
        // 値が null または空文字列の場合、デフォルトの '00:00' を返す
        if (is_null($value)) {
            return '00:00';
        }

        // $value は "HH:MM:SS" 形式の文字列
        // strftimeの代わりに、PHPのDateTimeオブジェクトを使って安全に整形します
        try {
            // DateTime::createFromFormatでTIME型をパース
            $time = \DateTime::createFromFormat('H:i:s', $value);

            // 形式が有効であれば HH:MM 形式で返す
            if ($time) {
                return $time->format('H:i');
            }
        } catch (\Exception $e) {
            // パースエラーが発生した場合や形式が不正な場合は、元の値をそのまま返す
            // エラーハンドリングとして、ここでは元の値を返します
            return '00:00';
        }

        // 形式が合わない場合はそのまま返すのではなく、安全のために '00:00' を返す
        // 例えば、DBに 'abc' のような無効な文字列が入っていた場合など
        return '00:00';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

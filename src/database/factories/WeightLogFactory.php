<?php

namespace Database\Factories;

use App\Models\WeightLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;

class WeightLogFactory extends Factory
{
    /**
     * 対応するモデル
     *
     * @var string
     */
    protected $model = WeightLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition(): array
    {
        // 過去35日間の日付をランダムに生成し、一意になるようにします
        $date = $this->faker->unique()->dateTimeBetween('-35 days', 'yesterday')->format('Y-m-d');

        // 体重をランダムに設定 (45.0kgから50.0kgの間、小数点以下1桁)
        $weight = $this->faker->randomFloat(1, 45.0, 50.0);

        // カロリーをランダムに設定 (1200kcalから2500kcalの間)
        $calories = $this->faker->numberBetween(1200, 2500);

        // 運動時間をランダムに設定 (00:00:00から01:30:00の間)
        // DateTimeオブジェクトから時間部分をフォーマット
        $exercise_time = $this->faker->dateTimeBetween('0 minutes', '90 minutes')->format('H:i:s');

        // 運動内容をランダムに設定
        $exercise_content = $this->faker->randomElement([
            'ランニング 30分',
            '筋力トレーニング (全身)',
            'ウォーキング 60分',
            'ヨガとストレッチ',
            '特に運動なし'
        ]);

        return [
            // user_id は Seederで設定するためここでは定義しません
            'date' => $date,
            'weight' => $weight,
            'calories' => $calories,
            'exercise_time' => $exercise_time,
            'exercise_content' => $exercise_content,
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use app\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $category_id = Category::all()->pluck('id')->toArray();

        return [
            'name'=>$this->faker->company(),
            'description'=>$this->faker->text(),
            'start_date'=>fake()->dateTimeBetween('-1 month', 'now'),
            'final_date'=>fake()->dateTimeBetween('now', '+1 month'),
            'category_id'=>$this->faker->randomElement($category_id),
            'hidden'=>$this->faker->optional()->dateTimeThisYear()
        ];
    }
}

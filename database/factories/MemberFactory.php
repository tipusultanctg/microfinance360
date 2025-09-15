<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            // We will set tenant_id and branch_id in the seeder itself.
            // This factory only needs to define the member-specific attributes.
            'member_uid' => 'M-' . fake()->unique()->randomNumber(8),
            'name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}

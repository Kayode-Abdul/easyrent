<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        
        return [
            'user_id' => fake()->unique()->numberBetween(1000000, 9999999),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => fake()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role' => 2, // Default role (landlord)
            'region' => fake()->randomElement(['Lagos', 'Abuja', 'Kano', 'Port Harcourt']),
        ];
    }
    
    /**
     * Configure the factory to handle 'name' attribute for backward compatibility.
     * If 'name' is provided, it will be split into first_name and last_name.
     */
    public function configure()
    {
        return $this->afterMaking(function (\App\Models\User $user) {
            // Handle 'name' attribute if it was set via state
            if (isset($this->states['name'])) {
                $nameParts = explode(' ', $this->states['name'], 2);
                $user->first_name = $nameParts[0];
                $user->last_name = $nameParts[1] ?? '';
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'recipient_id' => User::factory(),
            'body' => fake()->realText(200),
            'scheduled_for' => Message::nextBatchFor(),
            'delivered_at' => null,
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn () => ['delivered_at' => now()]);
    }
}

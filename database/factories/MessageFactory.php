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
            'is_draft' => false,
            'scheduled_for' => Message::nextBatchFor(),
            'sent_at' => now(),
            'delivered_at' => null,
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn () => ['delivered_at' => now()]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'is_draft' => true,
            'scheduled_for' => null,
            'sent_at' => null,
            'delivered_at' => null,
        ]);
    }
}
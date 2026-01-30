<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(), // ✅ Langsung pakai fake()
            'email' => fake()->unique()->safeEmail(),
            'affiliation' => fake()->randomElement([
                'Universitas Gadjah Mada',
                'Institut Teknologi Bandung',
                'Universitas Indonesia',
                'Institut Pertanian Bogor',
                'Universitas Airlangga',
                'Universitas Brawijaya',
                'Universitas Diponegoro',
                'Institut Teknologi Sepuluh Nopember',
            ]),
            'bio' => fake()->paragraph(3),
            'photo_path' => null,
        ];
    }

    public function withUser(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    public function inField(string $field): static
    {
        $affiliations = [
            'biology' => ['Universitas Gadjah Maga - Fakultas Biologi', 'IPB University - Departemen Biologi'],
            'physics' => ['Institut Teknologi Bandung - Fisika', 'Universitas Indonesia - Departemen Fisika'],
            'chemistry' => ['Universitas Brawijaya - Kimia', 'ITS - Departemen Kimia'],
        ];

        return $this->state(fn(array $attributes) => [
            'affiliation' => fake()->randomElement($affiliations[$field] ?? $affiliations['biology']),
        ]);
    }
}

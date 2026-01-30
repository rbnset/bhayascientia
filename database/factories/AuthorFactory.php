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
            'user_id' => null, // Akan di-set manual atau null
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'affiliation' => $this->faker->randomElement([
                'Universitas Gadjah Mada',
                'Institut Teknologi Bandung',
                'Universitas Indonesia',
                'Institut Pertanian Bogor',
                'Universitas Airlangga',
                'Universitas Brawijaya',
                'Universitas Diponegoro',
                'Institut Teknologi Sepuluh Nopember',
            ]),
            'bio' => $this->faker->paragraph(3),
            'photo_path' => null,
        ];
    }

    /**
     * Author yang terhubung dengan user
     */
    public function withUser(): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    /**
     * Author dengan bidang spesifik
     */
    public function inField(string $field): static
    {
        $affiliations = [
            'biology' => ['Universitas Gadjah Mada - Fakultas Biologi', 'IPB University - Departemen Biologi'],
            'physics' => ['Institut Teknologi Bandung - Fisika', 'Universitas Indonesia - Departemen Fisika'],
            'chemistry' => ['Universitas Brawijaya - Kimia', 'ITS - Departemen Kimia'],
        ];

        return $this->state(fn(array $attributes) => [
            'affiliation' => $this->faker->randomElement($affiliations[$field] ?? $affiliations['biology']),
        ]);
    }
}

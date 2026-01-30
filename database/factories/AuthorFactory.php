<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    /**
     * ✅ Force initialize Faker instance
     */
    public function withFaker()
    {
        return \Faker\Factory::create($this->faker ? $this->faker->locale : 'en_US');
    }

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
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

    public function withUser(): static
    {
        return $this->state(function (array $attributes) {
            $user = User::factory()->create();

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('author');
            }

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo_path' => $user->profile_photo ?? null,
            ];
        });
    }

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

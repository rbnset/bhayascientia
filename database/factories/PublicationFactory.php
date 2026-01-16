<?php

namespace Database\Factories;

use App\Models\Method;
use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PublicationFactory extends Factory
{
    protected $model = Publication::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(rand(6, 12));

        return [
            'publication_type_id' => PublicationType::inRandomOrder()->first()?->id,
            'method_id' => Method::inRandomOrder()->first()?->id,
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'abstract' => $this->faker->paragraphs(3, true),
            'status' => 'published', // ✅ Sesuai ENUM
            'published_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'cover_image_path' => null,
        ];
    }

    /**
     * Publication dengan status draft
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Publication dengan status submitted
     */
    public function submitted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'submitted',
            'published_at' => null,
        ]);
    }

    /**
     * Publication dengan status in_review
     */
    public function inReview(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'in_review', // ✅ Sesuai ENUM di migration
            'published_at' => null,
        ]);
    }

    /**
     * Publication dengan status revision_required
     */
    public function revisionRequired(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'revision_required',
            'published_at' => null,
        ]);
    }

    /**
     * Publication dengan status accepted
     */
    public function accepted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'accepted',
            'published_at' => null,
        ]);
    }

    /**
     * Publication dengan status rejected
     */
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'rejected',
            'published_at' => null,
        ]);
    }

    /**
     * Publication dengan tipe spesifik
     */
    public function ofType(string $typeSlug): static
    {
        return $this->state(function (array $attributes) use ($typeSlug) {
            $type = PublicationType::where('slug', $typeSlug)->first();

            return [
                'publication_type_id' => $type?->id,
            ];
        });
    }

    /**
     * Publication dengan method spesifik
     */
    public function withMethod(string $methodSlug): static
    {
        return $this->state(function (array $attributes) use ($methodSlug) {
            $method = Method::where('slug', $methodSlug)->first();

            return [
                'method_id' => $method?->id,
            ];
        });
    }
}

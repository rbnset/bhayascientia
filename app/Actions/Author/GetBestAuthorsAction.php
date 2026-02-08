<?php

namespace App\Actions\Author;

use App\Repositories\AuthorRepository;
use App\Services\AuthorService;
use Illuminate\Support\Collection;

class GetBestAuthorsAction
{
    public function __construct(
        private AuthorRepository $authorRepository,
        private AuthorService $authorService
    ) {}

    /**
     * Execute: Get best authors untuk publication type tertentu
     *
     * @param string|null $typeSlug Slug dari PublicationType
     * @param int $limit Jumlah author yang diambil
     * @return Collection Collection of formatted author arrays
     */
    public function execute(?string $typeSlug = null, int $limit = 6): Collection
    {
        // Get authors dari repository
        $authors = $this->authorRepository->getTopAuthors($typeSlug, $limit);

        // Format untuk view
        return $this->authorService->formatAuthorsForCards($authors);
    }
}

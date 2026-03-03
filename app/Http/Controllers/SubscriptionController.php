<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function getPublicationTypes(): array
    {
        return PublicationType::select('id', 'name', 'slug', 'description')
            ->withCount(['publications' => fn($q) => $q->where('status', 'published')])
            ->having('publications_count', '>', 0)
            ->get()
            ->mapWithKeys(function ($type) {
                $categoryIds = DB::table('publications')
                    ->where('publication_type_id', $type->id)
                    ->where('status', 'published')
                    ->join('category_publication', 'publications.id', '=', 'category_publication.publication_id')
                    ->distinct()
                    ->pluck('category_publication.category_id')
                    ->toArray();

                return [
                    $type->slug => [
                        'id'                     => $type->id,
                        'label'                  => $type->name,
                        'slug'                   => $type->slug,
                        'description'            => $type->description ?? 'Koleksi ' . $type->name,
                        'emoji'                  => $this->getTypeEmoji($type->slug),
                        'count'                  => $type->publications_count,
                        'available_category_ids' => $categoryIds,
                    ],
                ];
            })
            ->toArray();
    }

    private function buildCategories(array $publicationTypes): array
    {
        return Category::select('id', 'name', 'slug')
            ->withCount('publications')
            ->having('publications_count', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($category) use ($publicationTypes) {
                $countPerType = [];

                foreach ($publicationTypes as $slug => $type) {
                    $count = Publication::where('status', 'published')
                        ->where('publication_type_id', $type['id'])
                        ->whereHas('categories', fn($q) => $q->where('categories.id', $category->id))
                        ->count();

                    if ($count > 0) {
                        $countPerType[$slug] = $count;
                    }
                }

                return [
                    'id'                 => $category->id,
                    'name'               => $category->name,
                    'slug'               => $category->slug,
                    'total_count'        => $category->publications_count,
                    'count_per_type'     => $countPerType,
                    'available_in_types' => array_keys($countPerType),
                ];
            })
            ->toArray();
    }

    private function getNotificationTypes(): array
    {
        return [
            'instant' => [
                'label'       => 'Notifikasi Instan',
                'description' => 'Langsung setiap ada publikasi baru',
                'detail'      => 'Email segera setelah publikasi tersedia (Max 3 email/hari)',
                'icon'        => '⚡',
                'frequency'   => 'Real-time',
                'recommended' => false,
                'estimated'   => 'Max 3 email/hari',
                'spam_risk'   => 'low',
            ],
            'daily' => [
                'label'       => 'Ringkasan Harian',
                'description' => 'Kumpulan publikasi baru setiap hari',
                'detail'      => 'Email dikirim setiap pagi pukul 08:00 WIB berisi publikasi 24 jam terakhir',
                'icon'        => '🌅',
                'frequency'   => 'Setiap Hari',
                'recommended' => false,
                'estimated'   => '1 email/hari',
            ],
            'weekly_new' => [
                'label'       => 'Mingguan - Terbaru',
                'description' => 'Publikasi terbaru minggu ini',
                'detail'      => 'Email dikirim setiap Senin pukul 08:00 WIB berisi publikasi 7 hari terakhir',
                'icon'        => '📅',
                'frequency'   => 'Setiap Minggu',
                'recommended' => true,
                'estimated'   => '1 email/minggu',
            ],
            'weekly_popular' => [
                'label'       => 'Mingguan - Terpopuler',
                'description' => 'Publikasi paling banyak dibaca',
                'detail'      => 'Email berisi 10 publikasi terpopuler minggu lalu',
                'icon'        => '🔥',
                'frequency'   => 'Setiap Minggu',
                'recommended' => false,
                'estimated'   => '1 email/minggu',
            ],
            'monthly_popular' => [
                'label'       => 'Bulanan - Terpopuler',
                'description' => 'Publikasi terbaik bulan ini',
                'detail'      => 'Email berisi top publikasi bulan lalu',
                'icon'        => '⭐',
                'frequency'   => 'Setiap Bulan',
                'recommended' => false,
                'estimated'   => '1 email/bulan',
            ],
        ];
    }

    private function getStats(array $categories, array $publicationTypes): array
    {
        return [
            'total_publications' => Publication::where('status', 'published')->count(),
            'total_categories'   => count($categories),
            'total_types'        => count($publicationTypes),
            'this_week'          => Publication::where('status', 'published')
                ->where('published_at', '>=', now()->startOfWeek())
                ->count(),
            'subscribers_count'  => Subscription::where('is_active', true)->count(),
        ];
    }

    private function resolveValidCategories(array $typeSlugs, array $categoryIds): array
    {
        $typeIds = PublicationType::whereIn('slug', $typeSlugs)->pluck('id')->toArray();

        if (empty($typeIds)) return [];

        return DB::table('category_publication')
            ->join('publications', 'category_publication.publication_id', '=', 'publications.id')
            ->whereIn('publications.publication_type_id', $typeIds)
            ->where('publications.status', 'published')
            ->whereIn('category_publication.category_id', $categoryIds)
            ->distinct()
            ->pluck('category_publication.category_id')
            ->toArray();
    }

    private function maxEmailsPerDay(string $notificationType): int
    {
        return match ($notificationType) {
            'instant' => 3,
            'daily'   => 1,
            default   => 0,
        };
    }

    private function getTypeEmoji(string $slug): string
    {
        return [
            'jurnal'     => '📚',
            'journal'    => '📚',
            'opini'      => '✍️',
            'opinion'    => '✍️',
            'buku'       => '📖',
            'book'       => '📖',
            'artikel'    => '📄',
            'article'    => '📄',
            'penelitian' => '🔬',
            'research'   => '🔬',
            'thesis'     => '🎓',
            'tesis'      => '🎓',
        ][$slug] ?? '📑';
    }

    private function validationRules(): array
    {
        return [
            'types'             => 'required|array|min:1|max:3',
            'types.*'           => 'string',
            'categories'        => 'required|array|min:1|max:10',
            'categories.*'      => 'integer|exists:categories,id',
            'notification_type' => 'required|in:instant,daily,weekly_new,weekly_popular,monthly_popular',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'types.required'             => 'Pilih minimal 1 jenis publikasi.',
            'types.min'                  => 'Pilih minimal 1 jenis publikasi.',
            'types.max'                  => 'Maksimal 3 jenis publikasi untuk menghindari spam.',
            'categories.required'        => 'Pilih minimal 1 kategori.',
            'categories.min'             => 'Pilih minimal 1 kategori.',
            'categories.max'             => 'Maksimal 10 kategori untuk kualitas konten yang lebih fokus.',
            'notification_type.required' => 'Pilih frekuensi notifikasi.',
            'notification_type.in'       => 'Frekuensi notifikasi tidak valid.',
        ];
    }

    // =========================================================================
    // PUBLIC ACTIONS
    // =========================================================================

    public function index()
    {
        $publicationTypes  = $this->getPublicationTypes();
        $categories        = $this->buildCategories($publicationTypes);
        $notificationTypes = $this->getNotificationTypes();
        $stats             = $this->getStats($categories, $publicationTypes);
        $subscription      = Auth::user()->subscription;

        return view('pages.subscription', compact(
            'subscription',
            'categories',
            'publicationTypes',
            'notificationTypes',
            'stats'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $validCategories = $this->resolveValidCategories(
            $validated['types'],
            $validated['categories']
        );

        if (empty($validCategories)) {
            return back()->withInput()
                ->with('error', 'Kategori yang dipilih tidak memiliki publikasi untuk jenis yang dipilih. Silakan pilih kategori lain.');
        }

        Subscription::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'types'              => $validated['types'],
                'categories'         => array_values($validCategories),
                'notification_type'  => $validated['notification_type'],
                'max_emails_per_day' => $this->maxEmailsPerDay($validated['notification_type']),
                'is_active'          => true,
                'subscribed_at'      => now(),
                'unsubscribed_at'    => null,
                'emails_sent_today'  => 0,
                'last_email_date'    => null,
            ]
        );

        Log::info('Newsletter subscription created', [
            'user_id'           => Auth::id(),
            'types'             => $validated['types'],
            'categories_count'  => count($validCategories),
            'notification_type' => $validated['notification_type'],
        ]);

        return redirect()->route('subscription.index')
            ->with('success', sprintf(
                '🎉 Berhasil berlangganan! Anda akan menerima pembaruan dari %d jenis publikasi dan %d kategori ke email %s',
                count($validated['types']),
                count($validCategories),
                Auth::user()->email
            ));
    }

    public function update(Request $request)
    {
        $subscription = Auth::user()->subscription;

        if (!$subscription) {
            return redirect()->route('subscription.index')
                ->with('error', 'Anda belum berlangganan.');
        }

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $validCategories = $this->resolveValidCategories(
            $validated['types'],
            $validated['categories']
        );

        if (empty($validCategories)) {
            return back()->withInput()
                ->with('error', 'Kategori yang dipilih tidak tersedia untuk jenis publikasi yang dipilih.');
        }

        $subscription->update([
            'types'              => $validated['types'],
            'categories'         => array_values($validCategories),
            'notification_type'  => $validated['notification_type'],
            'max_emails_per_day' => $this->maxEmailsPerDay($validated['notification_type']),
        ]);

        Log::info('Newsletter subscription updated', [
            'user_id'          => Auth::id(),
            'types'            => $validated['types'],
            'categories_count' => count($validCategories),
        ]);

        return redirect()->route('subscription.index')
            ->with('success', '✅ Preferensi langganan berhasil diperbarui!');
    }

    public function destroy()
    {
        $subscription = Auth::user()->subscription;

        if (!$subscription) {
            return redirect()->route('subscription.index')
                ->with('error', 'Anda belum berlangganan.');
        }

        $subscription->update([
            'is_active'       => false,
            'unsubscribed_at' => now(),
        ]);

        Log::info('Newsletter unsubscribed', ['user_id' => Auth::id()]);

        return redirect()->route('subscription.index')
            ->with('success', 'Langganan berhasil dibatalkan. Anda tidak akan menerima email lagi.');
    }

    public function reactivate()
    {
        $subscription = Auth::user()->subscription;

        if (!$subscription) {
            return redirect()->route('subscription.index')
                ->with('error', 'Anda belum pernah berlangganan.');
        }

        $subscription->update([
            'is_active'       => true,
            'subscribed_at'   => now(),
            'unsubscribed_at' => null,
        ]);

        Log::info('Newsletter reactivated', ['user_id' => Auth::id()]);

        return redirect()->route('subscription.index')
            ->with('success', '🎉 Langganan berhasil diaktifkan kembali!');
    }

    // ─── AJAX ────────────────────────────────────────────────────────────────

    public function getCategoriesAjax(Request $request)
    {
        $types = $request->input('types', []);

        if (empty($types)) return response()->json([]);

        $typeIds = PublicationType::whereIn('slug', $types)->pluck('id')->toArray();

        if (empty($typeIds)) return response()->json([]);

        $categories = DB::table('category_publication')
            ->join('publications', 'category_publication.publication_id', '=', 'publications.id')
            ->join('categories', 'category_publication.category_id', '=', 'categories.id')
            ->whereIn('publications.publication_type_id', $typeIds)
            ->where('publications.status', 'published')
            ->select(
                'categories.id',
                'categories.name',
                'categories.slug',
                DB::raw('COUNT(DISTINCT publications.id) as count')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->having('count', '>', 0)
            ->orderBy('categories.name')
            ->get();

        return response()->json($categories);
    }
}

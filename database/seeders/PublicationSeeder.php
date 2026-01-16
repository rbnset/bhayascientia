<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Category;
use App\Models\Keyword;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Method;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublicationSeeder extends Seeder
{
    public function run(): void
    {
        // 9 Buku
        $this->createExplosivesForensicsBook();
        $this->createBlastEngineeringHandbook();
        $this->createMilitaryExplosivesGuide();
        $this->createIEDDetectionTechniques();
        $this->createPyrotechnicsChemistry();
        $this->createMiningExplosivesManual();
        $this->createExplosivesSafetyProtocols();
        $this->createBlastInjuryMedicine();
        $this->createHomemadeExplosivesAnalysis();

        // 9 Jurnal
        $this->createBlastWaveJournalArticle();
        $this->createTNTDetonationStudy();
        $this->createGroundVibrationResearch();
        $this->createExplosiveResidueAnalysis();
        $this->createShockWaveModeling();
        $this->createFragmentationPatternStudy();
        $this->createElectronicDetonatorEfficiency();
        $this->createCraterFormationResearch();
        $this->createBlastMitigationStructures();

        // 9 Opini
        $this->createMiningBlastingOpinion();
        $this->createUrbanBlastingRegulation();
        $this->createTerrorismPreventionStrategy();
        $this->createExplosiveStorageSafety();
        $this->createBlastingEnvironmentalImpact();
        $this->createModernDetonatorTechnology();
        $this->createQuarryBlastingStandards();
        $this->createCivilianExplosiveControl();
        $this->createBlastProtectionInfrastructure();
    }

    // ========================================
    // BUKU (9 publikasi)
    // ========================================

    /**
     * Buku 1: Forensik Ledakan
     */
    private function createExplosivesForensicsBook(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'studi-kasus')->first()->id,
            'title' => 'Forensik Ledakan: Investigasi Pasca Bom di Indonesia',
            'slug' => Str::slug('Forensik Ledakan: Investigasi Pasca Bom di Indonesia'),
            'abstract' => 'Buku ini membahas secara komprehensif tentang teknik investigasi forensik ledakan yang diterapkan di Indonesia. Mencakup metodologi pengumpulan bukti, analisis residu bahan peledak, dan rekonstruksi kejadian ledakan berdasarkan kasus-kasus nyata yang terjadi di Indonesia selama periode 2010-2024. Dilengkapi dengan studi kasus Bom Bali, Sarinah, dan Surabaya.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(6),
            'cover_image_path' => 'publications/covers/cover-jurnal1.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'crime-bombing-cases',
            'post-blast-investigation',
            'explosives-forensics-trace-analysis'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'post-blast-investigation',
            'forensic-analysis',
            'explosive-residue',
            'crime-scene',
            'bombing'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Ahmad Fauzi, M.Sc.', 'email' => 'ahmad.fauzi@forensik.ac.id', 'affiliation' => 'Universitas Gadjah Mada - Forensik', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Prof. Budi Santoso, Ph.D', 'email' => 'budi.santoso@itb.ac.id', 'affiliation' => 'Institut Teknologi Bandung - Teknik Kimia', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Expert in explosives forensics and investigation.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Buku 2: Blast Engineering Handbook
     */
    private function createBlastEngineeringHandbook(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'literatur-review')->first()->id,
            'title' => 'Handbook of Blast Engineering: Theory and Applications',
            'slug' => Str::slug('Handbook of Blast Engineering: Theory and Applications'),
            'abstract' => 'Panduan komprehensif tentang teknik peledakan yang mencakup teori dasar gelombang ledakan, perhitungan scaled distance, desain struktur tahan ledakan, hingga aplikasi praktis dalam industri konstruksi dan pertahanan. Buku ini dilengkapi dengan 150+ diagram, grafik Kingery-Bulmash, dan studi kasus internasional dari proyek infrastruktur besar.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(12),
            'cover_image_path' => 'publications/covers/cover-jurnal2.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'detonation-blast-physics',
            'shock-waves-overpressure-modeling',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'blast-wave',
            'shock-wave',
            'blast-engineering',
            'structural-protection',
            'explosion'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Prof. Dr. Ir. Wahyu Kristanto, M.Eng.', 'email' => 'wahyu.k@itb.ac.id', 'affiliation' => 'Institut Teknologi Bandung - Teknik Sipil', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Siti Nurhaliza, S.T., M.T.', 'email' => 'siti.n@ui.ac.id', 'affiliation' => 'Universitas Indonesia - Teknik Sipil', 'order' => 2, 'is_corresponding' => false],
            ['name' => 'Dr. Bambang Setiawan, M.Sc.', 'email' => 'bambang.s@ugm.ac.id', 'affiliation' => 'UGM - Teknik Mesin', 'order' => 3, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Specialist in blast-resistant structural engineering.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Buku 3: Military Explosives Guide
     */
    private function createMilitaryExplosivesGuide(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Panduan Bahan Peledak Militer: Dari TNT hingga RDX',
            'slug' => Str::slug('Panduan Bahan Peledak Militer: Dari TNT hingga RDX'),
            'abstract' => 'Buku referensi lengkap tentang karakteristik bahan peledak militer termasuk TNT, RDX, HMX, PETN, dan Composition B. Membahas sifat kimia, sensitivitas, kecepatan detonasi, dan aplikasi taktis masing-masing bahan peledak. Dilengkapi dengan protokol keamanan penyimpanan dan prosedur pemusnahan sesuai standar NATO dan TNI.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(18),
            'cover_image_path' => 'publications/covers/cover-jurnal3.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'military-commercial-industrial-explosives',
            'detonation-blast-physics',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'tnt',
            'rdx',
            'military-explosives',
            'detonation',
            'explosive-materials'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Letkol Ckm Dr. Agung Prasetyo, S.T., M.T.', 'email' => 'agung.p@tni.mil.id', 'affiliation' => 'TNI AD - Pusat Zeni', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Eng. Rudi Hermawan', 'email' => 'rudi.h@lemhannas.go.id', 'affiliation' => 'Lemhannas RI - Kajian Pertahanan', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Military explosives expert with extensive field experience.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Buku 4: IED Detection Techniques
     */
    private function createIEDDetectionTechniques(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'studi-kasus')->first()->id,
            'title' => 'Teknik Deteksi dan Penjinakan Bom Rakitan (IED)',
            'slug' => Str::slug('Teknik Deteksi dan Penjinakan Bom Rakitan IED'),
            'abstract' => 'Buku praktis untuk petugas Gegana dan EOD yang membahas teknik deteksi improvised explosive devices (IED) menggunakan X-ray, K9 detection, electronic sniffers, dan ground-penetrating radar. Mencakup prosedur render safe procedures (RSP), penggunaan bomb suit, dan studi kasus penanganan IED di wilayah konflik dan perkotaan Indonesia.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(9),
            'cover_image_path' => 'publications/covers/cover-jurnal4.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'ied-improvised-explosives',
            'post-blast-investigation',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'ied',
            'bomb-disposal',
            'explosive-detection',
            'render-safe',
            'counter-terrorism'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'AKBP Dwi Hartanto, S.I.K., M.H.', 'email' => 'dwi.hartanto@polri.go.id', 'affiliation' => 'Polri - Gegana Brimob', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Mayor Ckm Yudi Setiawan, S.T.', 'email' => 'yudi.s@tni.mil.id', 'affiliation' => 'TNI AD - Den Jihandak', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Certified EOD technician with 12+ years operational experience.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Buku 5: Pyrotechnics Chemistry
     */
    private function createPyrotechnicsChemistry(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Kimia Piroteknik: Dari Kembang Api hingga Sinyal Militer',
            'slug' => Str::slug('Kimia Piroteknik: Dari Kembang Api hingga Sinyal Militer'),
            'abstract' => 'Buku yang mengulas kimia di balik komposisi piroteknik, termasuk oksidator (kalium perklorat, kalium nitrat), bahan bakar (aluminium, magnesium), dan pewarna api. Membahas formulasi untuk berbagai aplikasi: kembang api komersial, flare maritim, smoke grenade, dan illumination rounds. Dilengkapi dengan perhitungan stoikiometri dan prosedur mixing yang aman.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(15),
            'cover_image_path' => 'publications/covers/cover-jurnal5.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'pyrotechnics-fireworks-propellants',
            'military-commercial-industrial-explosives'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'pyrotechnics',
            'fireworks',
            'oxidizers',
            'metal-fuels',
            'color-flames'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Ir. Andi Wijaya, M.T.', 'email' => 'andi.w@itb.ac.id', 'affiliation' => 'ITB - Teknik Kimia', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Ratna Sari, S.Si., M.Si.', 'email' => 'ratna.s@ui.ac.id', 'affiliation' => 'Universitas Indonesia - Kimia', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Pyrotechnic chemistry researcher and formulation specialist.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Buku 6: Mining Explosives Manual
     */
    private function createMiningExplosivesManual(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Manual Peledakan Tambang: ANFO, Emulsi, dan Water Gel',
            'slug' => Str::slug('Manual Peledakan Tambang: ANFO, Emulsi, dan Water Gel'),
            'abstract' => 'Panduan praktis untuk blaster dan supervisor tambang tentang penggunaan bahan peledak komersial: ANFO (Ammonium Nitrate Fuel Oil), emulsion explosives, dan water gel. Membahas blast design, burden-spacing calculation, stemming materials, fragmentation optimization, dan troubleshooting misfire. Dilengkapi dengan standar keselamatan ESDM dan case study dari tambang batubara Kalimantan dan nikel Sulawesi.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(10),
            'cover_image_path' => 'publications/covers/cover-jurnal6.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'mining-quarry-blasting',
            'military-commercial-industrial-explosives',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'mining-blasting',
            'anfo',
            'emulsion-explosives',
            'blast-design',
            'fragmentation'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Ir. Hendra Gunawan, M.Eng.', 'email' => 'hendra.g@itb.ac.id', 'affiliation' => 'ITB - Teknik Pertambangan', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Ir. Supriyanto, M.T.', 'email' => 'supriyanto@upnvj.ac.id', 'affiliation' => 'UPN Veteran Jakarta - Teknik Pertambangan', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Mining engineer with 15+ years experience in blasting operations.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Buku 7: Explosives Safety Protocols
     */
    private function createExplosivesSafetyProtocols(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'literatur-review')->first()->id,
            'title' => 'Protokol Keselamatan Bahan Peledak: Penyimpanan hingga Transport',
            'slug' => Str::slug('Protokol Keselamatan Bahan Peledak: Penyimpanan hingga Transport'),
            'abstract' => 'Kompilasi lengkap regulasi dan best practices keselamatan bahan peledak berdasarkan UN Orange Book, ATF regulations, dan Permenaker RI. Membahas desain magazine storage, compatibility groups, quantity-distance tables, security measures, transportation placarding, dan emergency response procedures. Buku wajib baca untuk HSE officer dan explosive handlers.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(8),
            'cover_image_path' => 'publications/covers/cover-jurnal7.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'explosion-safety-risk-assessment',
            'military-commercial-industrial-explosives'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'explosive-safety',
            'storage-regulations',
            'transportation',
            'hazmat',
            'risk-management'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Ir. Joko Susilo, M.K3., CFPS', 'email' => 'joko.s@k3institute.ac.id', 'affiliation' => 'Institut K3 Indonesia', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Rina Kusuma, S.T., M.T.', 'email' => 'rina.k@trisakti.ac.id', 'affiliation' => 'Universitas Trisakti - Teknik Industri', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Certified explosives safety specialist and HSE consultant.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Buku 8: Blast Injury Medicine
     */
    private function createBlastInjuryMedicine(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'studi-kasus')->first()->id,
            'title' => 'Kedokteran Trauma Ledakan: Penanganan Primary hingga Quaternary Blast Injury',
            'slug' => Str::slug('Kedokteran Trauma Ledakan: Penanganan Primary hingga Quaternary Blast Injury'),
            'abstract' => 'Buku kedokteran bencana yang membahas klasifikasi blast injury (primary, secondary, tertiary, quaternary), patofisiologi trauma akibat overpressure, penetrating fragment wounds, dan crush injuries. Mencakup triage di lokasi kejadian, stabilisasi airway pada blast lung injury, manajemen luka bakar, dan rehabilitation post-blast trauma. Dilengkapi clinical case dari serangan teror dan kecelakaan industri.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(11),
            'cover_image_path' => 'publications/covers/cover-jurnal8.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'post-blast-investigation',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'blast-injury',
            'trauma-medicine',
            'emergency-response',
            'overpressure-damage',
            'blast-lung'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. dr. Sari Wijayanti, Sp.BP-RE(K)', 'email' => 'sari.w@rspad.mil.id', 'affiliation' => 'RSPAD Gatot Soebroto - Bedah Plastik', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Prof. Dr. dr. Benny Wiharta, Sp.B(K)Trauma', 'email' => 'benny.w@fk.ui.ac.id', 'affiliation' => 'FKUI - Bedah Trauma', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Trauma surgeon specializing in blast-related injuries.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Buku 9: Homemade Explosives Analysis
     */
    private function createHomemadeExplosivesAnalysis(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'buku')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Analisis Bahan Peledak Rakitan: Deteksi TATP, HMTD, dan Peroksida Organik',
            'slug' => Str::slug('Analisis Bahan Peledak Rakitan: Deteksi TATP, HMTD, dan Peroksida Organik'),
            'abstract' => 'Buku teknis untuk analis forensik tentang identifikasi homemade explosives (HME) menggunakan GC-MS, FTIR, Raman spectroscopy, dan ion chromatography. Fokus pada peroxide-based explosives (TATP, HMTD), urea nitrate, dan chlorate mixtures yang sering digunakan dalam IED. Mencakup sampling techniques, degradation products analysis, dan courtroom testimony preparation.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(5),
            'cover_image_path' => null,
        ]);

        $categories = Category::whereIn('slug', [
            'ied-improvised-explosives',
            'explosives-forensics-trace-analysis',
            'post-blast-investigation'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'homemade-explosives',
            'tatp',
            'forensic-analysis',
            'gc-ms',
            'trace-detection'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Maya Kusumawati, M.Si.', 'email' => 'maya.k@labfor.polri.go.id', 'affiliation' => 'Labfor Polri - Kimia Forensik', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Eng. Arief Rahman, S.Si., M.Sc.', 'email' => 'arief.r@lipi.go.id', 'affiliation' => 'BRIN - Kimia Analitik', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Forensic chemist specializing in explosives trace analysis.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    // ========================================
    // JURNAL (9 publikasi)
    // ========================================

    /**
     * Jurnal 1: Blast Wave Characteristics
     */
    private function createBlastWaveJournalArticle(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Karakteristik Gelombang Ledakan TNT di Ruang Terbuka: Studi Eksperimental',
            'slug' => Str::slug('Karakteristik Gelombang Ledakan TNT di Ruang Terbuka'),
            'abstract' => 'Penelitian ini mengkaji karakteristik propagasi gelombang ledakan TNT dengan massa 100-500 gram di area terbuka. Pengukuran tekanan overpressure dan impulse dilakukan menggunakan sensor piezoelektrik pada berbagai jarak scaled distance. Hasil menunjukkan korelasi kuat antara scaled distance dengan peak overpressure, dengan deviasi maksimal 8% dari prediksi empiris Kingery-Bulmash.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(3),
            'cover_image_path' => 'publications/covers/cover-jurnal1.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'detonation-blast-physics',
            'shock-waves-overpressure-modeling',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'blast-wave',
            'overpressure',
            'tnt',
            'shock-wave',
            'scaled-distance',
            'explosion'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Eng. Rizki Maulana', 'email' => 'rizki.m@its.ac.id', 'affiliation' => 'ITS - Teknik Fisika', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Sarah Kartika, S.T., M.T.', 'email' => 'sarah.k@ugm.ac.id', 'affiliation' => 'UGM - Teknik Mesin', 'order' => 2, 'is_corresponding' => false],
            ['name' => 'Dr. Indra Wijaya', 'email' => 'indra.w@ui.ac.id', 'affiliation' => 'Universitas Indonesia - Fisika', 'order' => 3, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Researcher in blast physics and explosive engineering.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Jurnal 2: TNT Detonation Velocity
     */
    private function createTNTDetonationStudy(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Pengaruh Densitas terhadap Kecepatan Detonasi TNT: Eksperimen dengan Fiber Optic Probe',
            'slug' => Str::slug('Pengaruh Densitas terhadap Kecepatan Detonasi TNT'),
            'abstract' => 'Studi eksperimental untuk menentukan hubungan antara densitas TNT (1.50-1.64 g/cm³) dengan velocity of detonation (VOD) menggunakan continuous velocity probe berbasis fiber optic. Hasil menunjukkan VOD meningkat linear dari 6,850 m/s hingga 7,100 m/s seiring peningkatan densitas, dengan coefficient of determination R² = 0.982. Temuan ini penting untuk optimasi performa TNT dalam aplikasi demolisi.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(7),
            'cover_image_path' => 'publications/covers/cover-jurnal2.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'detonation-blast-physics',
            'military-commercial-industrial-explosives'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'tnt',
            'detonation-velocity',
            'density-effect',
            'fiber-optic-probe',
            'explosive-performance'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Prof. Dr. Ir. Gunawan Santoso, M.Sc.', 'email' => 'gunawan.s@itb.ac.id', 'affiliation' => 'ITB - Teknik Kimia', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dian Pramudya, S.T., M.T.', 'email' => 'dian.p@its.ac.id', 'affiliation' => 'ITS - Teknik Fisika', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Expert in detonation physics and explosive characterization.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Jurnal 3: Ground Vibration from Blasting
     */
    private function createGroundVibrationResearch(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Prediksi Ground Vibration dari Peledakan Tambang Menggunakan Regresi Multilinear dan ANN',
            'slug' => Str::slug('Prediksi Ground Vibration dari Peledakan Tambang'),
            'abstract' => 'Penelitian ini membandingkan akurasi prediksi peak particle velocity (PPV) menggunakan multiple linear regression (MLR) dengan artificial neural network (ANN) pada 85 blast events di tambang batubara Kalimantan Timur. Parameter input meliputi maximum charge per delay, distance, dan burden-spacing ratio. Model ANN menunjukkan akurasi superior dengan R² = 0.91 dibanding MLR (R² = 0.78), dan mean absolute error 22% lebih rendah.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(4),
            'cover_image_path' => 'publications/covers/cover-jurnal3.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'mining-quarry-blasting',
            'shock-waves-overpressure-modeling',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'ground-vibration',
            'peak-particle-velocity-ppv',
            'mining-blasting',
            'artificial-neural-network',
            'vibration-prediction'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Ir. Eko Prasetyo, M.T.', 'email' => 'eko.p@upnvj.ac.id', 'affiliation' => 'UPN Veteran Jakarta - Teknik Pertambangan', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Fitri Handayani, S.T., M.Eng.', 'email' => 'fitri.h@itb.ac.id', 'affiliation' => 'ITB - Teknik Pertambangan', 'order' => 2, 'is_corresponding' => false],
            ['name' => 'Ir. Bambang Susilo, M.T.', 'email' => 'bambang.s@adaro.co.id', 'affiliation' => 'PT Adaro Energy - Blasting Engineer', 'order' => 3, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Mining engineering researcher specializing in blast vibration control.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Jurnal 4: Explosive Residue Detection
     */
    private function createExplosiveResidueAnalysis(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Deteksi Residu PETN dan RDX Pasca Ledakan Menggunakan LC-MS/MS dengan SPE Cleanup',
            'slug' => Str::slug('Deteksi Residu PETN dan RDX Pasca Ledakan'),
            'abstract' => 'Metode analisis residu bahan peledak PETN dan RDX pada sampel post-blast debris menggunakan liquid chromatography-tandem mass spectrometry (LC-MS/MS) dengan solid phase extraction (SPE) cleanup. Limit of detection mencapai 0.05 ng/mL untuk PETN dan 0.08 ng/mL untuk RDX. Metode divalidasi pada 32 sampel dari simulasi ledakan Composition C-4, dengan recovery rate 87-94% dan repeatability RSD < 6%.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(2),
            'cover_image_path' => 'publications/covers/cover-jurnal4.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'explosives-forensics-trace-analysis',
            'post-blast-investigation'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'explosive-residue',
            'lc-ms-ms',
            'petn',
            'rdx',
            'forensic-analysis',
            'trace-detection'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Nur Azizah, M.Si.', 'email' => 'nur.a@labfor.polri.go.id', 'affiliation' => 'Labfor Polri - Kimia Forensik', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Prof. Dr. Hadi Pranoto, M.Sc.', 'email' => 'hadi.p@ugm.ac.id', 'affiliation' => 'UGM - Kimia', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Forensic analytical chemist specializing in explosives detection.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Jurnal 5: Shock Wave Modeling
     */
    private function createShockWaveModeling(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'simulasi-komputasi')->first()->id,
            'title' => 'Simulasi CFD Propagasi Shock Wave dalam Struktur Urban: Validasi dengan Data Eksperimental',
            'slug' => Str::slug('Simulasi CFD Propagasi Shock Wave dalam Struktur Urban'),
            'abstract' => 'Penelitian ini mengembangkan model computational fluid dynamics (CFD) menggunakan ANSYS Fluent untuk memprediksi propagasi shock wave di lingkungan urban dengan konfigurasi bangunan kompleks. Model menggunakan persamaan Euler dengan k-ε turbulence model dan adaptive mesh refinement. Validasi terhadap data eksperimental shock tube menunjukkan akurasi prediksi overpressure dengan error < 12% dan good agreement untuk arrival time dan impulse.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(5),
            'cover_image_path' => 'publications/covers/cover-jurnal5.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'shock-waves-overpressure-modeling',
            'detonation-blast-physics',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'shock-wave',
            'cfd-simulation',
            'urban-blast',
            'overpressure',
            'ansys-fluent'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Eng. Fajar Kurniawan, S.T., M.Eng.', 'email' => 'fajar.k@itb.ac.id', 'affiliation' => 'ITB - Teknik Mesin', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Lestari Widodo, S.T., M.T.', 'email' => 'lestari.w@ui.ac.id', 'affiliation' => 'UI - Teknik Sipil', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Computational mechanics researcher in blast wave simulation.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Jurnal 6: Fragmentation Pattern Study
     */
    private function createFragmentationPatternStudy(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Analisis Pola Fragmentasi Batuan Granit pada Blasting dengan Variasi Burden-Spacing Ratio',
            'slug' => Str::slug('Analisis Pola Fragmentasi Batuan Granit pada Blasting'),
            'abstract' => 'Studi lapangan di quarry granit untuk menganalisis pengaruh burden-spacing ratio (0.8:1, 1:1, 1.2:1) terhadap distribusi ukuran fragmen batuan menggunakan image analysis software Split-Desktop. Sebanyak 45 blast rounds dianalisis dengan total volume batuan 28,500 m³. Hasil menunjukkan ratio 1:1 menghasilkan fragmentasi optimal dengan d₅₀ = 42 cm, uniformity index 1.8, dan oversize (<80 cm) minimal 3.2%. Produktivitas crusher meningkat 18% dengan ratio optimal.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(6),
            'cover_image_path' => 'publications/covers/cover-jurnal6.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'mining-quarry-blasting'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'fragmentation',
            'blast-design',
            'burden-spacing',
            'image-analysis',
            'mining-blasting'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Ir. Teguh Prasetyo, M.T.', 'email' => 'teguh.p@upnvj.ac.id', 'affiliation' => 'UPN Veteran Jakarta - Teknik Pertambangan', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Ir. Rini Setiawati, M.Eng.', 'email' => 'rini.s@itb.ac.id', 'affiliation' => 'ITB - Teknik Pertambangan', 'order' => 2, 'is_corresponding' => false],
            ['name' => 'Muhammad Iqbal, S.T., M.T.', 'email' => 'iqbal.m@holcim.com', 'affiliation' => 'PT Holcim Indonesia - Quarry Manager', 'order' => 3, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Mining engineer with expertise in rock fragmentation and blast optimization.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Jurnal 7: Electronic Detonator Performance
     */
    private function createElectronicDetonatorEfficiency(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Perbandingan Performa Electronic Detonator vs Nonel pada Blasting Tambang Terbuka',
            'slug' => Str::slug('Perbandingan Performa Electronic Detonator vs Nonel'),
            'abstract' => 'Penelitian komparatif antara electronic detonator dan nonel shock tube pada 60 production blasts di tambang nikel Sulawesi. Parameter evaluasi meliputi timing accuracy, ground vibration (PPV), fragmentation quality, dan misfires. Electronic detonator menunjukkan timing precision ±1 ms (vs ±20 ms nonel), PPV reduction 35%, d₅₀ improvement 12%, dan zero misfires. Meskipun biaya 4x lebih tinggi, total blast cost per ton ore turun 8% karena efisiensi downstream processes.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(3),
            'cover_image_path' => 'publications/covers/cover-jurnal7.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'mining-quarry-blasting',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'electronic-detonator',
            'blasting-optimization',
            'mining-blasting',
            'blast-timing',
            'ground-vibration'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Ir. Agus Setiawan, M.T.', 'email' => 'agus.s@itb.ac.id', 'affiliation' => 'ITB - Teknik Pertambangan', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Ir. Wawan Kurniawan, M.Eng.', 'email' => 'wawan.k@valeni.co.id', 'affiliation' => 'PT Vale Indonesia - Blasting Superintendent', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Mining engineer with focus on blast optimization and modern detonator technology.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Jurnal 8: Crater Formation Mechanics
     */
    private function createCraterFormationResearch(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Studi Pembentukan Kawah Ledakan pada Tanah Kohesif: Pengaruh Depth of Burial dan Charge Weight',
            'slug' => Str::slug('Studi Pembentukan Kawah Ledakan pada Tanah Kohesif'),
            'abstract' => 'Eksperimen skala laboratorium untuk mengkaji pengaruh depth of burial (DOB) dan charge weight terhadap dimensi kawah pada tanah lempung dengan undrained shear strength 45 kPa. Sebanyak 36 test shots dengan TNT 50-400 gram dan DOB/R ratio 0.5-4.0 dilakukan. Crater apparent volume maksimal dicapai pada optimum DOB/R = 1.8-2.2. Hasil dimodelkan dengan persamaan regresi power-law dengan R² = 0.89, berguna untuk prediksi dalam aplikasi land mine clearance dan construction blasting.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(8),
            'cover_image_path' => 'publications/covers/cover-jurnal8.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'detonation-blast-physics',
            'military-commercial-industrial-explosives'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'crater-formation',
            'depth-of-burial',
            'soil-mechanics',
            'explosion',
            'blast-effects'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Ir. Yudha Pratama, M.T.', 'email' => 'yudha.p@its.ac.id', 'affiliation' => 'ITS - Teknik Sipil', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Prof. Dr. Ir. Heru Purnomo, M.Sc.', 'email' => 'heru.p@ugm.ac.id', 'affiliation' => 'UGM - Teknik Geologi', 'order' => 2, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Geotechnical engineer researching blast effects on soils.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Jurnal 9: Blast Mitigation Structures
     */
    private function createBlastMitigationStructures(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'jurnal')->first()->id,
            'method_id' => Method::where('slug', 'eksperimental')->first()->id,
            'title' => 'Efektivitas Sandwich Panel dengan Core Aluminium Foam untuk Mitigasi Blast Loading',
            'slug' => Str::slug('Efektivitas Sandwich Panel dengan Core Aluminium Foam'),
            'abstract' => 'Pengujian eksperimental sandwich panel dengan face sheet steel ASTM A36 dan core aluminium foam (densitas 0.3-0.6 g/cm³) terhadap blast loading dari charge TNT 100 gram pada standoff distance 50-100 cm. Panel dengan foam density 0.45 g/cm³ dan total thickness 50 mm menunjukkan performa optimal dengan energy absorption 78%, permanent deflection 18 mm, dan tidak mengalami tearing. Struktur ini prospektif untuk aplikasi protective barriers di fasilitas kritis.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(4),
            'cover_image_path' => null,
        ]);

        $categories = Category::whereIn('slug', [
            'detonation-blast-physics',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'blast-mitigation',
            'sandwich-panel',
            'aluminium-foam',
            'protective-structure',
            'energy-absorption'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Eng. Rizal Firmansyah, S.T., M.Eng.', 'email' => 'rizal.f@itb.ac.id', 'affiliation' => 'ITB - Teknik Mesin', 'order' => 1, 'is_corresponding' => true],
            ['name' => 'Dr. Dedi Setiawan, S.T., M.T.', 'email' => 'dedi.s@ui.ac.id', 'affiliation' => 'UI - Teknik Metalurgi', 'order' => 2, 'is_corresponding' => false],
            ['name' => 'Dr. Ir. Anto Prasetyo, M.Sc.', 'email' => 'anto.p@bppt.go.id', 'affiliation' => 'BPPT - Material Engineering', 'order' => 3, 'is_corresponding' => false],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Materials engineer specializing in blast-resistant structures.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    // ========================================
    // OPINI (9 publikasi)
    // ========================================

    /**
     * Opini 1: Mining Blasting Optimization
     */
    private function createMiningBlastingOpinion(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Optimalisasi Peledakan Tambang: Antara Produktivitas dan Keselamatan Lingkungan',
            'slug' => Str::slug('Optimalisasi Peledakan Tambang'),
            'abstract' => 'Industri pertambangan Indonesia menghadapi dilema antara meningkatkan produktivitas melalui peledakan masif dengan menjaga keselamatan lingkungan. Artikel opini ini membahas perlunya regulasi yang lebih ketat terkait peak particle velocity (PPV) dan ground vibration, serta pentingnya adopsi teknologi electronic detonator untuk mengurangi dampak negatif peledakan terhadap pemukiman sekitar tambang.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(2),
            'cover_image_path' => 'publications/covers/cover-jurnal1.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'mining-quarry-blasting',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'mining-blasting',
            'ground-vibration',
            'peak-particle-velocity-ppv',
            'electronic-detonator',
            'blasting-optimization',
            'blast-safety'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Ir. Hendra Gunawan, M.Eng.', 'email' => 'hendra.g@itb.ac.id', 'affiliation' => 'ITB - Teknik Pertambangan', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Mining engineer with 15+ years experience in blasting operations.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Opini 2: Urban Blasting Regulation
     */
    private function createUrbanBlastingRegulation(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Perlunya Standarisasi Controlled Blasting untuk Proyek Konstruksi di Area Urban',
            'slug' => Str::slug('Perlunya Standarisasi Controlled Blasting untuk Proyek Konstruksi'),
            'abstract' => 'Meningkatnya proyek infrastruktur di wilayah padat penduduk memerlukan teknik controlled blasting yang ketat. Artikel ini mengusulkan adoption of Swedish blasting standards dan pre-split/trim blasting techniques untuk meminimalkan ground vibration dan flyrock hazards. Pemerintah perlu mewajibkan seismic monitoring dan structural inspection pra-pasca peledakan dalam peraturan konstruksi nasional.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(4),
            'cover_image_path' => 'publications/covers/cover-jurnal2.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'explosion-safety-risk-assessment',
            'military-commercial-industrial-explosives'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'controlled-blasting',
            'urban-construction',
            'blast-safety',
            'ground-vibration',
            'regulation'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Ir. Bambang Widodo, M.T.', 'email' => 'bambang.w@ui.ac.id', 'affiliation' => 'UI - Teknik Sipil', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Civil engineer advocating for safer urban construction practices.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Opini 3: Terrorism Prevention Strategy
     */
    private function createTerrorismPreventionStrategy(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Penguatan Surveillance Precursor Chemicals untuk Mencegah Pembuatan Bom Rakitan',
            'slug' => Str::slug('Penguatan Surveillance Precursor Chemicals'),
            'abstract' => 'Kemudahan akses terhadap precursor chemicals seperti amonium nitrat, hidrogen peroksida, dan asam nitrat memfasilitasi pembuatan IED oleh kelompok teroris. Opini ini merekomendasikan implementasi sistem electronic tracking untuk penjualan bahan kimia sensitif, pelatihan deteksi suspicious transactions bagi retailers, dan kolaborasi Polri-BPOM dalam enforcement. Malaysia dan Singapura telah sukses menerapkan model serupa sejak 2012.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(6),
            'cover_image_path' => 'publications/covers/cover-jurnal3.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'ied-improvised-explosives',
            'crime-bombing-cases',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'precursor-chemicals',
            'ied',
            'counter-terrorism',
            'chemical-surveillance',
            'prevention'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Prof. Dr. Anton Setiawan, M.A.', 'email' => 'anton.s@ui.ac.id', 'affiliation' => 'UI - Kajian Terorisme', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Counter-terrorism expert and national security analyst.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Opini 4: Explosive Storage Safety
     */
    private function createExplosiveStorageSafety(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Urgensi Audit Magazin Penyimpanan Bahan Peledak Pasca Tragedi Beirut 2020',
            'slug' => Str::slug('Urgensi Audit Magazin Penyimpanan Bahan Peledak'),
            'abstract' => 'Ledakan dahsyat ammonium nitrate di Beirut (2020) dan Tianjin (2015) menyoroti bahaya penyimpanan bahan peledak yang tidak proper. Indonesia memiliki ratusan magazine storage tersebar di seluruh nusantara, namun compliance terhadap quantity-distance standards masih lemah. Artikel ini mendesak ESDM dan BNPB melakukan comprehensive audit terhadap semua explosive storage facilities, terutama yang berlokasi dekat pemukiman, dan enforcement terhadap violators.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(8),
            'cover_image_path' => 'publications/covers/cover-jurnal4.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'explosion-safety-risk-assessment',
            'military-commercial-industrial-explosives'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'explosive-storage',
            'magazine-safety',
            'ammonium-nitrate',
            'quantity-distance',
            'risk-assessment'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Ir. Sutopo Nugroho, M.K3., CFPS', 'email' => 'sutopo.n@bnpb.go.id', 'affiliation' => 'BNPB - Disaster Risk Reduction', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Disaster management expert focusing on industrial hazard prevention.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Opini 5: Blasting Environmental Impact
     */
    private function createBlastingEnvironmentalImpact(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Dampak Nitrogen Oxides dari Blasting terhadap Kualitas Udara di Zona Tambang',
            'slug' => Str::slug('Dampak Nitrogen Oxides dari Blasting terhadap Kualitas Udara'),
            'abstract' => 'Peledakan menggunakan ANFO dan emulsi nitrate menghasilkan nitrogen oxides (NOx) dan carbon monoxide yang berdampak pada kesehatan pekerja tambang dan masyarakat sekitar. Artikel ini menyoroti minimnya monitoring kualitas udara post-blast di Indonesia dan mengusulkan mandatory air quality assessment sesuai standar ACGIH. Penggunaan low-fume explosives dan improved ventilation systems di underground mines perlu diprioritaskan.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(10),
            'cover_image_path' => 'publications/covers/cover-jurnal5.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'mining-quarry-blasting',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'blast-fumes',
            'nitrogen-oxides',
            'air-quality',
            'mining-health',
            'environmental-impact'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Siti Rahmawati, S.T., M.T.', 'email' => 'siti.r@itb.ac.id', 'affiliation' => 'ITB - Teknik Lingkungan', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Environmental engineer researching mining pollution impacts.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Opini 6: Modern Detonator Technology
     */
    private function createModernDetonatorTechnology(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Saatnya Indonesia Beralih dari Electric Cap ke Electronic Detonator System',
            'slug' => Str::slug('Saatnya Indonesia Beralih dari Electric Cap ke Electronic Detonator'),
            'abstract' => 'Mayoritas operasi blasting di Indonesia masih menggunakan electric cap detonator yang prone to misfires dan accidental initiation dari stray currents atau lightning. Electronic detonator systems menawarkan superior safety dengan programmable timing, immunity to electromagnetic interference, dan individual blasthole monitoring. Meski investasi awal tinggi, long-term benefits dalam productivity dan safety sangat signifikan. Artikel ini mengajak industry players untuk accelerated adoption.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(5),
            'cover_image_path' => 'publications/covers/cover-jurnal6.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'mining-quarry-blasting',
            'military-commercial-industrial-explosives',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'electronic-detonator',
            'blasting-technology',
            'blast-safety',
            'initiation-systems',
            'modernization'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Ir. Dedi Kurniawan, M.Eng.', 'email' => 'dedi.k@orica.com', 'affiliation' => 'Orica Indonesia - Technical Services', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Blasting technology specialist with industry experience.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Opini 7: Quarry Blasting Standards
     */
    private function createQuarryBlastingStandards(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Mendesak Revisi SNI Peledakan Quarry: Belajar dari Best Practices Global',
            'slug' => Str::slug('Mendesak Revisi SNI Peledakan Quarry'),
            'abstract' => 'SNI 13-3481-2005 tentang peledakan quarry sudah tidak relevan dengan perkembangan teknologi dan safety standards terkini. Artikel opini ini membandingkan regulasi Indonesia dengan Australian AS 2187, European CEN standards, dan US MSHA regulations, dan mengidentifikasi gap kritik terutama dalam blast design documentation, mandatory vibration monitoring, dan blaster certification requirements. Revisi SNI yang comprehensive dan enforceable sangat mendesak untuk mencegah accidents.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(7),
            'cover_image_path' => 'publications/covers/cover-jurnal7.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'mining-quarry-blasting',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'blasting-standards',
            'quarry-operations',
            'regulation',
            'safety-compliance',
            'best-practices'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Prof. Ir. Wahyu Santoso, M.T., Ph.D.', 'email' => 'wahyu.s@itb.ac.id', 'affiliation' => 'ITB - Teknik Pertambangan', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Mining engineering professor and standards development consultant.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Opini 8: Civilian Explosive Control
     */
    private function createCivilianExplosiveControl(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Black Market Petasan dan Mercon: Ancaman Tersembunyi yang Terabaikan',
            'slug' => Str::slug('Black Market Petasan dan Mercon: Ancaman Tersembunyi'),
            'abstract' => 'Peredaran ilegal petasan dan mercon berkekuatan tinggi di Indonesia mencapai puncaknya menjelang perayaan keagamaan, mengakibatkan ratusan korban luka bakar dan amputasi setiap tahun. Artikel ini mengkritisi lemahnya enforcement terhadap Permendag tentang larangan petasan, dan mengusulkan strategi comprehensive meliputi raid terhadap production facilities di Jawa Timur dan Sumatera, public awareness campaigns, dan hukuman deterrent bagi producers dan distributors.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(3),
            'cover_image_path' => 'publications/covers/cover-jurnal8.jpg',
        ]);

        $categories = Category::whereIn('slug', [
            'pyrotechnics-fireworks-propellants',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'illegal-fireworks',
            'pyrotechnics',
            'public-safety',
            'enforcement',
            'black-market'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Dr. Rini Handayani, S.H., M.H.', 'email' => 'rini.h@ui.ac.id', 'affiliation' => 'UI - Hukum Pidana', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Criminal law expert focusing on explosive-related crimes.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }

    /**
     * Opini 9: Blast Protection Infrastructure
     */
    private function createBlastProtectionInfrastructure(): void
    {
        $publication = Publication::create([
            'publication_type_id' => PublicationType::where('slug', 'opini')->first()->id,
            'method_id' => null,
            'title' => 'Integrasi Blast-Resistant Design dalam Pembangunan Gedung Pemerintahan dan Fasilitas Publik',
            'slug' => Str::slug('Integrasi Blast-Resistant Design dalam Pembangunan Gedung'),
            'abstract' => 'Setelah serangan Sarinah 2016, kesadaran akan ancaman terorisme meningkat, namun pembangunan gedung pemerintahan dan fasilitas publik masih minim consideration terhadap blast resistance. Artikel ini mengadvokasi mandatory blast-resistant design guidelines untuk bangunan high-risk seperti kantor polisi, pengadilan, bandara, dan stasiun kereta. Implementasi standoff distance, progressive collapse prevention, dan laminated blast-resistant glazing harus menjadi building code requirements, belajar dari US GSA dan UK Home Office standards.',
            'status' => Publication::STATUS_PUBLISHED,
            'published_at' => now()->subWeeks(1),
            'cover_image_path' => null,
        ]);

        $categories = Category::whereIn('slug', [
            'detonation-blast-physics',
            'explosion-safety-risk-assessment'
        ])->get();
        $publication->categories()->attach($categories->pluck('id'));

        $keywords = Keyword::whereIn('slug', [
            'blast-resistant-design',
            'protective-structure',
            'building-security',
            'counter-terrorism',
            'infrastructure'
        ])->get();
        $publication->keywords()->attach($keywords->pluck('id'));

        $authors = [
            ['name' => 'Ir. Andi Prasetyo, M.T., Ph.D.', 'email' => 'andi.p@ui.ac.id', 'affiliation' => 'UI - Teknik Sipil', 'order' => 1, 'is_corresponding' => true],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'affiliation' => $authorData['affiliation'],
                    'bio' => 'Structural engineer specializing in blast-resistant building design.',
                ]
            );

            $publication->authors()->attach($author->id, [
                'order' => $authorData['order'],
                'is_corresponding' => $authorData['is_corresponding'],
            ]);
        }

        $this->command->info("✓ Created: {$publication->title}");
    }
}

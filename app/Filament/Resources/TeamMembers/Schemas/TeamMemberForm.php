<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ═══════════════════════════════════════════════════════════
                // SECTION 1 — Foto Profil (diletakkan paling atas agar
                //              user langsung tahu "siapa" yang sedang diedit)
                // ═══════════════════════════════════════════════════════════
                Section::make('Foto Profil')
                    ->description('Upload foto anggota tim. Foto akan di-crop otomatis menjadi persegi (1:1).')
                    ->icon(Heroicon::OutlinedCamera)
                    ->schema([

                        FileUpload::make('photo')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('team')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->imageEditor()                   // ← aktifkan editor bawaan Filament
                            ->imageEditorAspectRatios([       // ← paksa rasio 1:1
                                '1:1',
                            ])
                            ->imageEditorMode(2)              // ← mode 2 = crop saja (tanpa rotate/flip)
                            ->imageResizeMode('cover')        // ← pastikan hasil fill container
                            ->imageResizeTargetWidth(400)     // ← resolusi output optimal
                            ->imageResizeTargetHeight(400)
                            ->imagePreviewHeight('200')
                            ->panelAspectRatio('1:1')         // ← panel preview juga persegi
                            ->panelLayout('integrated')       // ← preview inline, lebih compact
                            ->helperText('Format: JPG, PNG, WebP. Maks. 2 MB. Klik ikon ✎ untuk crop / edit.')
                            ->nullable()
                            ->columnSpanFull(),

                    ]),

                // ═══════════════════════════════════════════════════════════
                // SECTION 2 — Informasi Utama
                // ═══════════════════════════════════════════════════════════
                Section::make('Informasi Utama')
                    ->description('Data identitas anggota yang ditampilkan di halaman tim.')
                    ->icon(Heroicon::OutlinedUser)
                    ->columns(2)
                    ->schema([

                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Contoh: Budi Santoso')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->columnSpan(1),

                        TextInput::make('title')
                            ->label('Jabatan / Posisi')
                            ->placeholder('Chief Executive Officer')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedBriefcase)
                            ->columnSpan(1),

                        Select::make('level')
                            ->label('Level Organisasi')
                            ->required()
                            ->live()
                            ->native(false)
                            ->options([
                                'leadership' => '👑  Leadership  (CEO / Pimpinan)',
                                'management' => '🏢  Management  (C-Level)',
                                'department' => '👥  Department  (Tim / Divisi)',
                            ])
                            ->default('department')
                            ->helperText('Level menentukan pengelompokan tampilan di halaman tim.')
                            ->columnSpan(1),

                        TextInput::make('department')
                            ->label('Nama Departemen')
                            ->placeholder('Engineering / Marketing / Operations')
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedBuildingOffice2)
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->label('Bio Singkat')
                            ->placeholder('Tuliskan deskripsi singkat mengenai peran dan keahlian anggota ini...')
                            ->rows(3)
                            ->maxLength(500)
                            ->nullable()
                            ->helperText('Maks. 500 karakter. Ditampilkan sebagai tooltip atau bagian profil.')
                            ->columnSpanFull(),

                    ]),

                // ═══════════════════════════════════════════════════════════
                // SECTION 3 — Kontak & Tautan
                // ═══════════════════════════════════════════════════════════
                Section::make('Kontak & Tautan')
                    ->description('Informasi kontak opsional yang dapat dihubungkan ke profil publik.')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->columns(2)
                    ->collapsible()          // ← bisa dilipat agar form tidak terlalu panjang
                    ->collapsed(false)
                    ->schema([

                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->placeholder('budi@perusahaan.com')
                            ->maxLength(255)
                            ->nullable()
                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                            ->columnSpan(1),

                        TextInput::make('linkedin')
                            ->label('LinkedIn URL')
                            ->url()
                            ->placeholder('https://linkedin.com/in/username')
                            ->maxLength(500)
                            ->nullable()
                            ->prefixIcon(Heroicon::OutlinedLink)
                            ->columnSpan(1),

                    ]),

                // ═══════════════════════════════════════════════════════════
                // SECTION 4 — Pengaturan Tampilan
                // (urutan & visibilitas dikelompokkan tersendiri agar tidak
                //  bercampur dengan data konten)
                // ═══════════════════════════════════════════════════════════
                Section::make('Pengaturan Tampilan')
                    ->description('Atur urutan dan visibilitas anggota ini di halaman publik.')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->columns(2)
                    ->collapsible()
                    ->schema([

                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Angka lebih kecil = tampil lebih awal.')
                            ->prefixIcon(Heroicon::OutlinedBarsArrowUp)
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Tampilkan di Halaman')
                            ->helperText('Nonaktifkan untuk menyembunyikan tanpa menghapus data.')
                            ->default(true)
                            ->onIcon(Heroicon::OutlinedEye)
                            ->offIcon(Heroicon::OutlinedEyeSlash)
                            ->onColor('success')
                            ->offColor('gray')
                            ->inline(false)
                            ->columnSpan(1),

                    ]),

                // ═══════════════════════════════════════════════════════════
                // SECTION 5 — Pengaturan Department Card
                // (hanya tampil jika level = department)
                // ═══════════════════════════════════════════════════════════
                Section::make('Pengaturan Kartu Departemen')
                    ->description('Konfigurasi ikon dan jumlah anggota yang ditampilkan pada kartu departemen.')
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->columns(2)
                    ->schema([

                        Select::make('icon_type')
                            ->label('Ikon Departemen')
                            ->native(false)
                            ->searchable()
                            ->nullable()
                            ->options([
                                'code'       => '💻  Pengembangan (Development)',
                                'content'    => '✍️   Konten (Content)',
                                'marketing'  => '📣  Pemasaran (Marketing)',
                                'operations' => '⚙️   Operasional (Operations)',
                                'support'    => '🎧  Dukungan (Support)',
                            ])
                            ->helperText('Ikon visual yang mewakili departemen ini di kartu.')
                            ->columnSpan(1),

                        TextInput::make('member_count')
                            ->label('Jumlah Anggota Tim')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('orang')
                            ->prefixIcon(Heroicon::OutlinedUsers)
                            ->helperText('Total anggota aktif dalam departemen ini.')
                            ->columnSpan(1),

                    ])
                    ->visible(fn(Get $get): bool => $get('level') === 'department'),

            ]);
    }
}

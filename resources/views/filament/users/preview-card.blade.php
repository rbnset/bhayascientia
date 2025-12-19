@php
use Livewire\TemporaryUploadedFile;

$name = $get('name') ?: 'Nama User';
$email = $get('email') ?: 'email@example.com';
$job = $get('job_title');
$wa = $get('whatsapp_number');

$photoState = $get('profile_photo');
$photoUrl = null;

// Upload langsung
if ($photoState instanceof TemporaryUploadedFile) {
$photoUrl = $photoState->temporaryUrl();
}
// Image editor (array)
elseif (is_array($photoState) && isset($photoState['path'])) {
$photoUrl = asset('storage/' . $photoState['path']);
}
// Sudah tersimpan
elseif (is_string($photoState)) {
$photoUrl = asset('storage/' . $photoState);
}
@endphp

<div class="profile-preview">
    <div class="profile-card">

        {{-- FOTO --}}
        @if ($photoUrl)
        <img src="{{ $photoUrl }}" class="profile-avatar-img" alt="Foto Profil">
        @else
        <div class="profile-avatar">
            {{ strtoupper(substr($name, 0, 1)) }}
        </div>
        @endif

        <div class="profile-info">
            <h3 class="profile-name">{{ $name }}</h3>
            <p class="profile-role">{{ $job ?: 'System User' }}</p>
            <p class="profile-email">{{ $email }}</p>
            @if ($wa)
            <p class="profile-wa">📱 {{ $wa }}</p>
            @endif
        </div>
    </div>
</div>

<style>
    .profile-preview {
        padding: 16px;
        border-radius: 16px
    }

    .profile-card {
        display: flex;
        gap: 16px;
        align-items: center;
        padding: 20px;
        border-radius: 14px
    }

    .profile-avatar,
    .profile-avatar-img {
        width: 72px;
        height: 72px;
        border-radius: 999px;
        flex-shrink: 0
    }

    .profile-avatar {
        background: #6366f1;
        color: #fff;
        font-size: 24px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center
    }

    .profile-avatar-img {
        object-fit: cover
    }

    .profile-name {
        font-size: 16px;
        font-weight: 600
    }

    .profile-role {
        font-size: 14px;
        font-weight: 500
    }

    .profile-email,
    .profile-wa {
        font-size: 13px
    }

    /* Light */
    .profile-preview {
        background: #f8fafc
    }

    .profile-card {
        background: #fff;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .08)
    }

    .profile-role {
        color: #4f46e5
    }

    .profile-email,
    .profile-wa {
        color: #64748b
    }

    /* Dark */
    .dark .profile-preview {
        background: #020617
    }

    .dark .profile-card {
        background: #020617;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .6)
    }

    .dark .profile-name {
        color: #f8fafc
    }

    .dark .profile-role {
        color: #a5b4fc
    }

    .dark .profile-email,
    .dark .profile-wa {
        color: #94a3b8
    }
</style>

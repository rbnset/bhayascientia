@php
$name = $get('name') ?: 'Nama Penulis';
$email = $get('email') ?: 'email@example.com';
$affiliation = $get('affiliation') ?: 'Author';
@endphp

<style>
    /* ===== Profile Preview (Inline) ===== */
    .profile-preview {
        padding: 16px;
        border-radius: 16px;
    }

    .profile-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        padding: 20px;
        border-radius: 14px;
        text-align: center;
    }

    .profile-avatar {
        width: 64px;
        height: 64px;
        border-radius: 999px;
        background: #6366f1;
        color: #fff;
        font-size: 22px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-info {
        width: 100%;
    }

    .profile-name {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .profile-role {
        margin-top: 4px;
        font-size: 14px;
        font-weight: 500;
    }

    .profile-email {
        margin-top: 2px;
        font-size: 13px;
        word-break: break-word;
    }

    /* ===== Desktop ===== */
    @media (min-width: 640px) {
        .profile-card {
            flex-direction: row;
            align-items: center;
            text-align: left;
            gap: 16px;
        }

        .profile-avatar {
            width: 72px;
            height: 72px;
            font-size: 24px;
            flex-shrink: 0;
        }
    }

    /* ===== Light Mode ===== */
    .profile-preview {
        background: #f8fafc;
    }

    .profile-card {
        background: #ffffff;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    }

    .profile-role {
        color: #4f46e5;
    }

    .profile-email {
        color: #64748b;
    }

    /* ===== Dark Mode (Filament) ===== */
    .dark .profile-preview {
        background: #020617;
    }

    .dark .profile-card {
        background: #020617;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .6);
    }

    .dark .profile-name {
        color: #f8fafc;
    }

    .dark .profile-role {
        color: #a5b4fc;
    }

    .dark .profile-email {
        color: #94a3b8;
    }
</style>

<div class="profile-preview">
    <div class="profile-card">
        <div class="profile-avatar">
            {{ strtoupper(substr($name, 0, 1)) }}
        </div>

        <div class="profile-info">
            <h3 class="profile-name">{{ $name }}</h3>
            <p class="profile-role">{{ $affiliation }}</p>
            <p class="profile-email">{{ $email }}</p>
        </div>
    </div>
</div>

<!DOCTYPE html>
<html>

<head>
    <title>Test Card</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="p-8 bg-gray-100">
    <div class="max-w-sm">
        <x-publication.card title="Test Placeholder Cover Design" cover="" category="Teknologi" publicationType="Jurnal"
            date="14 Februari 2026" :authors="[
                ['name' => 'John Doe', 'photo' => '', 'initials' => 'JD'],
                ['name' => 'Jane Smith', 'photo' => '', 'initials' => 'JS'],
            ]" :totalAuthors="2" detailUrl="#" slug="test-placeholder" />
    </div>
</body>

</html>
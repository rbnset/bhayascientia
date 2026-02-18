<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;


class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        TeamMember::truncate();

        // Leadership
        TeamMember::create([
            'name'        => 'Dr. Ahmad Setiawan',
            'title'       => 'Chief Executive Officer',
            'department'  => 'Leadership',
            'level'       => 'leadership',
            'email'       => 'ahmad@dabraka.id',
            'description' => 'Memimpin visi strategis dan pertumbuhan platform',
            'order'       => 1,
        ]);

        // Management
        TeamMember::create([
            'name'        => 'Budi Santoso, M.Kom',
            'title'       => 'Chief Technology Officer',
            'department'  => 'Management',
            'level'       => 'management',
            'email'       => 'budi@dabraka.id',
            'description' => 'Mengawasi pengembangan teknologi dan infrastruktur platform',
            'icon_type'   => 'code',
            'order'       => 1,
        ]);

        TeamMember::create([
            'name'        => 'Siti Nurhaliza, S.E., M.M.',
            'title'       => 'Chief Operating Officer',
            'department'  => 'Management',
            'level'       => 'management',
            'email'       => 'siti@dabraka.id',
            'description' => 'Mengelola operasional harian dan efisiensi organisasi',
            'icon_type'   => 'operations',
            'order'       => 2,
        ]);

        TeamMember::create([
            'name'        => 'Andi Wijaya, S.Sos., M.M.',
            'title'       => 'Chief Marketing Officer',
            'department'  => 'Management',
            'level'       => 'management',
            'email'       => 'andi@dabraka.id',
            'description' => 'Memimpin strategi pemasaran dan komunikasi brand',
            'icon_type'   => 'marketing',
            'order'       => 3,
        ]);

        // Departments
        $departments = [
            ['name' => 'Tim Pengembangan',  'title' => 'Development Team',  'desc' => 'Mengembangkan dan memelihara platform', 'icon' => 'code',       'count' => 8, 'order' => 1],
            ['name' => 'Tim Konten',         'title' => 'Content Team',       'desc' => 'Kurasi dan review publikasi ilmiah',   'icon' => 'content',    'count' => 6, 'order' => 2],
            ['name' => 'Tim Pemasaran',      'title' => 'Marketing Team',     'desc' => 'Strategi marketing dan outreach',      'icon' => 'marketing',  'count' => 5, 'order' => 3],
            ['name' => 'Tim Dukungan',       'title' => 'Support Team',       'desc' => 'Customer support dan bantuan teknis',  'icon' => 'support',    'count' => 4, 'order' => 4],
        ];

        foreach ($departments as $dept) {
            TeamMember::create([
                'name'         => $dept['name'],
                'title'        => $dept['title'],
                'department'   => 'Department',
                'level'        => 'department',
                'description'  => $dept['desc'],
                'icon_type'    => $dept['icon'],
                'member_count' => $dept['count'],
                'order'        => $dept['order'],
            ]);
        }
    }
}

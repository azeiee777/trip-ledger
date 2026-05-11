<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Category;

class CoreDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'CNG / Petrol',    'icon' => 'fuel',             'color' => '#F59E0B', 'is_personal' => false],
            ['name' => 'Toll',            'icon' => 'road',             'color' => '#6366F1', 'is_personal' => false],
            ['name' => 'Food & Drinks',   'icon' => 'utensils',         'color' => '#10B981', 'is_personal' => false],
            ['name' => 'Hotel / Stay',    'icon' => 'bed',              'color' => '#3B82F6', 'is_personal' => false],
            ['name' => 'Entry Tickets',   'icon' => 'ticket',           'color' => '#8B5CF6', 'is_personal' => false],
            ['name' => 'Parking',         'icon' => 'parking',          'color' => '#64748B', 'is_personal' => false],
            ['name' => 'Shopping',        'icon' => 'shopping-bag',     'color' => '#EC4899', 'is_personal' => true],
            ['name' => 'Cigarette',       'icon' => 'zap',              'color' => '#78716C', 'is_personal' => false],
            ['name' => 'Miscellaneous',   'icon' => 'more-horizontal',  'color' => '#9CA3AF', 'is_personal' => false],
            ['name' => 'Personal',        'icon' => 'user',             'color' => '#F97316', 'is_personal' => true],
            ['name' => 'Medical',         'icon' => 'heart',            'color' => '#EF4444', 'is_personal' => false],
            ['name' => 'Adventure',       'icon' => 'mountain',         'color' => '#14B8A6', 'is_personal' => false],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        $this->command->info('Categories seeded.');
    }
}

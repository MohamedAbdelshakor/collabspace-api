<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'UI/UX', 'slug' => 'ui-ux', 'color' => '#EC4899'],
            ['name' => 'Frontend', 'slug' => 'frontend', 'color' => '#3B82F6'],
            ['name' => 'Backend', 'slug' => 'backend', 'color' => '#10B981'],
            ['name' => 'Marketing', 'slug' => 'marketing', 'color' => '#F59E0B'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['slug' => $dept['slug']], $dept);
        }
    }
}

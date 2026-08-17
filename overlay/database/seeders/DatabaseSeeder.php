<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Database\Seeders\CoreDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreDatabaseSeeder::class,

            // ← سيدرز موديولات الدومين بتتضاف هنا في المرحلة 4
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Database\Seeders\CoreDatabaseSeeder;
use Modules\Seo\Database\Seeders\LandingPageSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreDatabaseSeeder::class,
            CatalogSeeder::class,
            BlogSeeder::class,
            LeadSeeder::class,
            // بعد الكتالوج: بتتبني من الوحدات المنشورة
            LandingPageSeeder::class,
        ]);
    }
}

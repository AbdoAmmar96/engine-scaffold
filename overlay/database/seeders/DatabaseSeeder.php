<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Database\Seeders\CoreDatabaseSeeder;
use Modules\Pages\Database\Seeders\PagesDatabaseSeeder;
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
            PagesDatabaseSeeder::class,
            // بعد الكتالوج: بتتبني من الوحدات المنشورة
            LandingPageSeeder::class,
        ]);
    }
}

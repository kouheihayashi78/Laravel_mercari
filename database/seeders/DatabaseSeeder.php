<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\ItemCondition;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        
        // User::factory(1)->create();
        // ItemCondition::factory(6)->create();

        $this->call(UserSeeder::class);
        $this->call(ItemConditionSeeder::class);
        $this->call(PrimaryCategorySeeder::class);
        $this->call(SecondaryCategorySeeder::class);
    }
}

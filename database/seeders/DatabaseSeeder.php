<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'knikolovv98@gmail.com',
            'password' => 'koko9899?',
            'owner' => true,
        ]);

        User::factory(5)->create();

        Strategy::create([
           'name' => 'BTC ETH'
        ]);
    }
}

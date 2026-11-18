<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $user = new User();
        $user->name = 'Test';
        $user->username = 'test01';
        $user->password = bcrypt('123456');
        $user->token = 'testtoken001';
        $user->credit = 0;
        $user->save();
    }
}

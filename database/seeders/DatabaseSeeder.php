<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserTypeSeeder::class,
        ]);

        $admin = array(
            array(
                'user_type_id' => 1,
                'email' => "dev@motoka.net",
                'phone_number' => "08169453935",
                'full_name' => 'Super Admin',
                'image' =>
                "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSyudBxqf1sdD2e3L4nI3nqsMt1_tceOyuZ7A&usqp=CAU",
                'password' => bcrypt('12345'),
                'email_verified_at' => Carbon::now(),
            )
        );


        foreach ($admin as $value) {
            $user = User::updateOrCreate($value);
        }
    }
}

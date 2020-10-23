<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        User::create([
            'name' => 'Alexius',
            'email' => 'alexiuslim1994@gmail.com',
            'password' => Hash::make('123456789'),
            'status' => User::STATUS_ACTIVE,
        ]);
    }
}

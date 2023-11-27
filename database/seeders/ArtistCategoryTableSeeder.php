<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ArtistCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::whereHas("roles", function ($q) {
            $q->where("name", "artist");
            $q->orWhere("name", "salon");
        })->get();

        foreach ($users as $key => $user) {
            DB::table('artist_categories')->insert(
                ['user_id' => $user->id, 'category_id' => 1]
            ); 
        }
    }
}

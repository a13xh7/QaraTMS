<?php

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $numberOfSuites = 100;
        while($numberOfSuites > 0) {
            $numberOfSuites--;

            DB::table('test_suites')->insert([
                'title' => Str::random(10),
                'project_id' => 1,
                'repository_id' => 1,
                'parent_id' => 1,
            ]);
        }

//        User::create([
//            'name' => 'Admin',
//            'email' => 'admin@admin.com',
//            'password' => Hash::make('password')
//        ]);

    }
}

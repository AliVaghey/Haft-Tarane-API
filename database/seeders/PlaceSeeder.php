<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::first();
        Place::create([
            'name' => 'مشهد',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'کرمان',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'اهواز',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'شیراز',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'تهران',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'تبریز',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'ساری',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'بیرجند',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'اصفهان',
            'author' => $author->username,
        ]);
        Place::create([
            'name' => 'یزد',
            'author' => $author->username,
        ]);
    }
}

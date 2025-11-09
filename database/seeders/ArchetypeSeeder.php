<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArchetypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $archetypes = [
            [
                'name' => 'The Innocent',
                'description' => 'Optimistic, honest, and seeks happiness. Fears doing something wrong or being punished.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Everyman',
                'description' => 'Relatable, humble, and values connection. Fears being left out or standing out from the crowd.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Hero',
                'description' => 'Courageous, bold, and seeks to prove their worth through difficult action. Fears weakness or vulnerability.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Caregiver',
                'description' => 'Compassionate, nurturing, and generous. Fears selfishness and ingratitude.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Explorer',
                'description' => 'Independent, authentic, and seeks freedom to find out who they are through exploring the world. Fears getting trapped and conformity.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Rebel',
                'description' => 'Revolutionary, wild, and seeks to overturn what isn’t working. Fears being powerless or ineffectual.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Lover',
                'description' => 'Passionate, intimate, and seeks connection and sensual pleasure. Fears being alone or unwanted.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Creator',
                'description' => 'Creative, imaginative, and seeks to realize a vision. Fears mediocre execution.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Jester',
                'description' => 'Playful, humorous, and seeks to live in the moment with full enjoyment. Fears being bored or boring others.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Sage',
                'description' => 'Wise, visionary, and seeks truth and understanding. Fears being duped or ignorant.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Magician',
                'description' => 'Visionary, charismatic, and seeks to understand the fundamental laws of the universe to make dreams come true. Fears unintended negative consequences.',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'The Ruler',
                'description' => 'Controlling, responsible, and seeks to create a prosperous, successful family or community. Fears chaos and being overthrown.',
                'created_at' => now(), 'updated_at' => now()
            ],
        ];

        // Assuming your table is named 'personality_traits'
        // If it's different, change it here.
        DB::table('personality_traits')->insert($archetypes);
    }
}
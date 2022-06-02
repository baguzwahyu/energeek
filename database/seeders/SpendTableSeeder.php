<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Spend;
use App\Models\SpendDetail;


class SpendTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $spend = Spend::create([
            'user_id'  => 1,
            'month'    => 1,
            'year'     => 2022
        ]);

        $spend = SpendDetail::create([
            'spend_id' => 1,
            'day' => '1',
            'total' => 20000,
            'description' => "Beli Bakso"
        ]);
    }
}

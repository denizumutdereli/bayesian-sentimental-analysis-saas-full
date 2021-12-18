<?php


class AccountTableSeeder extends Seeder {

    public function run()
    {
        DB::table('accounts')->delete();

        $data = [
            [
                'id' => 1, #default system account
                'accountType' => 'live',
                'name' => 'Sandbox',
                'package' => '1M',
                'api' => 1,
                'is_active' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                
            ],
        ];

        foreach ($data as $item)
        {
            Account::create($item);
        }
    }

}

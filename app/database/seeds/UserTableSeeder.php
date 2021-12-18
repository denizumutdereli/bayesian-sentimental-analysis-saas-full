<?php


class UserTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();

        $data = [
            [
                'account_id' => 1,
                'email' => 'deniz.umut.dereli@gmail.com',
                'password' => Hash::make('tr69km'),
                'role' => 'super',
                'permissions' => '["UserController.index","UserController.edit"]',
                'created_by' => 1,
                'updated_by' =>1
                ]
        ];

        foreach ($data as $item)
        {
            User::create($item);
        }
    }

}

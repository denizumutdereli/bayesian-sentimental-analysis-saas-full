<?php

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Eloquent::unguard();

        $this->call('AccountTableSeeder');
        $this->call('UserTableSeeder');

        $this->command->info('Tables seeded!');
    }

}

<?php

use Phinx\Seed\AbstractSeed;

class AStatesSeeder extends AbstractSeed
{
    public function run()
    {
        $states_json = file_get_contents(__DIR__ . "/../../config/states.json");
        $states = array_map(function($state) {
          return ['name' => $state];
        }, json_decode($states_json, true));
        $this->table('states')->truncate();
        $this->table('states')->insert($states)->save();
    }
}

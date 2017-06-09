<?php

use App\QueryBuilder as DB;
use Phinx\Seed\AbstractSeed;

class CSenatorsSeeder extends AbstractSeed
{
    public function run()
    {
        $senators_json = file_get_contents(__DIR__ . "/../../config/senators.json");
        $senators = array_map(function($senator) {
            $state = self::getStateByName($senator['state']);
            return [
                'state_id' => $state['id'],
                'name' => $senator['name'],
                'phone' => $senator['phone'],
            ];
        } , json_decode($senators_json, true));
        $this->table('senators')->truncate();
        $this->table('senators')->insert($senators)->save();
    }

    private static function getStateByName($name)
    {
        return DB::fromTable('states')
            ->where('name', $name)
            ->first();
    }
}

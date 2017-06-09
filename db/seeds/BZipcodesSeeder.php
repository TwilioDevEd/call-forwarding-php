<?php

use App\QueryBuilder as DB;
use Phinx\Seed\AbstractSeed;

class BZipcodesSeeder extends AbstractSeed
{
    public function run()
    {
        $zipcodes_csv = file(__DIR__ . '/../../config/free-zipcode-database.csv');
        $zipcodes = array_map(function($zipcode) {
            $state = self::getStateByName($zipcode[3]);
            return [
                'zipcode' => $zipcode[0],
                'state_id' => $state['id']
            ];
        }, array_slice(array_map('str_getcsv', $zipcodes_csv), 1));
        $this->table('zipcodes')->truncate();
        $this->table('zipcodes')->insert($zipcodes)->save();
    }

    private static function getStateByName($name)
    {
        return DB::fromTable('states')
            ->where('name', $name)
            ->first();
    }
}

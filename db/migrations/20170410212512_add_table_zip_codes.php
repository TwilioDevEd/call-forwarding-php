<?php

use Phinx\Migration\AbstractMigration;

class AddTableZipCodes extends AbstractMigration
{
    public function change()
    {
        $this->table('zipcodes')
             ->addColumn('zipcode', 'string', ['null' => false])
             ->addColumn('state_id', 'integer', ['null' => false])
             ->addForeignKey('state_id', 'states', 'id')
             ->create();
    }
}

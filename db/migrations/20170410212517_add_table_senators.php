<?php

use Phinx\Migration\AbstractMigration;

class AddTableSenators extends AbstractMigration
{
    public function change()
    {
        $this->table('senators')
             ->addColumn('state_id', 'integer', ['null' => false])
             ->addColumn('name', 'string', ['null' => false])
             ->addColumn('phone', 'string', ['null' => false])
             ->addForeignKey('state_id', 'states', 'id')
             ->create();
    }
}

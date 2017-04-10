<?php

use Phinx\Migration\AbstractMigration;

class AddTableStates extends AbstractMigration
{
    public function change()
    {
        $this->table('states')
             ->addColumn('name', 'string', ['null' => false])
             ->create();
    }
}

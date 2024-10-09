<?php

namespace AIMuseVendor\Illuminate\Database\PDO;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use AIMuseVendor\Illuminate\Database\PDO\Concerns\ConnectsToDatabase;

class MySqlDriver extends AbstractMySQLDriver
{
    use ConnectsToDatabase;
}

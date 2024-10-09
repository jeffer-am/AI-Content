<?php

namespace AIMuseVendor\Illuminate\Database\PDO;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use AIMuseVendor\Illuminate\Database\PDO\Concerns\ConnectsToDatabase;

class PostgresDriver extends AbstractPostgreSQLDriver
{
    use ConnectsToDatabase;
}

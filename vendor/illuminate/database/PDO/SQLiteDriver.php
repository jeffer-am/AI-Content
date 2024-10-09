<?php

namespace AIMuseVendor\Illuminate\Database\PDO;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use AIMuseVendor\Illuminate\Database\PDO\Concerns\ConnectsToDatabase;

class SQLiteDriver extends AbstractSQLiteDriver
{
    use ConnectsToDatabase;
}

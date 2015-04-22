# Curse - Cursor reader for cursed PostgreSQL's functions

If you have large result sets returned by a PostgreSQL's function maybe is time to return a cursor and try this tiny library to help you out.

How to use it
-------------

Composer installation coming soon!

This component was made to use its own connection (You need to have a own transaction in order to use cursors in PostgreSQL) so you will need to use the component's ```PDOConnection``` (a wrapper for [PDO](http://php.net/pdo) with less methods and functionality) or create your own implementation of ```ConnectionInterface``` (for using Doctrine's DBAL, for example).

Note that the component will not declare a cursor for your query or something like that (although this will be done soon) it was made to take a function that returns a cursor. That said let's see some code (I will skip connection part because it is really, really simple).

```php
<?php

$storedProcedureName = 'sp_get_products';
$storedProcedureParameters = array();

$cursorReader = new SequentialCursorReader($connection, $storedProcedureName, $storedProcedureParameters);

$cursorReader->open();

while ($result = $cursorReader->read()) {
    // Do something here with one result
}

$cursorReader->close();
```

```SequentialCursorReader``` will not rewind or store results it will just go on forward until it returns null (I know this is not good) to signal that there are no more rows to read.

Since it uses its own connection ```close``` and ```__destruct``` will "close" (commit the transaction and close the connection) to avoid a high number of connections in your database.

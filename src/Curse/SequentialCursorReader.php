<?php

namespace Curse;

class SequentialCursorReader
{
    private $storedProcedureName = '';
    private $storedProcedureParameters = array();
    private $connection = null;
    private $cursorName = '';
    private $opened = false;

    public function __construct(ConnectionInterface $connection, $storedProcedureName, array $storedProcedureParameters)
    {
        $this->connection = $connection;
        $this->storedProcedureName = $storedProcedureName;
        $this->storedProcedureParameters = $storedProcedureParameters;
    }

    public function open()
    {
        $openingQuery = sprintf(
            'SELECT %s(%s) AS cursor_name',
            $this->storedProcedureName,
            implode(',', $this->storedProcedureParameters)
        );

        $this->connection->beginTransaction();
        $results = $this->connection->executeQuery($openingQuery, array());
        $result = current($results);

        $this->cursorName = $result['cursor_name'];
        $this->opened = true;
    }

    public function fetch()
    {
        if ($this->isOpen()) {
            $results = $this->connection->executeQuery(sprintf('FETCH 1 FROM "%s"', $this->cursorName), array());

            return current($results);
        }

        throw new \RuntimeException('You cannot fetch from a closed cursor');
    }

    public function close()
    {
        if ($this->isOpen()) {
            $this->connection->commit();
            $this->connection = null;
        }

        $this->opened = false;
    }

    public function isOpen()
    {
        return $this->opened;
    }

    public function __destruct()
    {
        $this->close();
    }
}

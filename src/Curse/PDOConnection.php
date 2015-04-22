<?php

namespace Curse;

class PDOConnection implements ConnectionInterface
{
    private $pdo = null;
    private $executedStatements = array();

    public function __construct($host, $port, $databaseName, $user, $password)
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
            $host,
            $port,
            $databaseName,
            $user,
            $password
        );

        $this->pdo = new \PDO($dsn);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function executeQuery($statement, array $parameters)
    {
        if (false === isset($this->executedStatements[$statement])) {
            $preparedStatement = $this->pdo->prepare($statement);
            $this->cacheStatement($statement, $preparedStatement);
        } else {
            $preparedStatement = $this->executedStatements[$statement];
        }

        $preparedStatement->execute($parameters);

        return $preparedStatement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    private function cacheStatement($statement, \PDOStatement $preparedStatement)
    {
        $this->executedStatements[$statement] = $preparedStatement;
    }
}

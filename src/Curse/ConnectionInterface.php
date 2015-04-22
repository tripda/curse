<?php

namespace Curse;

interface ConnectionInterface
{
    public function beginTransaction();
    public function executeQuery($statement, array $parameters);
    public function commit();
    public function rollBack();
}

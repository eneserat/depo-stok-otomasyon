<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function run_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(is_int($key) ? $key + 1 : ':' . $key, $value);
    }
    $stmt->execute();
    return $stmt;
}

function fetch_all(string $sql, array $params = []): array
{
    return run_query($sql, $params)->fetchAll();
}

function fetch_one(string $sql, array $params = []): ?array
{
    $stmt = run_query($sql, $params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}


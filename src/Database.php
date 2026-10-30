<?php
declare(strict_types=1);

namespace Holy;

use mysqli;

/** One shared mysqli connection. Always use prepared statements through it. */
final class Database
{
    private static ?mysqli $db = null;
    private static bool $inTransaction = false;

    private static function connection(): mysqli
    {
        if (self::$db === null) {
            $c = config('db');
            // Errors throw exceptions instead of returning false.
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                $db = mysqli_init();
                // Return integer and float columns as PHP numbers.
                $db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
                $db->real_connect($c['host'], $c['user'], $c['pass'], $c['name']);
                $db->set_charset($c['charset']);
                self::$db = $db;
            } catch (\mysqli_sql_exception $e) {
                // Say what is wrong in plain words instead of a stack trace.
                http_response_code(503);
                error_log('Database connection failed: ' . $e->getMessage());
                $hint = config('app_env') === 'development'
                    ? '<br><br>Details: ' . e($e->getMessage()) . '<br>Check that MySQL is running, that the database "' . e($c['name']) . '" exists and sql/schema.sql was imported, and that the user and password in config/config.local.php are right.'
                    : '';
                exit('<p style="font-family:sans-serif;padding:2rem">The shop is temporarily unavailable. Please try again soon.' . $hint . '</p>');
            }
        }
        return self::$db;
    }

    /** Prepare a statement, bind the values as strings and run it. */
    private static function query(string $sql, array $params): \mysqli_stmt
    {
        $st = self::connection()->prepare($sql);
        if ($params) {
            $st->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $st->execute();
        return $st;
    }

    /** Run a query and return all rows. */
    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Run a query and return the first row or null. */
    public static function one(string $sql, array $params = []): ?array
    {
        return self::query($sql, $params)->get_result()->fetch_assoc();
    }

    /** Run a query and return the first column of the first row. */
    public static function value(string $sql, array $params = [])
    {
        $row = self::query($sql, $params)->get_result()->fetch_row();
        return $row[0] ?? null;
    }

    /** Run INSERT, UPDATE or DELETE. Returns affected rows. */
    public static function run(string $sql, array $params = []): int
    {
        return (int) self::query($sql, $params)->affected_rows;
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::connection()->insert_id;
    }

    /** Start a transaction. Finish it with commit() or rollback(). */
    public static function begin(): void
    {
        self::connection()->begin_transaction();
        self::$inTransaction = true;
    }

    public static function commit(): void
    {
        self::connection()->commit();
        self::$inTransaction = false;
    }

    public static function rollback(): void
    {
        self::connection()->rollback();
        self::$inTransaction = false;
    }

    public static function inTransaction(): bool
    {
        return self::$inTransaction;
    }
}

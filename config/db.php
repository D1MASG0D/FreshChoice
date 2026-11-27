<?php
/**
 * The one and only database connection.
 *
 * These are the XAMPP defaults: user "root" with an empty password.
 * Change the four values below when the project is uploaded to a real host,
 * and keep this file out of git wherever it holds a real password.
 */

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'freshchoice';
const DB_USER = 'root';
const DB_PASS = '';

/**
 * Returns one shared PDO connection, created the first time it is asked for.
 *
 * The last option turns off emulated prepares, so real prepared statements
 * reach MariaDB. That is what protects the app from SQL injection.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}

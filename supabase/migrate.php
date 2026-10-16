<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dbUrl = $_ENV['DATABASE_URL'] ?? '';
if (empty($dbUrl)) {
    fwrite(STDERR, "DATABASE_URL not set in .env\n");
    exit(1);
}

function parseDatabaseUrl(string $url): string
{
    $parts = parse_url($url);
    $host = $parts['host'] ?? 'localhost';
    $port = $parts['port'] ?? 5432;
    $dbname = ltrim($parts['path'] ?? 'postgres', '/');
    $user = $parts['user'] ?? '';
    $password = isset($parts['pass']) ? urldecode($parts['pass']) : '';
    return sprintf(
        'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
        $host,
        (int) $port,
        $dbname,
        $user,
        $password
    );
}

$pdo = new PDO(parseDatabaseUrl($dbUrl));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Run migrations
$migrationDir = __DIR__ . '/migrations';
$files = glob($migrationDir . '/*.sql');
sort($files);

foreach ($files as $file) {
    echo "Running migration: {$file}\n";
    $sql = file_get_contents($file);
    // Split by semicolon followed by newline or end of string
    $statements = preg_split('/;[\r\n]+/', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            // Ignore errors for statements that may fail if objects already exist
            // In a real migration system, we would track versions.
            // For simplicity, we just continue.
            echo "Warning: {$e->getMessage()}\n";
        }
    }
}

echo "Migrations completed.\n";

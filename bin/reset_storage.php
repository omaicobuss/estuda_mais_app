<?php

declare(strict_types=1);

$projectDir = dirname(__DIR__);
$sqliteFile = $projectDir . '/var/data_dev.db';

if (is_file($sqliteFile)) {
    unlink($sqliteFile);
    echo "Banco SQLite removido: {$sqliteFile}\n";
}

$databaseUrl = null;
foreach (file($projectDir . '/.env') ?: [] as $line) {
    if (str_starts_with(trim($line), 'DATABASE_URL=')) {
        $databaseUrl = trim(substr(trim($line), strlen('DATABASE_URL=')), "\"'");
        break;
    }
}

$console = escapeshellarg($projectDir . '/bin/console');
$php = escapeshellarg(PHP_BINARY);
$commands = [];

if (is_string($databaseUrl) && str_starts_with($databaseUrl, 'mysql://')) {
    $commands[] = sprintf('%s %s doctrine:database:create --if-not-exists --no-interaction', $php, $console);
}

$commands[] = sprintf('%s %s doctrine:migrations:migrate --no-interaction --allow-no-migration', $php, $console);

foreach ($commands as $command) {
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        fwrite(STDERR, "Falha ao aplicar migrations.\n");
        exit($exitCode);
    }
}

echo "Banco pronto para uso.\n";

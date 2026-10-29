<?php

/**
 * One-off script: salin data dari database SQLite lokal ke MySQL hosting.
 * Jalankan: php database/transfer-sqlite-to-mysql.php
 */

function envFromDotEnv(string $key): string
{
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        foreach (file(__DIR__.'/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line[0] === '#' || ! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $v = trim($v);
            if (strlen($v) >= 2 && ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'")))) {
                $v = substr($v, 1, -1);
            }
            $vars[trim($k)] = $v;
        }
    }

    return $vars[$key];
}

$src = new PDO('sqlite:database/database.sqlite');
$src->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$dst = new PDO(
    'mysql:host='.envFromDotEnv('DB_HOST').';port='.envFromDotEnv('DB_PORT').';dbname='.envFromDotEnv('DB_DATABASE').';charset=utf8mb4',
    envFromDotEnv('DB_USERNAME'),
    envFromDotEnv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

// Urutan: tabel induk dulu, pivot/anak belakangan.
$tables = [
    'users', 'roles', 'role_user', 'permissions', 'permission_role',
    'categories', 'tags', 'media', 'pages', 'menus', 'settings',
    'posts', 'post_tag', 'post_media', 'ratings',
    'contact_messages', 'activity_logs', 'registrations',
    'galleries', 'gallery_images',
];

$dst->exec('SET FOREIGN_KEY_CHECKS=0');
$dst->exec('SET UNIQUE_CHECKS=0');

$total = 0;

foreach ($tables as $table) {
    $colInfo = $src->query("PRAGMA table_info(`$table`)")->fetchAll(PDO::FETCH_ASSOC);
    $srcCols = array_column($colInfo, 'name');

    $dstInfo = $dst->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    $dstCols = array_column($dstInfo, 'Field');

    if ($srcCols !== $dstCols) {
        // Order boleh beda (mis. ->after() diabaikan di SQLite) — asalkan nama kolom sama.
        if (count(array_intersect($srcCols, $dstCols)) !== count($dstCols)) {
            $missing = array_diff($dstCols, $srcCols);
            echo "SKIP $table: kolom MySQL tidak ada di SQLite: ".implode(',', $missing)."\n";
            continue;
        }
        $srcCols = $dstCols; // pakai urutan kolom MySQL, ambil nilai via nama
    }

    $rows = $src->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    $count = count($rows);

    if ($count === 0) {
        echo "OK   $table: 0 baris\n";
        continue;
    }

    $colList = '`'.implode('`,`', $srcCols).'`';
    $placeholders = implode(', ', array_fill(0, count($srcCols), '?'));
    $dst->exec("TRUNCATE TABLE `$table`");
    $stmt = $dst->prepare("INSERT INTO `$table` ($colList) VALUES ($placeholders)");

    $dst->beginTransaction();
    $i = 0;
    foreach ($rows as $row) {
        $values = [];
        foreach ($srcCols as $col) {
            $values[] = $row[$col] ?? null;
        }
        $stmt->execute($values);
        if (++$i % 500 === 0) {
            $dst->commit();
            $dst->beginTransaction();
        }
    }
    $dst->commit();

    if (in_array('id', $srcCols, true)) {
        $nextAuto = $dst->query("SELECT COALESCE(MAX(`id`), 0) + 1 FROM `$table`")->fetchColumn();
        $dst->exec("ALTER TABLE `$table` AUTO_INCREMENT=$nextAuto");
    }

    $total += $count;
    echo "OK   $table: $count baris\n";
}

$dst->exec('SET UNIQUE_CHECKS=1');
$dst->exec('SET FOREIGN_KEY_CHECKS=1');

echo "\nSelesai. Total $total baris dipindahkan.\n";

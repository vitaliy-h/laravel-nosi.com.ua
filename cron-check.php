<?php
// Temporary: shows which PHP the hosting cron runs. Delete after the check.
// Old-PHP-safe syntax on purpose — it must run even where Laravel cannot.
file_put_contents(
    dirname(__FILE__) . '/storage/logs/cron-check.log',
    date('Y-m-d H:i:s') . ' PHP ' . PHP_VERSION . ' binary ' . PHP_BINARY . ' sapi ' . PHP_SAPI . "\n",
    FILE_APPEND
);

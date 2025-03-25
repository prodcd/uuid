<?php
/**
 * 展示UUID不连贯
 * 使用不同的key，加密出来的UUID完全不一样，差别很大
 * 请在命令行执行
 * run in cli mode
 */
use prodcd\uuid;
require '../uuid.php';
// 记录开始时间
$startTime = microtime(true);

$key = 123456; // 加密和解密使用同一个key
echo 'The key:' . $key .PHP_EOL;
echo 'Write same integer, but the key is different, get different uuid.'. PHP_EOL;
$uuid = new uuid($key);
// 循环写入整数，int8，用来展示UUID不连贯
for ($i = 0; $i < 10; $i++) {
    $uuid->clean();
    $uuid->wInt8($i);
    $uuidString = $uuid->toUuidString();
    $read = uuid::fromUuidString($key, $uuidString);
    $int8 = $read->rInt8();
    echo "Write an integer: $i, Get UUID: $uuidString, Read an integer: $int8" . PHP_EOL;
}

$key = 654321; // 更换key
echo "The key: $key" . PHP_EOL;
for ($i = 0; $i < 10; $i++) {
    $uuid = new uuid($key);
    $uuid->wInt8($i);
    $uuidString = $uuid->toUuidString();
    $read = $uuid = uuid::fromUuidString($key, $uuidString);
    $int8 = $read->rInt8();
    echo "Write an integer: $i, Get UUID: $uuidString, Read an integer: $int8" . PHP_EOL;
}

// 记录结束时间
$endTime = microtime(true);
// 计算执行时间
$executionTime = ($endTime - $startTime) * 1000 * 1000;
// 输出执行时间
echo "runtime: $executionTime us";
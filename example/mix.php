<?php

/**
 * 展示UUID不连贯
 * 使用不同的key，加密出来的UUID不一样
 * 请在命令行执行
 * run in cli mode
 */

use prodcd\uuid;

require '../uuid.php';
// 记录开始时间
$startTime = microtime(true);

$key = 123456; // 加密和解密使用同一个key
echo 'The key:' . $key .PHP_EOL;
// 写入整数和字符串示例
echo "key: $key<br /><br />";
echo "<strong>Write a integer and a string</strong><br />";
$num = 12345;
$str = "Hello, world!";
$uuid = new uuid($key);
$uuid->wInt16($num);
echo "Write a integer:".$num;
$uuid->wString("Hello, world!");
echo ",and write a string:" . $str . "<br />";
$uuidString = $uuid->toUuidString();
echo "Get a UUID: $uuidString<br />";

$read = uuid::fromUuidString($key, $uuidString);
$int16 = $read->rInt16();
$string = $read->rString();
echo "Read a integer: $int16<br />";
echo "Read a string: $string<br /><br />";

// 写入浮点数与汉字示例
echo "<strong>Write a float32 and a utf8 string</strong><br />";
$pi = 3.14159;
$utf8 = "你好！";
$uuid = new uuid($key);
$uuid->wFloat32($pi);
echo "Write a float32: $pi<br />";
$uuid->wString($utf8);
echo "and write a utf8 string: $utf8<br />";
$uuidString = $uuid->toUuidString();
echo "Get a UUID: $uuidString<br />";

$read = uuid::fromUuidString($key, $uuidString);
$float = $read->rFloat32();
$string = $read->rString();
echo "Read a float32: $float(accuracy loss)<br />";
echo "Read a string: $string<br /><br />";

// 写入MAC地址与时间戳
echo "<strong>Write a MAC address and a timestamp(microseconds)</strong><br />";
$mac = "00:11:22:33:44:55";
$uuid = new uuid($key);
$uuid->wMac($mac);
echo "Write a MAC address: $mac";
$uuid->wMicrotime();
echo ",and write a timestamp(microseconds)<br />";

$uuidString = $uuid->toUuidString();
echo "Get a UUID: $uuidString<br />";

$read = uuid::fromUuidString($key, $uuidString);
$mac = $read->rMac();
$timestamp = $read->rMicrotime();
echo "Read a MAC address: $mac<br />";
echo "Read a timestamp: $timestamp[0].$timestamp[1]<br />";
echo "Read a timestamp: ". date("Y-m-d H:i:s", $timestamp[0]).".".$timestamp[1]. "<br />";
$floatSTimestamp = $timestamp[0] + $timestamp[1] / 1000000;
echo "Read a timestamp: $floatSTimestamp(accuracy loss)<br /><br />";

// 写入IPv4地址与时间戳
echo "<strong>Write a IPv4 address and a timestamp</strong><br />";
$ip = $_SERVER['REMOTE_ADDR'];
$time = time();
$uuid = new uuid($key);
$uuid->wUInt32(ip2long($ip));
echo "Write a IPv4 address: $ip";
$uuid->wUInt32(time());
echo ",and write a timestamp: $time<br />";
$uuidString = $uuid->toUuidString();
echo "Get a UUID: $uuidString<br />";

$read = uuid::fromUuidString($key, $uuidString);
$ip = long2ip($read->rUInt32());
$timestamp = $read->rUInt32();
echo "Read a IPv4 address: $ip<br />";
echo "Read a timestamp: $timestamp<br />";
echo "Read a timestamp: ". date("Y-m-d H:i:s", $timestamp). "<br /><br />";


// 写入IPv6地址
echo "<strong>Write a IPv6 address</strong><br />";
$ip = "ec81::e826:3eef:0:3ded";
$uuid = new uuid($key);
$uuid->wIPv6($ip);
echo "Write a IPv6 address: $ip<br />";
$uuidString = $uuid->toUuidString();
echo "Get a UUID: $uuidString<br />";

$read = uuid::fromUuidString($key, $uuidString);
$ip = $read->rIPv6();
echo "Read a IPv6 address(toString=true): $ip<br />";

$read = uuid::fromUuidString($key, $uuidString);
$ip = bin2hex($read->rIPv6(false));
echo "Read a IPv6 address(toString=false): $ip<br />";


// 记录结束时间
$endTime = microtime(true);
// 计算执行时间
$executionTime = ($endTime - $startTime) * 1000 * 1000;
// 输出执行时间
echo "runtime : $executionTime us";

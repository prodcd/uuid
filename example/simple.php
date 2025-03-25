<?php
/**
 * 最基础的使用示例
 * 展示写入一个整数、浮点数、字符串，然后读取出来
 * 请在命令行执行
 * run in cli mode
 */

use prodcd\uuid;

require '../uuid.php';
// 记录开始时间
$startTime = microtime(true);
$key = 123456; // 加密和解密使用同一个key
echo 'The key:' . $key .PHP_EOL;
// 创建UUID对象
$uuid = new uuid($key);
// 写入数据
$uuid->wInt8(-123); // 写入16位有符号整型
$uuid->wInt16(6789); // 写入32位有符号整型
$uuid->wFloat32(3.14); // 写入32位单精度浮点
$uuid->wString("你好ya"); // 写入UTF8字符串（自动填充）
$uuidString = $uuid->toUuidString(); // 生成UUID字符串
echo 'Get UUID:' . $uuidString . PHP_EOL;
unset($uuid);

// 从UUID字符串中读取数据
$uuid = uuid::fromUuidString($key, $uuidString);
$num8 = $uuid->rInt8(); // 读取16位有符号整型
$num16 = $uuid->rInt16(); // 读取32位有符号整型
$pi = $uuid->rFloat32(); // 读取32位单精度浮点
$str = $uuid->rString(); // 读取UTF8字符串
// 输出结果
echo 'Read an integer:'. $num8. PHP_EOL;
echo 'Read an integer:' . $num16 . PHP_EOL;
echo 'Read a float32 number(accuracy loss):' . $pi . PHP_EOL;
echo 'Read a string:' . $str . PHP_EOL;
// 记录结束时间
$endTime = microtime(true);
// 计算执行时间
$executionTime = ($endTime - $startTime) * 1000 * 1000;
// 输出执行时间
echo "runtime: $executionTime us";

// 输出结果
/**
 * The key:123456
 * Get UUID:a5a8d980-b6f4-43d2-d8f4-741cfc04d7be
 * Read an integer:-123
 * Read an integer:6789
 * Read a float32 number(accuracy loss):3.1400001049042
 * Read a string:你好ya
 * runtime: 799.89433288574 us
 */
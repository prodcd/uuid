<?php
/**
 * 写入随机整数，并验证与读取数来的数据是否一致
 * 请在命令行执行
 * run in cli mode
 */
use prodcd\uuid;
require '../uuid.php';
// 记录开始时间
$startTime = microtime(true);

// 指定 key
$key = 123456;
echo 'The key:' . $key .PHP_EOL;
// 循环写入整数，uint32，用随机数来验证解密正确性
for ($i = 0; $i < 1000; $i++) {
    $num = rand(0, 4294967295);
    $uuid = new uuid($key);
    $uuid->wUInt32($num); // 写入随机数
    $uuidString = $uuid->toUuidString();
    $read = uuid::fromUuidString($key, $uuidString);
    $int32 = $read->rUInt32();
    if ($int32 !== $num) { // 如果读取的整数与写入的整数不一致，输出错误信息，并中断脚本执行
        echo "写入的整数: $num UUID: $uuidString 读取整数：$int32<br />";
        exit; 
    }

}
// 验证正确
echo "验证正确<br />";
// 记录结束时间
$endTime = microtime(true);
// 计算执行时间
$executionTime = ($endTime - $startTime) * 1000 * 1000;
// 输出执行时间
echo "runtime: $executionTime us";
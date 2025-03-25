# Uuid 类文档

<p align="center">
  <a href="./README.md"><img alt="简体中文版自述文件" src="https://img.shields.io/badge/简体中文-d9d9d9"></a>
  <a href="./README_EN.md"><img alt="README in English" src="https://img.shields.io/badge/English-d9d9d9"></a>
</p>

## 概述
在与外部系统进行数据交互时，需要传输数据表主键等信息，如果直接暴露int类型主键会泄漏敏感信息，如数据增量、存量等。如果改用随机生成的UUID，需要额外存储并索引，这样又会带来存储空间和性能损耗。

UUID本身可以看作一个16字节的二进制字节串，所以可以将常见简单数据类型写入到这16字节中，再转换成UUID格式来使用。例如：整数、浮点数、字符串、MAC地址、时间戳等。这样的UUID可以不存储到数据库中，只需要在传入和传出数据时加一个转换，不会影响原有代码。比如将数据表的主键id使用wUInt32($id)写入UUID，再将数据表名使用wString($tableName)写入UUID，这样就可以在不存储uuid的情况下，将数据表的id和数据表名写入到UUID中。

Uuid类，就是这样一个工具类，它提供了一系列方法用于读写不同类型的数据，同时支持数据的加密和解密，避免暴露id的增量和存量。

## 设计要点
1. **统一字节序**：采用大端字节序（网络字节序），主要是容易操作，转换成16进制后查看更方便。
2. **内置加密**：通过位移、位重排、映射表实现简单但有效的混淆加密。单次加解密约0.1ms。
3. **基础数据类型读写**：支持多种基础数据类型的读写，包括有符号和无符号整数、浮点数、字符串、时间戳和 MAC 地址等，并进行边界检查。
4. **固定长度**：UUID 固定为 16 字节，写入越界时抛出异常。
5. **偏移量自动处理**：读写按顺序进行，偏移量自动处理。

## 注意事项
1. key 是对称加密的密钥（整数类型），加密和解密的 key 必须一致，初始化后不可更改
2. toUuidString() 和 fromUuidString() 的参数（$shiftAmount、$mapCount）必须保持一致，初始化后不可更改
3. 写入与读取顺序必须严格一致
4. 字符串只能在 UUID 末尾写入且最多一次
5. UUID 最大容量为 16 字节，写入前需规划内容长度

## 方法名说明
1. `w`开头的方法为写入（write），`r`开头为读取（read）。务必按写入顺序读取数据。
2. 整数区分有符号（Int）和无符号（UInt），写入时使用对应的方法。
3. 浮点数区分单精度（Float）和双精度（Double），写入时使用对应的方法。
4. 最后的数字8、16、24、32、64为占用bit数量，对应1、2、3、4、8字节。

### 构造函数
```php
public function __construct($key, $macSeparator = ':');
```
参数：
- `$key`：必选参数，用于加密和解密。
- `$macSeparator`: 可选参数，用于配置返回 MAC 地址的分隔符，默认为 `:`。

### 写入方法
```php
public function wInt8(int $value);
public function wUInt8(int $value);
public function wInt16(int $value);
public function wUInt16(int $value);
public function wInt24(int $value);
public function wUInt24(int $value);
public function wInt32(int $value);
public function wUInt32(int $value); // 写入32位无符号整型，IP地址、时间戳应使用此方法写入。
public function wInt64(int $value); // 32bit PHP不支持
public function wFloat32(float $value); // PHP默认使用双精度浮点，写入32位单精度浮点数会造成精度损失。
public function wDouble64(float $value);
public function wString(string $value); // 写入UTF8字符串并填充完UUID。字符串只能在UUID最末尾写入最多一次。
public function wMicrotime(); // 写入当前时间戳或指定时间戳，精度是微秒，占7字节
public function wMac(string $value); // 写入MAC地址，支持分隔符分隔“:-”和空白。
public function wIpv6(string $value);
```
参数：
- `$value`：要写入的值。
### 读取方法
```php
public function rInt8(): int;
public function rUInt8(): int;
public function rInt16(): int;
public function rUInt16(): int;
public function rInt24(): int;
public function rUInt24(): int;
public function rInt32(): int;
public function rUInt32(): int;
public function rInt64(): int;
public function rFloat32(): float;
public function rFloat64(): float;
public function rString(): string;
public function rMicrotime(): array;
public function rMac(string $separator = ':'): string;
public function rIpv6(bool $toString = true): string;
```
返回值：
- 读取到的值。
### 加密和解密
$shiftAmount 与 $mapCount 的乘积应超过128，否则混淆效果会比较差。
```php
toUuidString(int $shiftAmount =71, int $mapCount = 2);
fromUuidString(string $uuidString, int $shiftAmount = 71, int $mapCount = 2); 
```
参数：
- `$shiftAmount`：位移量，应为奇数，最好是质数。
- `$mapCount`：映射次数，对性能影响较大。
返回值：
- 加密或解密后的数据。

## 使用示例
见`example`目录下代码。使用命令行方式执行。
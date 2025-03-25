# Uuid Class Documentation

<p align="center">
  <a href="./README.md"><img alt="Simplified Chinese README" src="https://img.shields.io/badge/简体中文-d9d9d9"></a>
  <a href="./README_EN.md"><img alt="README in English" src="https://img.shields.io/badge/English-d9d9d9"></a>
</p>

## Overview
When interacting with external systems, it is necessary to transmit information such as the primary key of a data table. If the primary key of the `int` type is directly exposed, sensitive information such as data increment and stock will be leaked. If a randomly generated UUID is used instead, additional storage and indexing are required, which will result in storage space and performance overhead.

A UUID can be regarded as a 16-byte binary string. Therefore, common simple data types can be written into these 16 bytes and then converted into the UUID format for use. For example, integers, floating-point numbers, strings, MAC addresses, timestamps, etc. Such a UUID does not need to be stored in the database. Only a conversion is needed when inputting and outputting data, which will not affect the original code. For example, the primary key `id` of the data table can be written into the UUID using `wUInt32($id)`, and the data table name can be written into the UUID using `wString($tableName)`. In this way, the `id` and the data table name of the data table can be written into the UUID without storing the UUID.

The `Uuid` class is such a utility class that provides a series of methods for reading and writing different types of data. It also supports data encryption and decryption to avoid exposing the increment and stock of the `id`.

## Design Highlights
1. **Unified Byte Order**: Big-endian byte order (network byte order) is used, which is mainly easy to operate and more convenient to view after conversion to hexadecimal.
2. **Built-in Encryption**: Simple but effective obfuscation encryption is implemented through bit shifting, bit rearrangement, and mapping tables. The single encryption and decryption takes about 0.1ms.
3. **Basic Data Type Reading and Writing**: Reading and writing of multiple basic data types are supported, including signed and unsigned integers, floating-point numbers, strings, timestamps, and MAC addresses. Boundary checks are also performed.
4. **Fixed Length**: The UUID is fixed at 16 bytes. An exception is thrown when the write operation exceeds the boundary.
5. **Automatic Offset Handling**: Reading and writing operations are performed in sequence, and the offset is automatically handled.

## Notes
1. The `key` is the key for symmetric encryption (integer type). The `key` for encryption and decryption must be the same and cannot be changed after initialization.
2. The parameters (`$shiftAmount`, `$mapCount`) of `toUuidString()` and `fromUuidString()` must be consistent and cannot be changed after initialization.
3. The writing and reading order must be strictly consistent.
4. Strings can only be written at the end of the UUID and at most once.
5. The maximum capacity of the UUID is 16 bytes. The content length needs to be planned before writing.

## Method Name Explanation
1. Methods starting with `w` are for writing (write), and those starting with `r` are for reading (read). Data must be read in the order of writing.
2. Integers are distinguished between signed (`Int`) and unsigned (`UInt`). Use the corresponding methods when writing.
3. Floating-point numbers are distinguished between single-precision (`Float`) and double-precision (`Double`). Use the corresponding methods when writing.
4. The final numbers 8, 16, 24, 32, 64 represent the number of bits occupied, corresponding to 1, 2, 3, 4, 8 bytes respectively.

### Constructor
```php
public function __construct($key, $macSeparator = ':');
```
Parameters:
- `$key`: Required parameter, used for encryption and decryption.
- `$macSeparator`: Optional parameter, used to configure the separator for the returned MAC address, defaults to `:`.
### Writing Methods
```php
public function wInt8(int $value);
public function wUInt8(int $value);
public function wInt16(int $value);
public function wUInt16(int $value);
public function wInt24(int $value);
public function wUInt24(int $value);
public function wInt32(int $value);
public function wUInt32(int $value); // Write a 32-bit unsigned integer. IP addresses and timestamps should be written using this method.
public function wInt64(int $value); // Not supported in 32-bit PHP.
public function wFloat32(float $value); // PHP uses double-precision floating-point numbers by default. Writing a 32-bit single-precision floating-point number will cause precision loss.
public function wDouble64(float $value);
public function wString(string $value); // Write a UTF-8 string and fill the UUID. Strings can only be written at the end of the UUID at most once.
public function wMicrotime(); // Write the current timestamp or a specified timestamp with microsecond precision, occupying 7 bytes.
public function wMac(string $value); // Write a MAC address, supporting separators “:-” and spaces.
public function wIpv6(string $value);
```
Parameters:
- `$value`: The value to be written.
### Reading Methods
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
Return Values:
- The value read.

### Encryption and Decryption
The product of `$shiftAmount` and `$mapCount` should exceed 128; otherwise, the obfuscation effect will be poor.
```php
toUuidString(int $shiftAmount = 71, int $mapCount = 2);
fromUuidString(string $uuidString, int $shiftAmount = 71, int $mapCount = 2);
```

Parameters:

- `$shiftAmount`: The shift amount, which should be an odd number and preferably a prime number.
- `$mapCount`: The number of mappings, which has a significant impact on performance. Return Values:
The encrypted or decrypted data.
## Usage Examples
See the code in the `example` directory. Execute it using the command line.

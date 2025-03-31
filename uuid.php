<?php

namespace prodcd;

use InvalidArgumentException;

/**
 * 加密二进制UUID操作类
 *
 */
class uuid
{
    public string $key; // 密码
    private string $cipher = 'aes-128-ecb'; // 加密算法，无需IV
    // 偏移量
    private int $currentOffset = 0;// 偏移量

    // 存储二进制数据
    private string $buffer;
    private const UUID_LENGTH = 16;


    // 构造函数，接收key参数
    public function __construct($key)
    {
        $this->key = $key; // 设置密码
        $this->buffer = str_repeat("\0", self::UUID_LENGTH); // 初始化缓冲区
    }

    public function clean(): void
    {
        $this->buffer = str_repeat("\0", self::UUID_LENGTH); // 初始化缓冲区
        $this->currentOffset = 0;
    }

    //------------------------ 基础读写方法 ------------------------

    /**
     * 写入8位有符号整型
     * @param int $value [-128, 127]
     */
    public function wInt8(int $value): void
    {
        $bytes = pack('c', $this->clamp($value, -128, 127));
        $this->writeBytes($bytes);
    }

    /**
     * 读取8位有符号整型
     * @return int
     */
    public function rInt8(): int
    {
        $bytes = $this->readBytes(1);
        return unpack('c', $bytes)[1];
    }

    /**
     * 写入8位无符号整型
     * @param int $value [0, 255]
     */
    public function wUInt8(int $value): void
    {
        $bytes = pack('C', $this->clamp($value, 0, 255));
        $this->writeBytes($bytes);
    }

    /**
     * 读取8位无符号整型
     * @return int
     */
    public function rUInt8(): int
    {
        $bytes = $this->readBytes(1);
        return unpack('C', $bytes)[1];
    }

    /**
     * 写入16位有符号整型
     * @param int $value [-32768, 32767]
     */
    public function wInt16(int $value): void
    {
        $bytes = pack('n', $this->clamp($value, -32768, 32767));
        $this->writeBytes($bytes);
    }

    /**
     * 读取16位有符号整型
     * @return int
     */
    public function rInt16(): int
    {
        $bytes = $this->readBytes(2);
        $result = unpack('n', $bytes)[1];
        if ($result > 32767) {
            $result -= 65536;
        }
        return $result;
    }

    /**
     * 写入16位无符号整型
     * @param int $value [0, 65535]
     */
    public function wUInt16(int $value): void
    {
        $bytes = pack('n', $this->clamp($value, 0, 65535));
        $this->writeBytes($bytes);
    }

    /**
     * 读取16位无符号整型
     * @return int
     */
    public function rUInt16(): int
    {
        $bytes = $this->readBytes(2);
        return unpack('n', $bytes)[1];
    }
    /**
     * 写入 24 位无符号整数
     * @param int $value 要写入的 24 位无符号整数
     */
    public function wUInt24(int $value): void
    {
        // 确保值在 24 位无符号整数的范围内
        $value = $this->clamp($value, 0, 0xFFFFFF);
        // 正确处理 24 位数据，将其拆分为 3 个字节
        $bytes = pack('C3', ($value >> 16) & 0xFF, ($value >> 8) & 0xFF, $value & 0xFF);
        $this->writeBytes($bytes);
    }
    /**
     * 读取 24 位无符号整数
     * @return int 读取的 24 位无符号整数
     */
    public function rUInt24(): int
    {
        $bytes = $this->readBytes(3);
        // 补齐为 32 位，前面补 0
        $bytes = "\x00" . $bytes;
        $unpacked = unpack('N', $bytes);
        if (!isset($unpacked[1])) {
            throw new InvalidArgumentException('解包 24 位无符号整数时出错');
        }
        return $unpacked[1];
    }
    /**
     * 写入 24 位有符号整数
     * @param int $value [-8388608, 8388607]
     */
    public function wInt24(int $value): void
    {
        $value = $this->clamp($value, -8388608, 8388607);
        if ($value < 0) {
            $value = 0xFFFFFF + $value + 1; // 转换为 24 位补码
        }
        // 正确处理 24 位数据
        $bytes = pack('C3', ($value >> 16) & 0xFF, ($value >> 8) & 0xFF, $value & 0xFF);
        $this->writeBytes($bytes);
    }
    /**
     * 读取 24 位有符号整数
     * @return int
     */
    public function rInt24(): int
    {
        $bytes = $this->readBytes(3);
        $bytes = "\x00" . $bytes;
        $result = unpack('N', $bytes)[1];
        if ($result & 0x00800000) {
            $result -= 0x01000000; // 转换为有符号整数
        }
        return $result;
    }
    /**
     * 写入32位有符号整型
     * @param int $value [-2147483648, 2147483647]
     */
    public function wInt32(int $value): void
    {
        $bytes = pack('N', $this->clamp($value, -2147483648, 2147483647));
        $this->writeBytes($bytes);
    }

    /**
     * 读取32位有符号整型
     * @return int
     */
    public function rInt32(): int
    {
        $bytes = $this->readBytes(4);
        $result = unpack('N', $bytes)[1];
        if ($result > 2147483647) {
            $result -= 4294967296;
        }
        return $result;
    }

    /**
     * 写入32位无符号整型
     * @param int $value [0, 4294967295]
     */
    public function wUInt32(int $value): void
    {
        $bytes = pack('N', $this->clamp($value, 0, 4294967295));
        $this->writeBytes($bytes);
    }

    /**
     * 读取32位无符号整型
     * @return int
     */
    public function rUInt32(): int
    {
        $bytes = $this->readBytes(4);
        return unpack('N', $bytes)[1];
    }
    /**
     * 写入64位无符号整型
     * @param int $value [0, 18446744073709551615]
     */
    public function wUInt64(int $value): void
    {
        $bytes = pack('J', $value);
        $this->writeBytes($bytes);
    }
    /**
     * 读取64位无符号整型
     * @return int
     */
    public function rUInt64(): int
    {
        $bytes = $this->readBytes(8);
        return unpack('J', $bytes)[1];
    }
    /**
     * 写入32位单精度浮点
     * 注意，PHP 中的浮点数默认是双精度的，使用单精度会导致精度丢失。
     * @param float $value
     */
    public function wFloat32(float $value): void
    {
        $bytes = pack('f', $value);
        $this->writeBytes($bytes);
    }

    /**
     * 读取32位单精度浮点
     * @return float
     */
    public function rFloat32(): float
    {
        $bytes = $this->readBytes(4);
        return unpack('f', $bytes)[1];
    }
    /**
     * 写入64位双精度浮点
     * @param float $value
     */
    public function wDouble64(float $value): void
    {
        $bytes = pack('d', $value);
        $this->writeBytes($bytes);
    }

    /**
     * 读取64位双精度浮点
     * @return float
     */
    public function rDouble64(): float
    {
        $bytes = $this->readBytes(8);
        return unpack('d', $bytes)[1];
    }
    /**
     * 写入 UTF8 字符串
     * @param string $str 要写入的字符串
     */
    public function wString(string $str): void
    {
        // 在字符串末尾添加 0x00 到 UUID 长度
        $str = str_pad($str, self::UUID_LENGTH - $this->currentOffset, "\0");
        $this->writeBytes($str);
        // 偏移量设置为 UUID 长度
        $this->currentOffset = self::UUID_LENGTH;
    }
    /**
     * 读取 UTF8 字符串
     * @return string 读取的字符串
     */
    public function rString(): string
    {
        if ($this->currentOffset >= self::UUID_LENGTH) {
            return '';
        }
        // 修改此处，去掉 - 1 以读取完整的字节数
        $bytes = $this->readBytes(self::UUID_LENGTH - $this->currentOffset);
        // 找到 0x00 结束符的位置
        $nullPos = strpos($bytes, "\0");
        if ($nullPos !== false) {
            return substr($bytes, 0, $nullPos);
        }
        return $bytes;
    }
    /**
     * 写入精确到微秒的 microtime，只占7字节
     */
    public function wMicrotime(): void
    {
        $microtime = microtime(true);
        // 分离整数部分（秒）和小数部分（微秒）
        $seconds = (int)$microtime;
        $microseconds = (int)(($microtime - $seconds) * 1000000);

        // 写入秒部分，使用 4 字节
        $this->wUInt32($seconds);

        // 写入微秒部分，使用 3 字节
        $this->wUInt24($microseconds);
    }

    /**
     * 读取精确到微秒的 microtime
     * @return array 精确到微秒的时间戳
     */
    public function rMicrotime(): array
    {
        // 读取秒部分
        $seconds = $this->rUInt32();
        // 读取微秒部分
        $microseconds = $this->rUInt24();

        // 组合秒和微秒部分
        return [$seconds, $microseconds];
    }
    /**
     * 写入 MAC 地址
     * @param string $macAddress MAC 地址，支持 XX:XX:XX:XX:XX:XX 或 XX-XX-XX-XX-XX-XX 格式
     */
    public function wMac(string $macAddress): void
    {
        // 移除冒号和连字符符
        $cleanedMac = preg_replace('/[:-]/', '', $macAddress);
        // 转换为二进制数据
        $bytes = hex2bin($cleanedMac);
        $this->writeBytes($bytes);
    }

    /**
     * 读取 MAC 地址
     * @param string|null $separator 自定义分隔符
     * @return string MAC 地址，格式为 XX{separator}XX{separator}XX{separator}XX{separator}XX{separator}XX
     */
    public function rMac(string $separator = ':'): string
    {
        $bytes = $this->readBytes(6);
        // 转换为十六进制字符串
        $hexMac = bin2hex($bytes);
        // 插入分隔符
        $formattedMac = implode($separator, str_split($hexMac, 2));
        return $formattedMac;
    }
    /**
     * 写入IPv6地址
     */
    public function wIpv6(string $ipv6Address): void
    {
        $this->writeBytes(inet_pton($ipv6Address));
    }
    /**
     * 读取IPv6地址
     * inet_pton()
     */
    public function rIpv6(bool $toString = true): string
    {
        $bytes = $this->readBytes(16);
        if ($toString) {
            return inet_ntop($bytes);
        }
        return $bytes;
    }
    /**
     * 数值范围限制
     */
    private function clamp($value, $min, $max)
    {
        return max($min, min($max, $value));
    }

    //------------------------ 加密核心方法 ------------------------

    /**
     * 写入加密字节
     */
    private function writeBytes(string $data): void
    {
        $length = strlen($data);
        $this->validateOffset($length);
        for ($i = 0; $i < $length; $i++) {
            $this->buffer[$this->currentOffset + $i] = $data[$i]; // 应按16进制写入buffer
        }
        $this->currentOffset += $length;
    }

    /**
     * 读取解密字节
     * @param int $length 读取长度
     * @return string
     */
    private function readBytes(int $length): string
    {
        $this->validateOffset($length);
        $bytes = substr($this->buffer, $this->currentOffset, $length);
        $this->currentOffset += $length;
        return $bytes;
    }

    //------------------------ 工具方法 ------------------------

    /**
     * 偏移量验证
     * @throws InvalidArgumentException
     */
    private function validateOffset(int $length): void
    {
        if ($this->currentOffset < 0 || $this->currentOffset + $length > self::UUID_LENGTH) {
            throw new InvalidArgumentException("越界访问 UUID [{$this->currentOffset}]");
        }
    }

    /**
     * 将当前存储的二进制数据转换为 UUID 字符串
     * @return string UUID 字符串，格式为 xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
     */
    public function toUuidString(): string
    {
        $hexData = openssl_encrypt($this->buffer, $this->cipher, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        
        $uuidString = bin2hex($hexData);
        
        return substr($uuidString, 0, 8) . '-' . substr($uuidString, 8, 4) . '-' . substr($uuidString, 12, 4) . '-' . substr($uuidString, 16, 4) . '-' . substr($uuidString, 20);
    }

    /**
     * 从 UUID 字符串中恢复数据
     */
    public static function fromUuidString(int $key, string $uuidString): self
    {
        $instance = new uuid($key);
        $instance->parseUuidString($uuidString);
        return $instance;
    }
    
    private function parseUuidString(string $uuidString): void
    {
        $uuidString = str_replace('-', '', $uuidString); // 去掉连字符
        if (strlen($uuidString) !== 32) {
            throw new InvalidArgumentException("UUID string must be exactly 32 characters long.");
        }
        $hexData = hex2bin($uuidString);
        $this->buffer = openssl_decrypt($hexData, $this->cipher, $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);

        if ($this->buffer === false) {
            throw new InvalidArgumentException("Failed to decrypt UUID string.");
        }
        $this->currentOffset = 0; // 重置偏移量
    }
}

<?php

namespace prodcd;

use InvalidArgumentException;

/**
 * 加密二进制UUID操作类
 *
 */
class uuid
{
    const CHUNK_SIZE = 4; // Process data in chunks of 4 bytes
    public int $key; // 种子
    // 偏移量
    private int $currentOffset = 0;
    // 加密映射表（256字节混淆）
    private array $encryptMap = []; // 映射
    private array $decryptMap = []; // 反映射

    // 存储二进制数据
    private string $buffer;
    private const UUID_LENGTH = 16;


    // 构造函数，接收key参数
    public function __construct($key)
    {
        $key = $key ?? 0;
        $this->init($key);
    }
    /**
     * 初始化加密映射表和缓冲区
     * @param int $key 加密种子
     */
    private function init(int $key): void
    {
        $this->generateCipherMap($key); // 生成加密映射表
        $this->buffer = str_repeat("\0", self::UUID_LENGTH); // 初始化缓冲区
    }

    /**
     * 生成加密映射表（核心加密逻辑）
     * @param int $key 加密种子
     */
    private function generateCipherMap(int $key): void
    {
        $a = 69069;
        $c = 12345;
        $m = 256;
        $seed = $key;

        $map = range(0, 255);
        //var_dump($map);

        for ($i = 0; $i < 256; $i++) {
            $seed = ($a * $seed + $c) % $m;
            //echo $seed . ',';
            $temp = $map[$i];
            $map[$i] = $map[$seed];
            $map[$seed] = $temp;
        }
        //var_dump($map);
        $this->encryptMap = $map;
        $this->decryptMap = array_flip($map);
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
    //------------------------ 加密核心方法 ------------------------

    /**
     * 加密二进制数据
     * @param string $data 要加密的数据
     * @return string 加密后的数据
     * @throws InvalidArgumentException
     */
    private function cipherEncrypt(string $data): string
    {
        $encrypted = [];
        $length = strlen($data);

        for ($i = 0; $i < $length; $i += self::CHUNK_SIZE) {
            $chunk = substr($data, $i, self::CHUNK_SIZE);
            $paddedChunk = str_pad($chunk, self::CHUNK_SIZE, "\0");
            $word = unpack('N', $paddedChunk)[1];

            // 加密
            for ($j = 0; $j < 8; $j++) {
                $word = $this->byteSubstitution($word, $this->encryptMap);
                $word = $this->leftRotate($word, 4);
            }

            $encrypted[] = $word;
        }

        return pack('N*', ...$encrypted);
    }

    /**
     * 解密二进制数据
     * @param string $data 要解密的数据
     * @return string 解密后的数据
     * @throws InvalidArgumentException
     */
    private function cipherDecrypt(string $data): string
    {
        $decrypted = [];
        $length = strlen($data);

        for ($i = 0; $i < $length; $i += self::CHUNK_SIZE) {
            $chunk = substr($data, $i, self::CHUNK_SIZE);
            $word = unpack('N', $chunk)[1];

            // 解密
            for ($j = 0; $j < 8; $j++) {
                $word = $this->rightRotate($word, 4);
                $word = $this->byteSubstitution($word, $this->decryptMap);
            }

            $decrypted[] = $word;
        }

        return pack('N*', ...$decrypted);
    }

    /**
     * 字节替换
     * @param int $word 单词
     * @param array $map 替换映射
     * @return int 替换后的单词
     */
    private function byteSubstitution(int $word, array $map): int
    {
        $bytes = [
            ($word >> 24) & 0xFF,
            ($word >> 16) & 0xFF,
            ($word >> 8) & 0xFF,
            $word & 0xFF
        ];
        $bytes = array_map(function ($b) use ($map) {
            return $map[$b];
        }, $bytes);
        return ($bytes[0] << 24) | ($bytes[1] << 16) | ($bytes[2] << 8) | $bytes[3];
    }

    /**
     * 左旋转
     * @param int $word 单词
     * @param int $amount 旋转位数
     * @return int 旋转后的单词
     */
    private function leftRotate(int $word, int $amount): int
    {
        return (($word << $amount) | ($word >> (32 - $amount))) & 0xFFFFFFFF;
    }

    /**
     * 右旋转
     * @param int $word 单词
     * @param int $amount 旋转位数
     * @return int 旋转后的单词
     */
    private function rightRotate(int $word, int $amount): int
    {
        return (($word >> $amount) | ($word << (32 - $amount))) & 0xFFFFFFFF;
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
     * 位重排
     * @param string $data 要逆向重排的数据
     * @return string 逆向重排后的数据
     */
    private function bitShuffle(string $data): string
    {
        if (strlen($data) !== self::UUID_LENGTH) {
            throw new InvalidArgumentException("Input data length must be exactly " . self::UUID_LENGTH . " bytes.");
        }

        $bitArray = $this->extractBits($data);

        $shuffledData = '';
        for ($i = 0; $i < self::UUID_LENGTH; $i++) {
            $byte = 0;
            for ($j = 0; $j < 8; $j++) {
                $bitIndex = ($j * self::UUID_LENGTH) + $i;
                $byte |= $bitArray[$bitIndex] << (7 - $j);
            }
            $shuffledData .= chr($byte);
        }
        //echo 'bitShuffle:'.bin2hex($shuffledData).PHP_EOL;
        return $shuffledData;
    }

    /**
     * 逆向位重排
     * @param string $data 要逆向重排的数据
     * @return string 逆向重排后的数据
     * @throws InvalidArgumentException
     */
    private function bitUnshuffle(string $data): string
    {
        if (strlen($data) !== self::UUID_LENGTH) {
            throw new InvalidArgumentException("Input data length must be exactly " . self::UUID_LENGTH . " bytes.");
        }

        $bitArray = $this->extractBits($data);

        $unshuffledData = '';
        for ($i = 0; $i < self::UUID_LENGTH; $i++) {
            $byte = 0;
            for ($j = 0; $j < 8; $j++) {
                $bitIndex = ($i * 8) + $j;
                $originalBitIndex = ($bitIndex % self::UUID_LENGTH) * 8 + intdiv($bitIndex, self::UUID_LENGTH);
                $byte |= $bitArray[$originalBitIndex] << (7 - $j);
            }
            $unshuffledData .= chr($byte);
        }
        return $unshuffledData;
    }

    /**
     * 提取字节中的所有位
     * @param string $data 输入数据
     * @return array 提取的位数组
     */
    private function extractBits(string $data): array
    {
        $bitArray = array_fill(0, self::UUID_LENGTH * 8, 0); // 初始化固定大小的数组
        for ($i = 0; $i < self::UUID_LENGTH; $i++) {
            $byte = ord($data[$i]);
            for ($j = 0; $j < 8; $j++) {
                $bitIndex = $i * 8 + $j; // 计算固定索引
                $bitArray[$bitIndex] = ($byte >> (7 - $j)) & 1;
            }
        }
        return $bitArray;
    }
    /**
     * 将当前存储的二进制数据转换为 UUID 字符串
     * @return string UUID 字符串，格式为 xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
     */
    public function toUuidString(int $shiftAmount = 71, int $mapCount = 2): string
    {
        $data = $this->buffer;
        for ($i = 0; $i < $mapCount; $i++) {
            var_dump(bin2hex($data));
            $data = $this->bitShuffle($data); // 位重排
            var_dump(bin2hex($data));
            $data = $this->shift128Bits($data, $shiftAmount); // 位移
            var_dump(bin2hex($data));
            $data = $this->cipherEncrypt($data); // 加密
            var_dump(bin2hex($data));
        }

        $hexData = '';
        for ($i = 0; $i < self::UUID_LENGTH; $i++) {
            $hexData .= str_pad(dechex(ord($data[$i])), 2, '0', STR_PAD_LEFT);
        }
        return substr($hexData, 0, 8) . '-' . substr($hexData, 8, 4) . '-' . substr($hexData, 12, 4) . '-' . substr($hexData, 16, 4) . '-' . substr($hexData, 20);
    }

    /**
     * 从 UUID 字符串中恢复数据
     */
    public static function fromUuidString(int $key, string $uuidString, int $shiftAmount = 71, int $mapCount = 2): self
    {
        $instance = new uuid($key);
        $instance->parseUuidString($uuidString, $shiftAmount, $mapCount);
        return $instance;
    }
    
    private function parseUuidString(string $uuidString, int $shiftAmount = 71, int $mapCount = 2): void
    {
        $cleanedUuid = str_replace('-', '', $uuidString);

        if (strlen($cleanedUuid) !== 32) {
            throw new InvalidArgumentException("Invalid UUID string length. Expected 32 hexadecimal characters.");
        }

        $data = hex2bin($cleanedUuid);
        for ($i = $mapCount - 1; $i >= 0; $i--) {
            $data = $this->cipherDecrypt($data);
            $data = $this->shift128Bits($data, -$shiftAmount);
            $data = $this->bitUnshuffle($data);
        }
        $this->currentOffset = 0;
        $this->buffer = $data;
    }

    /**
     * 循环位移
     */
    private function shift128Bits(string $data, int $shiftAmount): string
    {
        if (strlen($data) !== self::UUID_LENGTH) {
            throw new InvalidArgumentException("Input data length must be exactly " . self::UUID_LENGTH . " bytes.");
        }

        $shiftAmount = $shiftAmount % 128;
        if ($shiftAmount < 0) {
            $shiftAmount += 128;
        }

        // Convert data to an array of integers
        $bytes = [];
        for ($i = 0; $i < self::UUID_LENGTH; $i++) {
            $bytes[] = ord($data[$i]);
        }

        // Perform the circular shift
        $shiftBytes = intdiv($shiftAmount, 8);
        $shiftBits = $shiftAmount % 8;

        $shiftedBytes = [];
        for ($i = 0; $i < self::UUID_LENGTH; $i++) {
            $currentByte = $bytes[($i + $shiftBytes) % self::UUID_LENGTH];
            $nextByte = $bytes[($i + $shiftBytes + 1) % self::UUID_LENGTH];

            $shiftedBytes[] = (($currentByte << $shiftBits) | ($nextByte >> (8 - $shiftBits))) & 0xFF;
        }

        // Convert back to string
        return implode('', array_map('chr', $shiftedBytes));
    }
}

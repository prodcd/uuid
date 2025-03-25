# Uuid ���ĵ�

<p align="center">
  <a href="./README.md"><img alt="�������İ������ļ�" src="https://img.shields.io/badge/��������-d9d9d9"></a>
  <a href="./README_EN.md"><img alt="README in English" src="https://img.shields.io/badge/English-d9d9d9"></a>
</p>

## ����
�����ⲿϵͳ�������ݽ���ʱ����Ҫ�������ݱ���������Ϣ�����ֱ�ӱ�¶int����������й©������Ϣ�������������������ȡ��������������ɵ�UUID����Ҫ����洢�������������ֻ�����洢�ռ��������ġ�

UUID������Կ���һ��16�ֽڵĶ������ֽڴ������Կ��Խ���������������д�뵽��16�ֽ��У���ת����UUID��ʽ��ʹ�á����磺���������������ַ�����MAC��ַ��ʱ����ȡ�������UUID���Բ��洢�����ݿ��У�ֻ��Ҫ�ڴ���ʹ�������ʱ��һ��ת��������Ӱ��ԭ�д��롣���罫���ݱ������idʹ��wUInt32($id)д��UUID���ٽ����ݱ���ʹ��wString($tableName)д��UUID�������Ϳ����ڲ��洢uuid������£������ݱ��id�����ݱ���д�뵽UUID�С�

Uuid�࣬��������һ�������࣬���ṩ��һϵ�з������ڶ�д��ͬ���͵����ݣ�ͬʱ֧�����ݵļ��ܺͽ��ܣ����Ⱪ¶id�������ʹ�����

## ���Ҫ��
1. **ͳһ�ֽ���**�����ô���ֽ��������ֽ��򣩣���Ҫ�����ײ�����ת����16���ƺ�鿴�����㡣
2. **���ü���**��ͨ��λ�ơ�λ���š�ӳ���ʵ�ּ򵥵���Ч�Ļ������ܡ����μӽ���Լ0.1ms��
3. **�����������Ͷ�д**��֧�ֶ��ֻ����������͵Ķ�д�������з��ź��޷������������������ַ�����ʱ����� MAC ��ַ�ȣ������б߽��顣
4. **�̶�����**��UUID �̶�Ϊ 16 �ֽڣ�д��Խ��ʱ�׳��쳣��
5. **ƫ�����Զ�����**����д��˳����У�ƫ�����Զ�����

## ע������
1. key �ǶԳƼ��ܵ���Կ���������ͣ������ܺͽ��ܵ� key ����һ�£���ʼ���󲻿ɸ���
2. toUuidString() �� fromUuidString() �Ĳ�����$shiftAmount��$mapCount�����뱣��һ�£���ʼ���󲻿ɸ���
3. д�����ȡ˳������ϸ�һ��
4. �ַ���ֻ���� UUID ĩβд�������һ��
5. UUID �������Ϊ 16 �ֽڣ�д��ǰ��滮���ݳ���

## ������˵��
1. `w`��ͷ�ķ���Ϊд�루write����`r`��ͷΪ��ȡ��read������ذ�д��˳���ȡ���ݡ�
2. ���������з��ţ�Int�����޷��ţ�UInt����д��ʱʹ�ö�Ӧ�ķ�����
3. ���������ֵ����ȣ�Float����˫���ȣ�Double����д��ʱʹ�ö�Ӧ�ķ�����
4. ��������8��16��24��32��64Ϊռ��bit��������Ӧ1��2��3��4��8�ֽڡ�

### ���캯��
```php
public function __construct($key, $macSeparator = ':');
```
������
- `$key`����ѡ���������ڼ��ܺͽ��ܡ�
- `$macSeparator`: ��ѡ�������������÷��� MAC ��ַ�ķָ�����Ĭ��Ϊ `:`��

### д�뷽��
```php
public function wInt8(int $value);
public function wUInt8(int $value);
public function wInt16(int $value);
public function wUInt16(int $value);
public function wInt24(int $value);
public function wUInt24(int $value);
public function wInt32(int $value);
public function wUInt32(int $value); // д��32λ�޷������ͣ�IP��ַ��ʱ���Ӧʹ�ô˷���д�롣
public function wInt64(int $value); // 32bit PHP��֧��
public function wFloat32(float $value); // PHPĬ��ʹ��˫���ȸ��㣬д��32λ�����ȸ���������ɾ�����ʧ��
public function wDouble64(float $value);
public function wString(string $value); // д��UTF8�ַ����������UUID���ַ���ֻ����UUID��ĩβд�����һ�Ρ�
public function wMicrotime(); // д�뵱ǰʱ�����ָ��ʱ�����������΢�룬ռ7�ֽ�
public function wMac(string $value); // д��MAC��ַ��֧�ַָ����ָ���:-���Ϳհס�
public function wIpv6(string $value);
```
������
- `$value`��Ҫд���ֵ��
### ��ȡ����
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
����ֵ��
- ��ȡ����ֵ��
### ���ܺͽ���
$shiftAmount �� $mapCount �ĳ˻�Ӧ����128���������Ч����Ƚϲ
```php
toUuidString(int $shiftAmount =71, int $mapCount = 2);
fromUuidString(string $uuidString, int $shiftAmount = 71, int $mapCount = 2); 
```
������
- `$shiftAmount`��λ������ӦΪ�����������������
- `$mapCount`��ӳ�������������Ӱ��ϴ�
����ֵ��
- ���ܻ���ܺ�����ݡ�

## ʹ��ʾ��
��`example`Ŀ¼�´��롣ʹ�������з�ʽִ�С�

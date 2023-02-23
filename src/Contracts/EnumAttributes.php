<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Contracts;

interface EnumAttributes
{
    /**
     * 翻译值的含义
     *
     * @param mixed $value
     * @param mixed $default 默认值
     * @return mixed
     */
    public function translate(mixed $value, mixed $default = null): mixed;

    /**
     * 判断值是否合法
     *
     * @param mixed $value
     * @return bool
     */
    public function has(mixed $value): bool;

    /**
     * 获取值集合
     *
     * @return array
     */
    public function values(): array;

    /**
     * 是否为集合
     *
     * @return bool
     */
    public static function isCollection(): bool;
}

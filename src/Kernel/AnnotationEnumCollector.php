<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Kernel;

/**
 * 注解枚举收集器
 */
final class AnnotationEnumCollector
{
    /**
     * 收集到的枚举
     *
     * @var array
     */
    protected static array $enumCached = [];

    /**
     * 验证是否存在收到到的枚举
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$enumCached[$key]);
    }

    /**
     * 获取某个类下注解的枚举
     * PS: 优先从缓存中获取
     *
     * @param string $key
     * @param bool $once 为true时，立即收集一次
     * @return array
     */
    public static function get(string $key, bool $once = false): array
    {
        if (!$once && isset(self::$enumCached[$key])) {
            return self::$enumCached[$key];
        }

        return self::collect($key);
    }

    /**
     * 获取收集到的所有枚举
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$enumCached;
    }

    /**
     * 收集目标类上的注解枚举
     *
     * @param string $targetClass
     * @return array
     */
    public static function collect(string $targetClass): array
    {
        try {
            self::$enumCached[$targetClass] = [];

            $reflection = new \ReflectionClass($targetClass);

            $ret = [];
            foreach ($reflection->getReflectionConstants() as $constant) {
                if (empty($attributes = $constant->getAttributes(Message::class))) {
                    continue;
                } elseif (count($arguments = $attributes[0]?->getArguments() ?? []) != 2) {
                    continue;
                }

                ['0' => $column, '1' => $message] = $arguments;
                if (is_null($message)) {
                    $message = $column;
                    $column = static::class;
                }
                $value = $constant->getValue();

                $ret[] = [
                    'column'  => $column,
                    'message' => $message,
                    'value'   => $value,
                ];
            }

            return self::$enumCached[$targetClass] = (array) $ret;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * 加载注解缓存
     *
     * @return void
     */
    public static function loadCacheFile(): void
    {
        try {
            if (is_file($cachePath = app()->bootstrapPath(config('enum.cache_path')))) {
                return;
            }

            if (is_array($cacheData = require $cachePath)) {
                self::$enumCached = $cacheData;
            }
        } catch (\Throwable) {
        }
    }
}

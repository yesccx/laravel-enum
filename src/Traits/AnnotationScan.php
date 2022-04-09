<?php

namespace Yesccx\LaravelEnum\Traits;

use Yesccx\LaravelEnum\Support\Message;
use ReflectionClass;
use Throwable;

/**
 * 注解扫描
 */
trait AnnotationScan
{
    /**
     * 注解扫描状态
     *
     * @var bool
     */
    protected static bool $annotationScanned = false;

    /**
     * 注解扫描到的字段集
     *
     * @var array
     */
    protected static array $annotationColumns = [];

    /**
     * 实现BaseEnum中的loadColumnMap方法
     *
     * @return array
     */
    protected function loadColumnMap(): array
    {
        $this->scanColumns();

        return self::$annotationColumns;
    }

    /**
     * 扫描注释上的字段集
     *
     * @return void
     */
    protected function scanColumns(): void
    {
        if (static::$annotationScanned) {
            return;
        }

        try {
            $reflection = new ReflectionClass(static::class);

            // 扫描常量上的注解
            foreach ($reflection->getReflectionConstants() as $constant) {
                $arguments = $constant->getAttributes(Message::class)[0]?->getArguments() ?? [];
                if (count($arguments) != 2) {
                    continue;
                }

                ['0' => $column, '1' => $message] = $arguments;
                $value = $constant->getValue();

                static::$annotationColumns[$column][$value] ??= $message;
            }

            static::$annotationScanned = true;
        } catch (Throwable) {
        }
    }
}

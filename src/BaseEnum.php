<?php

declare(strict_types = 1);

namespace Yesccx\Enum;

use Illuminate\Support\Arr;
use Yesccx\Enum\Contracts\EnumAttributes;
use Yesccx\Enum\Contracts\EnumCollection;
use Yesccx\Enum\Traits\AnnotationScan;

/**
 * 枚举基类
 */
abstract class BaseEnum implements EnumAttributes
{
    use AnnotationScan;

    /**
     * 字段含义映射
     *
     * @var array
     */
    protected array $columnMap = [];

    /**
     * 当前字段名
     *
     * @var string
     */
    protected string $column = '';

    /**
     * @param string $column 字段名
     * @return void
     */
    public function __construct(string $column = '')
    {
        $this->initAttributes($column);
    }

    /**
     * 静态实例化
     *
     * @param string $column 字段名
     * @return static
     */
    public static function make(string $column = ''): static
    {
        return new static($column);
    }

    /**
     * 初始化
     *
     * @param string $column
     * @return void
     */
    protected function initAttributes(string $column = ''): void
    {
        $isCollection = static::isCollection();

        $this->column = $isCollection ? $column : static::class;

        if (method_exists($this, 'loadColumnMap')) {
            $this->columnMap = match (true) {
                !$isCollection => [static::class => $this->loadColumnMap()],
                default        => $this->loadColumnMap()
            };
        }
    }

    /**
     * 翻译值的含义
     *
     * @param mixed $value
     * @param mixed $default 默认值
     * @return mixed
     */
    public function translate(mixed $value, mixed $default = null): mixed
    {
        return $this->columnMap[$this->column][$value] ?? $default;
    }

    /**
     * 判断值是否合法
     *
     * @param mixed $value
     * @return bool
     */
    public function has(mixed $value): bool
    {
        return isset($this->columnMap[$this->column][$value]);
    }

    /**
     * 获取键值映射
     *
     * @return array
     */
    public function map(): array
    {
        return $this->columnMap[$this->column] ?? [];
    }

    /**
     * 获取以值为键的映射
     *
     * @return array
     */
    public function valueMap(): array
    {
        return Arr::map($this->map(), fn ($value, $key) => [
            'key'   => $key,
            'value' => $value,
        ]);
    }

    /**
     * 获取所有健
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->map());
    }

    /**
     * 获取所有值
     *
     * @return array
     */
    public function values(): array
    {
        return array_values($this->map());
    }

    /**
     * 指定字段名
     *
     * @param string $column 字段名
     * @return static
     */
    public function useColumn(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    /**
     * 指定字段名(useColumn别名)
     *
     * @param string $column 字段名
     * @return static
     */
    public function by(string $column): static
    {
        return $this->useColumn($column);
    }

    /**
     * 是否为集合
     *
     * @return bool
     */
    public static function isCollection(): bool
    {
        return is_subclass_of(static::class, EnumCollection::class);
    }
}

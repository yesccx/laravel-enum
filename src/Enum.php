<?php

namespace Yesccx\LaravelEnum;

use Illuminate\Support\Collection;

/**
 * 枚举基类
 */
abstract class Enum
{
    /**
     * 字段含义映射
     *
     * @var array
     */
    protected $columnMap = [];

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
        $this->column = $column;

        if (method_exists($this, 'loadColumnMap')) {
            $this->columnMap = $this->loadColumnMap();
        }
    }

    /**
     * 实例化
     *
     * @param string $column 字段名
     * @return static
     */
    public static function make(string $column = ''): static
    {
        return new static($column);
    }

    /**
     * 翻译字段值的含义
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
     * 判断是否包含某个值
     *
     * @param mixed $value
     * @return bool
     */
    public function has(mixed $value): bool
    {
        return isset($this->columnMap[$this->column][$value]);
    }

    /**
     * 获取值集合
     *
     * @return Collection
     */
    public function values(): Collection
    {
        return match (true) {
            empty($this->column) => collect($this->columnMap),
            default              => collect($this->columnMap[$this->column] ?? [])
        };
    }

    /**
     * 获取所有值集合
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return collect($this->columnMap);
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
     * @return array
     */
    protected function loadColumnMap(): array
    {
        return [];
    }
}

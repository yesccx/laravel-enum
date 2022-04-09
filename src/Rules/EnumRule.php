<?php

namespace Yesccx\LaravelEnum\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 枚举值验证
 */
final class EnumRule implements Rule
{
    /**
     * 枚举类
     *
     * @var string
     */
    protected string $enumClass = '';

    /**
     * 字段名
     *
     * @var string
     */
    protected string $attribute = '';

    /**
     * @param string $enumClass 枚举类
     * @param string $attribute 字段名
     * @return void
     */
    public function __construct(string $enumClass, string $attribute = '')
    {
        $this->enumClass = $enumClass;
        $this->attribute = $attribute;
    }

    /**
     * make
     *
     * @param string $enumClass 枚举类
     * @param string $attribute 字段名
     * @return static
     */
    public static function make(string $enumClass, string $attribute = ''): static
    {
        return new static($enumClass, $attribute);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $attribute = $this->attribute ?: $attribute;

        return $this->enumClass::make($attribute)->has($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return sprintf(':attribute 不是有效的值');
    }
}

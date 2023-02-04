<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Rules;

use Illuminate\Contracts\Validation\Rule;
use Throwable;
use Yesccx\Enum\Enum;

/**
 * 枚举值验证
 */
class EnumRule implements Rule
{
    /**
     * @param string $enumClass 枚举类
     * @param string $attribute 字段名
     * @return void
     */
    public function __construct(
        public string $enumClass,
        public string $attribute = ''
    ) {
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

        try {
            return $this->enumClass::make($attribute)->has($value);
        } catch (Throwable) {
            return false;
        }
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

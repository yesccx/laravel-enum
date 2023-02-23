<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Traits;

use Yesccx\Enum\Rules\EnumRule;

trait ToRule
{
    /**
     * Make to rule
     *
     * @param string $column
     * @param string $suffixMessage 验证错误时的后缀信息
     * @return EnumRule
     */
    public static function toRule(string $column = '', string $suffixMessage = ''): EnumRule
    {
        return new EnumRule(static::class, $column, $suffixMessage);
    }
}

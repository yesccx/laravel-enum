<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * 辅助Model Casts
 */
trait ToModelAttribute
{
    /**
     * To Attribute
     *
     * @param object $model
     * @param string $column
     * @param string $default
     * @return Attribute
     */
    public function toAttribute(object $model, string $column, string $default = ''): Attribute
    {
        return Attribute::make(
            get: fn () => $this->translate($model->{$column} ?? null, $default)
        );
    }

    /**
     * Make to Attribute
     *
     * @param object $model
     * @param string $default
     * @param string $matchSuffix
     * @return Attribute
     */
    public static function makeAttribute(object $model, string $default = '', string $matchSuffix = 'Definition'): Attribute
    {
        preg_match(
            "/^(.*){$matchSuffix}$/",
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? '',
            $matches
        );

        $column = mb_strtolower($matches[1] ?? null);

        return match (true) {
            empty($column) => Attribute::make(get: fn () => $default),
            default        => static::make($column)->toAttribute($model, $column, $default)
        };
    }
}

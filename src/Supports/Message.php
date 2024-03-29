<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Supports;

/**
 * 枚举注解类
 */
#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
class Message
{
    /**
     * @param string $column 枚举字段名
     * @param string|null $message 枚举值说明
     * @param mixed $value 枚举值
     */
    public function __construct(
        public string $column,
        public ?string $message = null,
        public mixed $value = null
    ) {
    }
}

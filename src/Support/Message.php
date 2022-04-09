<?php

namespace Yesccx\LaravelEnum\Support;

/**
 * Message 注解类
 */
class Message
{
    public function __construct(
        public string $column,
        public string $message
    ) {
    }
}

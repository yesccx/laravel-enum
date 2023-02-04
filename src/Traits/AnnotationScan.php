<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Traits;

use Yesccx\Enum\Kernel\AnnotationEnumCollector;

/**
 * 注解扫描
 */
trait AnnotationScan
{
    /**
     * @return array
     */
    protected function loadColumnMap(): array
    {
        $columnMap = [];

        try {
            $data = AnnotationEnumCollector::get(static::class);
            foreach ($data as $item) {
                $columnMap[static::class][$item['column']][$item['value']] ??= $item['message'];
            }
        } catch (\Throwable) {
            $columnMap = [];
        }

        return $columnMap;
    }
}

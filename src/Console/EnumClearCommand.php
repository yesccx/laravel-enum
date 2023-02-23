<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * 清理注解枚举缓存
 */
final class EnumClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'enum:clear';

    /**
     * The name of the console command.
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     */
    protected static $defaultName = 'enum:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理注解枚举缓存';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new config cache command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function handle()
    {
        $this->files->delete(
            $this->laravel->bootstrapPath(config('enum.cache_filename'))
        );

        $this->info('清理注解枚举缓存成功！');
    }
}

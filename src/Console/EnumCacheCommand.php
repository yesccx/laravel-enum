<?php

declare(strict_types = 1);

namespace Yesccx\Enum\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use Yesccx\Enum\Kernel\AnnotationEnumCollector;

/**
 * 构建注解枚举缓存
 */
final class EnumCacheCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'enum:cache';

    /**
     * The name of the console command.
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     */
    protected static $defaultName = 'enum:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '扫描并构建注解枚举缓存';

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
     * @throws \Exception
     */
    public function handle()
    {
        $this->call('enum:clear');

        $enumerations = $this->getEnumerations();

        $enumerationFilename = $this->laravel->bootstrapPath(config('enum.cache_filename'));

        $this->files->put(
            $enumerationFilename,
            '<?php return ' . var_export($enumerations, true) . ';' . PHP_EOL
        );

        try {
            require $enumerationFilename;
        } catch (Throwable $e) {
            $this->files->delete($enumerationFilename);

            throw new Exception('注解解析异常.', 0, $e);
        }

        $this->info('构建注解枚举缓存成功!');
    }

    /**
     * 反射解析注解内容
     *
     * @return array
     */
    protected function getEnumerations(): array
    {
        $enumFiles = $this->files->allFiles(config('enum.enum_root_path'));

        foreach ($enumFiles as $file) {
            /** @var SplFileInfo $file */
            preg_match('/(?:namespace\s*)(.*?);/', $file->getContents(), $res);
            $class = trim($res[1] . '\\' . $file->getBasename(), '.php');

            AnnotationEnumCollector::collect($class);
        }

        return AnnotationEnumCollector::all();
    }
}

<?php declare(strict_types = 1);

namespace Digua;

class Loader
{
    /**
     * Paths where we are looking for a file.
     *
     * @var string[]
     */
    protected array $includePaths;

    /**
     * @param string ...$includePath File search path
     * @uses spl_autoload_register
     */
    public function __construct(string ...$includePath)
    {
        $this->includePaths = $includePath;
        spl_autoload_register((fn(string $className) => self::load($className, ...$this->includePaths))(...));
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected static function prepareFilePath(string $filePath): string
    {
        return preg_replace(['/\\\\/', '/^App/'], [DIRECTORY_SEPARATOR, 'app'], ltrim($filePath, '\\')) . '.php';
    }

    /**
     * @param string $className
     * @param string ...$includePath File search path
     * @return bool
     */
    public static function load(string $className, string ...$includePath): bool
    {
        $filePath    = self::prepareFilePath($className);
        $defPath     = '..' . DIRECTORY_SEPARATOR . (stripos($className, 'app') === false ? 'vendor' : '');
        $includePath = array_filter(array_map(fn($path) => realpath($path), array_merge($includePath, [$defPath])));

        foreach ($includePath as $dirPath) {
            $fullPath = $dirPath . DIRECTORY_SEPARATOR . $filePath;
            if (is_readable($fullPath)) {
                require_once($fullPath);
                return true;
            }
        }

        return false;
    }
}
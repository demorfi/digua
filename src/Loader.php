<?php declare(strict_types=1);

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
        spl_autoload_register((fn(string $className) => $this->load($className, ...$this->includePaths))(...));
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected function prepareFilePath(string $filePath): string
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, ltrim($filePath, '\\')) . '.php';
    }

    /**
     * @param string $className
     * @param string ...$includePath File search path
     * @return bool
     */
    public function load(string $className, string ...$includePath): bool
    {
        $filePath    = self::prepareFilePath($className);
        $bases       = explode('/', $filePath);
        $includePath = array_map(function ($path) use ($bases, $filePath) {

            /**
             * Search and replace mask in include path
             * Example:
             * App\Components\Main = ../vendor/$1/src/ => ../vendor/app/src/Components/Main.php
             * App\Components\Main = ../vendor/$1/src/$2/dir/ => ../vendor/app/src/components/dir/Main.php
             * App\Components\Main = ../vendor/$2/src/$1/dir/$3/ => ../vendor/components/src/app/dir/Main.php
             */
            if (preg_match_all('/(?<mask>\$(?<number>\d+))/', $path, $matches)) {
                foreach ($matches['number'] as $index => $number) {
                    if (isset($bases[$number - 1])) {
                        // The last mask in the path is simply cleared
                        $path = (int)$number === sizeof($bases)
                            ? str_replace($matches['mask'][$index] . '/', '', $path)
                            : str_replace($matches['mask'][$index], strtolower($bases[$number - 1]), $path);
                    }
                }

                // The last file name remains unchanged
                $filePath = implode('/', array_slice($bases, min((int)max($matches['number']), sizeof($bases) - 1)));
            }

            return $path . $filePath;
        }, array_merge($includePath, ['../$1/', '../vendor/$1/src/', '../src/$2/']));

        foreach ($includePath as $filePath) {
            if (($filePath = realpath($filePath)) !== false) {
                require_once($filePath);
                return true;
            }
        }

        return false;
    }
}
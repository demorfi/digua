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
     * @var string[]
     */
    protected array $defIncludePaths = ['../$1', '../vendor/$1/src', '../src/$2'];

    /**
     * @param string ...$includePath File search path
     */
    public function __construct(string ...$includePath)
    {
        $this->includePaths = $includePath;
    }

    /**
     * @return void
     * @uses spl_autoload_register
     */
    public function register(): void
    {
        spl_autoload_register((fn(string $className) => $this->load($className))(...));
    }

    /**
     * @param string $filePath
     * @return array
     */
    protected function generateFileMap(string $filePath): array
    {
        $filePath = str_replace('\\', '/', ltrim($filePath, '\\')) . '.php';
        $names    = explode('/', $filePath);
        return array_map(function ($rootPath) use ($names, $filePath) {

            /**
             * Search and replace mask in include path
             * Example:
             * App\Components\Main = ../vendor/$1/src => ../vendor/app/src/Components/Main.php
             * App\Components\Main = ../vendor/$1/src/$2/dir => ../vendor/app/src/components/dir/Main.php
             * App\Components\Main = ../vendor/$2/src/$1/dir/$3 => ../vendor/components/src/app/dir/Main.php
             */
            if (preg_match_all('/(?<mask>\$(?<number>\d+))/', $rootPath, $matches)) {
                foreach ($matches['number'] as $index => $number) {
                    if (isset($names[$number - 1])) {
                        // The last mask in the path is simply cleared
                        $rootPath = (int)$number === sizeof($names)
                            ? str_replace($matches['mask'][$index], '', $rootPath)
                            : str_replace($matches['mask'][$index], strtolower($names[$number - 1]), $rootPath);
                    }
                }

                // The last file name remains unchanged
                $filePath = implode('/', array_slice($names, min((int)max($matches['number']), sizeof($names) - 1)));
            }

            return rtrim($rootPath, '/') . '/' . $filePath;
        }, array_merge($this->includePaths, $this->defIncludePaths));
    }

    /**
     * @param string $filePath
     * @return bool
     * @uses require_once
     */
    protected function requireFile(string $filePath): bool
    {
        if (($filePath = realpath($filePath)) !== false) {
            require_once($filePath);
            return true;
        }

        return false;
    }

    /**
     * @param string $className
     * @return bool
     */
    public function load(string $className): bool
    {
        $fileMap = $this->generateFileMap($className);
        foreach ($fileMap as $filePath) {
            if ($this->requireFile($filePath)) {
                return true;
            }
        }

        return false;
    }
}
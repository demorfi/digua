<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Exceptions\File as FileException;

class DotEnv
{
    /**
     * @param string $filePath
     * @throws FileException
     */
    public function __construct(protected string $filePath)
    {
        if (!is_file($this->filePath)) {
            throw new FileException(sprintf('File (%s) does not exist!', $this->filePath));
        }
    }

    /**
     * @return void
     * @throws FileException
     */
    public function load(): void
    {
        if (!is_readable($this->filePath)) {
            throw new FileException(sprintf('File (%s) is unreadable!', $this->filePath));
        }

        if (!empty($envData = parse_ini_file($this->filePath))) {
            foreach ($envData as $envKey => $envValue) {
                if (!isset($_ENV[$envKey]) && !isset($_SERVER[$envKey])) {
                    putenv(sprintf('%s=%s', $envKey, $envValue));
                }
            }
        }
    }
}
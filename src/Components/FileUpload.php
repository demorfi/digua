<?php declare(strict_types=1);

namespace Digua\Components;

use SplFileInfo;
use stdClass;

class FileUpload extends stdClass
{
    /**
     * @param array $fileInfo
     */
    public function __construct(array $fileInfo)
    {
        foreach ($fileInfo as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->{$name} ?? null;
    }

    /**
     * @return ?string
     */
    public function getBasename(): ?string
    {
        return basename((string)$this->name) ?: null;
    }

    /**
     * @return ?string
     */
    public function getExtension(): ?string
    {
        return strtolower(pathinfo((string)$this->name, PATHINFO_EXTENSION)) ?: null;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK && is_uploaded_file((string)$this->tmp_name);
    }

    /**
     * @param string $filePath
     * @return SplFileInfo|false
     */
    public function moveTo(string $filePath): SplFileInfo|false
    {
        return is_string($this->tmp_name) && move_uploaded_file($this->tmp_name, $filePath) ? new SplFileInfo($filePath) : false;
    }
}
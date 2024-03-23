<?php declare(strict_types=1);

namespace Digua\Components;

/**
 * Redefined function for testing isValid method.
 *
 * @param string $name
 * @return bool
 */
function is_uploaded_file(string $name): bool
{
    return $name === 'uploaded';
}

/**
 * Redefined function for testing moveTo method.
 *
 * @param string $from
 * @param string $to
 * @return bool
 */
function move_uploaded_file(string $from, string $to): bool
{
    return $to === 'uploaded';
}


namespace Tests\Components;

use Digua\Components\FileUpload;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

class FileUploadTest extends TestCase
{
    /**
     * @return array[]
     */
    protected function dataSetProvider(): array
    {
        return [
            [['name' => 'test', 'path' => '/tmp/path']],
            [['info' => 'test', 'value' => '/tmp/path']],
            [['obj' => 'key', 'tmp_name' => '/tmp/path', 'field' => null]]
        ];
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @return void
     */
    public function testIsItPossibleCreateInstance(array $dataSet): void
    {
        $file = new FileUpload($dataSet);
        foreach ($dataSet as $key => $value) {
            $this->assertSame($file->{$key}, $value);
        }
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetUndefinedKeyAsNull(): void
    {
        $file = new FileUpload([]);
        $this->assertNull($file->otherKey);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetBasename(): void
    {
        $file = new FileUpload(['name' => '/fake/path/name.zip']);
        $this->assertSame('/fake/path/name.zip', $file->name);
        $this->assertSame('name.zip', $file->getBasename());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetNullBasename(): void
    {
        $file = new FileUpload(['name' => '']);
        $this->assertSame('', $file->name);
        $this->assertSame(null, $file->getBasename());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetExtension(): void
    {
        $file = new FileUpload(['name' => '/fake/path/name.zip']);
        $this->assertSame('zip', $file->getExtension());

        $file = new FileUpload(['name' => '/fake/path/name.png']);
        $this->assertSame('png', $file->getExtension());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetNullExtension(): void
    {
        $file = new FileUpload(['name' => '']);
        $this->assertSame(null, $file->getExtension());

        $file = new FileUpload(['name' => '/fake/path/name']);
        $this->assertSame(null, $file->getExtension());
    }

    /**
     * @return void
     */
    public function testIsItValidUpload(): void
    {
        // function is_uploaded_file redefined!
        $file = new FileUpload(['error' => 0, 'tmp_name' => 'uploaded']);
        $this->assertTrue($file->isValid());
    }

    /**
     * @return void
     */
    public function testIsItInvalidUpload(): void
    {
        // function is_uploaded_file redefined!
        $file = new FileUpload(['error' => 0]);
        $this->assertFalse($file->isValid());

        $file = new FileUpload(['error' => 3, 'tmp_name' => 'uploaded']);
        $this->assertFalse($file->isValid());

        $file = new FileUpload(['tmp_name' => 'uploaded']);
        $this->assertFalse($file->isValid());
    }

    /**
     * @return void
     */
    public function testIsItSuccessMoveToUpload(): void
    {
        // function move_uploaded_file redefined!
        $file = new FileUpload(['tmp_name' => 'uploaded']);
        $this->assertInstanceOf(SplFileInfo::class, $file->moveTo('uploaded'));
    }

    /**
     * @return void
     */
    public function testIsItFailureMoveToUpload(): void
    {
        // function move_uploaded_file redefined!
        $file = new FileUpload(['tmp_name' => null]);
        $this->assertFalse($file->moveTo('uploaded'));

        $file = new FileUpload(['tmp_name' => 'uploaded']);
        $this->assertFalse($file->moveTo('failure'));
    }
}
<?php declare(strict_types=1);

namespace Tests;

use Digua\Loader;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    /**
     * @var Loader
     */
    protected Loader $loader;

    /**
     * @var string
     */
    protected string $filePath;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->loader = $this
            ->getMockBuilder(Loader::class)
            ->setConstructorArgs([
                '../',
                '../vendor/$1/src',
                '../vendor/$1/src/$2/dir',
                '../vendor/$2/src/$1/dir/$3'
            ])
            ->onlyMethods(['requireFile'])
            ->getMock();

        $this->loader->method('requireFile')
            ->will(
                $this->returnCallback(
                    function (string $filePath) {
                        $this->filePath = '';

                        $result = in_array($filePath, [
                            '../App/Components/File1.php',
                            '../vendor/app/src/Components/File2.php',
                            '../vendor/app/src/components/dir/File3.php',
                            '../vendor/components/src/app/dir/File4.php'
                        ]);

                        if ($result) {
                            $this->filePath = $filePath;
                        }

                        return $result;
                    }
                )
            );
    }

    /**
     * @return void
     */
    public function testExistingFile(): void
    {
        $this->loader->load('App\Components\File1');
        $this->assertSame('../App/Components/File1.php', $this->filePath);

        $this->loader->load('App\Components\File2');
        $this->assertSame('../vendor/app/src/Components/File2.php', $this->filePath);

        $this->loader->load('App\Components\File3');
        $this->assertSame('../vendor/app/src/components/dir/File3.php', $this->filePath);

        $this->loader->load('App\Components\File4');
        $this->assertSame('../vendor/components/src/app/dir/File4.php', $this->filePath);
    }

    /**
     * @return void
     */
    public function testMissingFile(): void
    {
        $this->assertFalse($this->loader->load('App1\Components\File1'));
    }
}
<?php declare(strict_types=1);

namespace Tests;

use Digua\{Template, Request, Templates\TemplateAsPHPEngine};
use Digua\Interfaces\{
    Template as TemplateInterface,
    Template\Engine as TemplateEngineInterface
};
use Digua\Exceptions\Path as PathException;
use PHPUnit\Framework\TestCase;
use Stringable;

class TemplateTest extends TestCase
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        Template::setDiskPath(__DIR__);
        $this->request = new Request;
    }

    /**
     * @return void
     * @throws PathException
     */
    public function testInstanceOfInterfaces(): void
    {
        $template = new Template($this->request);
        $this->assertInstanceOf(TemplateInterface::class, $template);
        $this->assertInstanceOf(Stringable::class, $template);
    }

    /**
     * @runInSeparateProcess
     * @return void
     * @throws PathException
     */
    public function testThrowIsTemplateDirectoryIsNotReadable(): void
    {
        $this->expectException(PathException::class);
        $this->expectExceptionMessage('The path (' . __DIR__ . '/templatePath) for ' . Template::class . ' is not readable!');
        $this->expectExceptionCode(200);

        Template::setDiskPath(__DIR__ . '/templatePath');
        new Template($this->request);
    }

    /**
     * @return void
     * @throws PathException
     */
    public function testDefaultEngine(): void
    {
        $template = new Template($this->request);
        $this->assertInstanceOf(TemplateAsPHPEngine::class, $template->getEngine());
    }

    /**
     * @return void
     * @throws PathException
     */
    public function testSetAndGetEngine(): void
    {
        $engine   = $this->getMockBuilder(TemplateEngineInterface::class)->getMock();
        $template = new Template($this->request, $engine);

        $this->assertInstanceOf(TemplateEngineInterface::class, $template->getEngine());
    }

    /**
     * @return void
     * @throws PathException
     */
    public function testAddAndGetVariables(): void
    {
        $template = new Template($this->request);
        $template->addVariables(['var1' => 'val1', 'var2' => 'val2']);
        $template->addVariables(['var3' => 'val1', 'var2' => 'val4']);

        $this->assertSame($template->getVariables(), ['var1' => 'val1', 'var2' => 'val4', 'var3' => 'val1']);
    }

    /**
     * @return void
     * @throws PathException
     */
    public function testRender(): void
    {
        $template = new Template($this->request);
        $result   = $template->render('templateName', ['var1' => 'val1']);

        $this->assertSame($result, $template);
        $this->assertSame($template->getName(), 'templateName');
        $this->assertSame($template->getVariables(), ['var1' => 'val1']);
    }

    /**
     * @return void
     * @throws PathException
     */
    public function testBuildTemplate(): void
    {
        $engine = $this->getMockBuilder(TemplateEngineInterface::class)
            ->onlyMethods(['build'])
            ->getMock();

        $engine->expects($this->once())->method('build')->will($this->returnValue('build result'));

        $template = new Template($this->request, $engine);
        $this->assertSame((string)$template->render('template'), 'build result');
    }
}
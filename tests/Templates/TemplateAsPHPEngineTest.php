<?php declare(strict_types=1);

namespace Tests\Templates;

use Digua\{Request, Routes\RouteAsName, Templates\TemplateAsPHPEngine};
use Digua\Interfaces\{Template\Engine as TemplateEngineInterface};
use Digua\Exceptions\{
    Path as PathException,
    Template as TemplateException
};
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class TemplateAsPHPEngineTest extends TestCase
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
        $this->request = new Request;
    }

    /**
     * @param TemplateAsPHPEngine $engine
     * @param string              $name
     * @param mixed               ...$arguments
     * @return mixed
     * @throws ReflectionException
     */
    protected function callToEngine(TemplateAsPHPEngine $engine, string $name, mixed ...$arguments): mixed
    {
        return (new ReflectionClass(TemplateAsPHPEngine::class))
            ->getMethod($name)
            ->invoke($engine, ...$arguments);
    }

    /**
     * @return void
     */
    public function testInstanceOfEngineInterface(): void
    {
        $this->assertInstanceOf(TemplateEngineInterface::class, new TemplateAsPHPEngine(__DIR__, $this->request));
    }

    /**
     * @return void
     * @throws PathException
     * @throws TemplateException
     */
    public function testBuildTemplate(): void
    {
        $engine = $this->getMockBuilder(TemplateAsPHPEngine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['startBuffering', 'view', 'flushBuffering', 'overwrite'])
            ->getMock();

        $engine->expects($this->once())->method('startBuffering');

        $templates = ['template', 'extend-template'];
        $engine->expects($this->exactly(2))->method('view')
            ->with(
                $this->callback(static function (string $name) use (&$templates) {
                    return $name === array_shift($templates);
                })
            );

        $engine->expects($this->once())->method('flushBuffering')->will($this->returnValue('build template'));
        $engine->expects($this->once())->method('overwrite')->with(['var1' => 'val1']);

        $engine->extend('extend-template');
        $result = $engine->build('template', ['var1' => 'val1']);
        $this->assertSame($result, 'build template');
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testHasRouteInTemplate(): void
    {
        $route = $this->getMockBuilder(RouteAsName::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasRoute'])
            ->getMock();

        $route->expects($this->once())->method('hasRoute')
            ->will($this->returnValue(true));
        $this->request->setRoute($route);

        $engine = new TemplateAsPHPEngine(__DIR__, $this->request);
        $this->assertTrue($this->callToEngine($engine, 'hasRoute', '/'));
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testGetUriInTemplate(): void
    {
        $engine = new TemplateAsPHPEngine(__DIR__, $this->request);
        $this->assertSame($this->callToEngine($engine, 'getUri'), '/');
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testIsItPossibleBlockAndSection(): void
    {
        $engine = $this->getMockBuilder(TemplateAsPHPEngine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['startBuffering', 'flushBuffering'])
            ->getMock();

        $engine->expects($this->once())->method('startBuffering')
            ->will($this->returnValue(true));

        $engine->expects($this->once())->method('flushBuffering')
            ->will($this->returnValue('content'));

        // -- Empty Section
        $this->assertNull($this->callToEngine($engine, 'block', 'sectionName'));
        $this->assertFalse($this->callToEngine($engine, 'hasBlock', 'sectionName'));
        // -- END Empty Section

        // -- Add Section
        $this->callToEngine($engine, 'section', 'sectionName');
        $this->callToEngine($engine, 'endSection', 'sectionName');
        // -- END Add Section

        // -- Check Added Section
        $this->assertTrue($this->callToEngine($engine, 'hasBlock', 'sectionName'));
        $this->assertSame($this->callToEngine($engine, 'block', 'sectionName'), 'content');
        // -- END Check Added Section
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testThrowViewTemplateInNotFound(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage(__DIR__ . '/template.tpl.php - template not found!');

        $engine = new TemplateAsPHPEngine(__DIR__, $this->request);
        $this->callToEngine($engine, 'view', 'template');
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testViewTemplate(): void
    {
        $engine = $this->getMockBuilder(TemplateAsPHPEngine::class)
            ->setConstructorArgs([__DIR__, $this->request])
            ->onlyMethods(['set'])
            ->getMock();

        $engine->expects($this->once())->method('set')
            ->with('self', ['var1' => 'val1']);

        $testFile = __DIR__ . '/' . $engine::class . '.tpl.php';
        file_put_contents($testFile, '<?php echo \'test string\'; ?>');

        $engine->startBuffering();
        $this->callToEngine($engine, 'view', $engine::class, ['var1' => 'val1']);
        $result = $engine->flushBuffering();

        $this->assertSame($result, 'test string');
        unlink($testFile);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testInjectTemplate(): void
    {
        $extends = (new ReflectionClass(TemplateAsPHPEngine::class))
            ->getProperty('extends');

        $engine = $this->getMockBuilder(TemplateAsPHPEngine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['section', 'view', 'endSection', 'block'])
            ->getMock();

        $templates = ['template', 'extend-template'];
        $engine->expects($this->exactly(2))->method('view')
            ->with(
                $this->callback(static function (string $name) use (&$templates) {
                    return $name === array_shift($templates);
                })
            );

        $engine->expects($this->once())->method('section')->with('template');
        $engine->expects($this->once())->method('endSection')->with('template');
        $engine->expects($this->once())->method('block')
            ->with('template')
            ->will($this->returnValue('block content'));

        $engine->extend('extend-template');
        $this->assertSame($extends->getValue($engine), ['extend-template']);

        $result = $this->callToEngine($engine, 'inject', 'template');
        $this->assertSame($result, 'block content');
        $this->assertEmpty($extends->getValue($engine));
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testShortSection(): void
    {
        $engine = $this->getMockBuilder(TemplateAsPHPEngine::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['section', 'view', 'endSection'])
            ->getMock();

        $engine->expects($this->once())->method('section')->with('sectionName');
        $engine->expects($this->once())->method('view')->with('template');
        $engine->expects($this->once())->method('endSection')->with('sectionName');

        $this->callToEngine($engine, 'shortSection', 'sectionName', 'template');
    }
}
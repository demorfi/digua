<?php declare(strict_types=1);

namespace Tests\Controllers;

use Digua\{Request, Template};
use Digua\Enums\Headers;
use Digua\Controllers\Base;
use Digua\Interfaces\{
    Controller as ControllerInterface,
    Template as TemplateInterface,
    Request as RequestInterface,
    Request\Data as RequestDataInterface,
};
use Digua\Exceptions\{Path, NotFound, Abort};
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    /**
     * @var Base
     */
    private Base $controller;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        $this->controller = $this->getMockForAbstractClass(Base::class, [new Request]);
        Template::setDiskPath(__DIR__);
    }

    /**
     * @return void
     */
    public function testInstanceOfInterfaces(): void
    {
        $this->assertInstanceOf(ControllerInterface::class, $this->controller);
        $this->assertInstanceOf(TemplateInterface::class, $this->controller);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetName(): void
    {
        $this->assertSame($this->controller::class, $this->controller->getName());
    }

    /**
     * @return void
     * @throws Path
     */
    public function testTemplateObjectIsReturned(): void
    {
        $testFile = Template::getDiskPath($this->controller::class . '.tpl.php');
        file_put_contents($testFile, '<?php echo $this->var; ?>');

        $result = $this->controller->render($this->controller::class, ['var' => 'test string']);
        $this->assertInstanceOf(TemplateInterface::class, $result);

        $data = (string)$result;
        $result->flushBuffer();
        $this->assertSame('test string', $data);
        unlink($testFile);
    }

    /**
     * @return void
     */
    public function testRequestObjectIsReturned(): void
    {
        $this->assertInstanceOf(RequestInterface::class, $this->controller->request());
    }

    /**
     * @return void
     */
    public function testDataRequestObjectIsReturned(): void
    {
        $this->assertInstanceOf(RequestDataInterface::class, $this->controller->dataRequest());
    }

    /**
     * @return void
     * @throws NotFound
     */
    public function testThrowNotFound(): void
    {
        $this->expectException(NotFound::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Page Not Found');
        $this->controller->throwNotFound('Page Not Found');
    }

    /**
     * @return void
     * @throws Abort
     */
    public function testThrowAbortIntCode(): void
    {
        $this->expectException(Abort::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Unprocessable Entity');
        $this->controller->throwAbort(422, 'Unprocessable Entity');
    }

    /**
     * @return void
     * @throws Abort
     */
    public function testThrowAbortEnumHeader(): void
    {
        $this->expectException(Abort::class);
        $this->expectExceptionCode(202);
        $this->expectExceptionMessage('Accepted');
        $this->controller->throwAbort(Headers::ACCEPTED, 'Accepted');
    }
}
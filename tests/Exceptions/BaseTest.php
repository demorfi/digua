<?php declare(strict_types=1);

namespace Tests\Exceptions;

use Digua\Interfaces\Exception as ExceptionInterface;
use Digua\Exceptions\Base as BaseException;
use Digua\LateEvent;
use PHPUnit\Framework\TestCase;
use Exception;

class BaseTest extends TestCase
{
    /**
     * @return void
     */
    public function testInstanceOfInterface(): void
    {
        $this->assertInstanceOf(ExceptionInterface::class, new BaseException);
        $this->assertInstanceOf(Exception::class, new BaseException);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToNotifyLateEvent(): void
    {
        $result = null;
        LateEvent::subscribe('LateEventBaseTest', function ($exception) use (&$result) {
            $result = $exception;
        });

        $exception = $this->getMockBuilder(BaseException::class)
            ->setMockClassName('LateEventBaseTest')
            ->getMock();

        $this->assertSame($exception, $result);
    }
}
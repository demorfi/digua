<?php

use Digua\LateEvent;
use PHPUnit\Framework\TestCase;

class LateEventTest extends TestCase
{
    public function testIsItPossibleToNotifyOneEvent()
    {
        LateEvent::subscribe(
            'handlerId1',
            'oneEventName',
            (function ($oneArgument) {
                $this->assertEquals('oneArgument', $oneArgument);
            })(...)
        );
        LateEvent::notify('oneEventName', 'oneArgument');
    }

    public function testIsItPossibleToNotifyTwoEvent()
    {
        LateEvent::subscribe(
            'handlerId2',
            ['twoEventName', 'oneEventName'],
            (function ($oneArgument, $twoArgument) {
                $this->assertEquals('oneArgument', $oneArgument);
                $this->assertTrue($twoArgument);
            })(...)
        );
        LateEvent::notify(['oneEventName', 'twoEventName'], 'oneArgument', true);
    }
}
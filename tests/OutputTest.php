<?php

namespace Excavator\Tests;

use Excavator\Output;

class OutputTest extends \PHPUnit\Framework\TestCase
{
    public function testWriteLineEchosMessage()
    {
        $stdout = new Output();

        ob_start();
        $stdout->writeLine("testing 1 2 3");
        $outputMessage = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("testing 1 2 3\n", $outputMessage);
    }

    public function testWriteMessageStartEchoMessageWithoutNewLine()
    {
        $stdout = new Output();

        ob_start();
        $stdout->writeMessageStart("testing 1 2 3");
        $outputMessage = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("testing 1 2 3", $outputMessage);
    }

    public function testWriteMessageEndEchoMessageWithNewLine()
    {
        $stdout = new Output();

        ob_start();
        $stdout->writeMessageEnd("done.");
        $outputMessage = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("done.\n", $outputMessage);
    }
}

<?php

namespace Excavator;

class Output
{
    public function writeLine(string $msg)
    {
        echo $msg . "\n";
    }

    public function writeMessageStart(string $msg)
    {
        echo $msg;
    }

    public function writeMessageEnd(string $msg)
    {
        echo $msg . "\n";
    }
}

<?php

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Tests\Functional\Fixtures\cache\syntaxError\project\src\My\Stuff;

class MyStuff
{
    public function getGreeting(): string
    {
        // Unlike YourGubbins, this module has a syntax error.
        return I am a syntax error;
    }
}

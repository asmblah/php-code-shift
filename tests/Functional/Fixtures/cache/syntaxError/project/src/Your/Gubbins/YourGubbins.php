<?php

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Tests\Functional\Fixtures\cache\syntaxError\project\src\Your\Gubbins;

class YourGubbins
{
    public function getGreeting(): string
    {
        // Unlike MyStuff, this module does not have a syntax error.
        return 'I am not a syntax error';
    }
}

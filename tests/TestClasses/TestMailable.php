<?php

declare(strict_types=1);

namespace Akira\Debugger\Tests\TestClasses;

use Illuminate\Mail\Mailable;

class TestMailable extends Mailable
{
    public function build()
    {
        return $this->markdown('mails.test');
    }
}

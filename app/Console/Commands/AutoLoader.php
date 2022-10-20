<?php


namespace App\Console\Commands;
use Worker;

use Illuminate\Contracts\Console\Kernel;

class AutoLoader extends Worker
{
    public function run()
    {
        require __DIR__. '/../../../vendor/autoload.php';
        $app = require __DIR__.'/../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
    }

    public function start($options = 1118481)
    {
        return parent::start(1);
    }

}

<?php
namespace App\Console\Commands;

use Exception;
use Threaded;

class OperatorThread extends Threaded
{
    /**
     * @var OperatorThreaded
     */
    private $operator;
    private $error;

    public function __construct(OperatorThreaded $operator)
    {
        $this->operator = $operator;
    }

    public function run()
    {
        try {
            $this->operator->handle();
        } catch (Exception $exception) {
            $this->error = (string) $exception;
        }
    }

    public function getError() {
        return $this->error;
    }

}

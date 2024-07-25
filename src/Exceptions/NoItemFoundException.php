<?php

namespace App\Exceptions;

use App\Struct\ProbabilityEntryCollection;

class NoItemFoundException extends \Exception
{
    private ProbabilityEntryCollection $collection;
    protected $message = "No item found";

    public function __construct(ProbabilityEntryCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
    }
}
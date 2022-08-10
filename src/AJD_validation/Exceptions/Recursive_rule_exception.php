<?php namespace AJD_validation\Exceptions;

use Countable;
use RecursiveIterator;

class Recursive_rule_exception implements RecursiveIterator, Countable
{
    private $exceptions;

    public function __construct(Nested_rule_exception $parent)
    {
        $this->exceptions = $parent->getRelated();
    }

    public function count() : int
    {
        return $this->exceptions->count();
    }

    public function hasChildren() : bool
    {
        if (!$this->valid()) {
            return false;
        }

        return ($this->current() instanceof Nested_rule_exception);
    }

    public function getChildren() : \RecursiveIterator
    {
        return new static($this->current());
    }

    public function current() : mixed
    {
        return $this->exceptions->current();
    }

    public function key() : mixed
    {
        return $this->exceptions->key();
    }

    public function next() : void
    {
        $this->exceptions->next();
    }

    public function rewind() : void
    {
        $this->exceptions->rewind();
    }

    public function valid() : bool
    {
        return $this->exceptions->valid();
    }
}

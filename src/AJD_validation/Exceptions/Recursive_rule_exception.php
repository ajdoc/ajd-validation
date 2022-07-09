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

    public function count()
    {
        return $this->exceptions->count();
    }

    public function hasChildren()
    {
        if (!$this->valid()) {
            return false;
        }

        return ($this->current() instanceof Nested_rule_exception);
    }

    public function getChildren()
    {
        return new static($this->current());
    }

    public function current()
    {
        return $this->exceptions->current();
    }

    public function key()
    {
        return $this->exceptions->key();
    }

    public function next()
    {
        $this->exceptions->next();
    }

    public function rewind()
    {
        $this->exceptions->rewind();
    }

    public function valid()
    {
        return $this->exceptions->valid();
    }
}

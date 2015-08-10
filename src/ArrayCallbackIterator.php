<?php

namespace Monad;

/**
 * Class ArrayCallbackIterator extends ArrayIterator to provide functionality that enables transforming elements of an
 * other iterator. This is implemented in such a way that if a transformation returns an array, the result will be
 * concatenated to the iterator's contents.
 *
 * @package Monad
 */
class ArrayCallbackIterator extends \ArrayIterator
{
    /**
     * @var \Iterator
     */
    private $source;

    /**
     * @var \Closure
     */
    private $transformation;

    public function __construct(\Iterator $source, callable $transform = null)
    {
        $this->source         = $source;
        $this->transformation = $transform;
    }

    private function transform($current)
    {
        $transform = $this->transformation;
        if ($current instanceof Monad) {
            $return = $current->bind($transform);
        } else {
            $return = $transform($current);
        }

        return $return;
    }

    /**
     * @param $transformed
     */
    public function append($transformed)
    {
        if (is_array($transformed)) {
            foreach ($transformed as $value) {
                parent::append($value);
            }
        } else {
            parent::append($transformed);
        }
    }

    public function next()
    {
        parent::next();
        if (!parent::valid()) {
            $this->source->next();
        }
    }

    public function valid()
    {
        if (!parent::valid()) {
            if ($this->source->valid()) {
                $this->append(
                    $this->transform($this->source->current())
                );
            }
        }

        return parent::valid();
    }
}
<?php

namespace Monad\Promise;

class TaskQueue
{
    private $q;
    private $tasksRunning = false;

    public function __construct()
    {
        $this->q = new \SplQueue();
        $this->q->setIteratorMode(\SplQueue::IT_MODE_DELETE);
    }

    public function enqueue(callable $task)
    {
        $this->q->enqueue($task);
    }

    public function runTasks()
    {
        if (!$this->tasksRunning) {
            $this->tasksRunning = true;
            foreach ($this->q as $task) {
                /** @var callable $task */
                $task();
            }
            $this->tasksRunning = false;
        }
    }
}
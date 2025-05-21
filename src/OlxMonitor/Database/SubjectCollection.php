<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Database;

abstract class SubjectCollection extends \ArrayObject
{
    public function __construct()
    {
        $data = $this->loadData();
        parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS);
    }

    public function __destruct()
    {
        $this->saveData();
    }

    abstract protected function loadData(): array;

    abstract protected function saveData(): void;
}

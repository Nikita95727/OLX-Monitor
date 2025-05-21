<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Subjects;

class Subject extends AbstractSubject
{
    private DTO $DTO;

    public function getDTO(): DTO
    {
        return $this->DTO;
    }

    public function setDTO(DTO $DTO): void
    {
        $this->DTO = $DTO;
    }

    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}

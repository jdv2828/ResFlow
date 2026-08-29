<?php

namespace Src\Modules\Tickets\Domain\Contracts;

use Src\Modules\Tickets\Domain\Entities\TicketEntity;

interface TicketRepository
{
    public function findByHash(string $hash): ?TicketEntity;

    public function save(TicketEntity $ticket): void;

    public function findById(string $id): ?TicketEntity;


    public function update(TicketEntity $ticket): void;

    public function findLockForUpdate(int $id): ?TicketEntity;
}

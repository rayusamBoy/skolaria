<?php

declare(strict_types=1);

namespace PermissionKit\Support;

final class SyncReport
{
    /** @var array<int, string> */
    public array $created = [];

    /** @var array<int, string> */
    public array $updated = [];

    /** @var array<int, string> */
    public array $unchanged = [];

    /** @var array<int, string> */
    public array $orphaned = [];

    /** @var array<int, string> */
    public array $pruned = [];

    public function totalProcessed(): int
    {
        return count($this->created) + count($this->updated) + count($this->unchanged);
    }
}

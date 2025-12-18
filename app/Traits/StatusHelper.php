<?php

namespace App\Traits;

use App\Models\StatusWo;

trait StatusHelper
{
    /**
     * Get the ID of a status by its name.
     *
     * @param string $statusName
     * @return int|null
     */
    protected function getStatusId(string $statusName): ?int
    {
        return StatusWo::where('nama_status', $statusName)->value('id_status_wo');
    }
}

?>

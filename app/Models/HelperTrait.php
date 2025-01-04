<?php

namespace App\Models;

use Carbon\Carbon;

trait HelperTrait
{
    /**
     * Get string form of created_at column
     * format `d/m/Y \a\t H:i a`
     *
     * @return string
     */
    public function getCreatedDetailAttribute(): string
    {
        return Carbon::parse($this->created_at)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y \a\t H:i a');

    }

    /**
     * Get string form of updated_at column
     * format `d/m/Y \a\t H:i a`
     *
     * @return string
     */
    public function getUpdatedDetailAttribute(): string
    {
        return Carbon::parse($this->updated_at)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y \a\t H:i a');

    }

    /**
     * Get customized date
     *
     * @param string $date
     * @return Carbon
     */
    public function date(string $date): Carbon
    {
        return Carbon::parse($date)->setTimezone('Africa/Addis_Ababa');
    }
}

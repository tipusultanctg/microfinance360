<?php

namespace App\Models\Traits;

use App\Models\GeneralLedger;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasJournalEntries
{
    /**
     * Get all of the journal entries for this model.
     * A "journal entry" is the header record in the general_ledgers table.
     */
    public function journalEntries(): MorphMany
    {
        return $this->morphMany(GeneralLedger::class, 'transactionable');
    }
}

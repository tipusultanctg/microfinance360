<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManualEntryController extends Controller
{
    public function create()
    {
        $accounts = ChartOfAccount::orderBy('name')->get();
        return view('accounting.manual-entries.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'entries' => 'required|array|min:2',
            'entries.*.account_id' => 'required|exists:chart_of_accounts,id',
            'entries.*.type' => 'required|in:debit,credit',
            'entries.*.amount' => 'required|numeric|min:0.01',
        ]);

        $debits = 0;
        $credits = 0;

        foreach ($validated['entries'] as $entry) {
            if ($entry['type'] === 'debit') {
                $debits += $entry['amount'];
            } else {
                $credits += $entry['amount'];
            }
        }

        // --- VALIDATION: Ensure debits equal credits ---
        if (round($debits, 2) !== round($credits, 2)) {
            throw ValidationException::withMessages([
                'entries' => 'The total debits must be equal to the total credits.',
            ]);
        }

        DB::transaction(function () use ($validated) {
            // Create the parent Journal Entry
            $journal = GeneralLedger::create([
                'date' => $validated['date'],
                'description' => $validated['description'],
            ]);

            // Create the child Ledger Entries (line items)
            foreach ($validated['entries'] as $entry) {
                $journal->ledgerEntries()->create([
                    'chart_of_account_id' => $entry['account_id'],
                    'type' => $entry['type'],
                    'amount' => $entry['amount'],
                ]);
            }
        });

        return redirect()->route('accounting.general-ledger')
            ->with('success', 'Manual journal entry created successfully.');
    }
}

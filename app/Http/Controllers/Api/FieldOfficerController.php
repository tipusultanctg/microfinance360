<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanRepaymentSchedule;
use App\Models\SavingsAccount; // We might add savings collections later
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FieldOfficerController extends Controller
{
    /**
     * Get the daily collection sheet for the authenticated field officer.
     */
    public function collectionSheet(Request $request)
    {
        $today = Carbon::today();
        $user = $request->user();

        // Fetch all individual overdue installments
        $dueInstallments = LoanRepaymentSchedule::with([
            'loanAccount:id,account_number,member_id',
            'loanAccount.member:id,name,phone'
        ])
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', $today)
            ->whereHas('loanAccount.member', function ($query) use ($user) {
                if ($user->branch_id) {
                    $query->where('branch_id', $user->branch_id);
                }
            })
            ->orderBy('due_date', 'asc')
            ->get();

        // --- NEW LOGIC: Group by member and consolidate ---
        $consolidatedTasks = new Collection();

        // Group all installments by the member's ID
        $groupedByMember = $dueInstallments->groupBy('loanAccount.member.id');

        foreach ($groupedByMember as $memberId => $installments) {
            // Get the first installment to extract common data
            $firstInstallment = $installments->first();

            // Sum the total amount due for this member
            $totalAmountDue = $installments->sum('total_amount');

            // Find the oldest due date for this member
            $oldestDueDate = $installments->min('due_date');

            $consolidatedTasks->push([
                'type' => 'loan',
                'due_date' => $oldestDueDate->format('Y-m-d'),
                'is_overdue' => true, // If they are in this list, they are overdue
                'amount_due' => $totalAmountDue,
                'member' => [
                    'id' => $firstInstallment->loanAccount->member->id,
                    'name' => $firstInstallment->loanAccount->member->name,
                    'phone' => $firstInstallment->loanAccount->member->phone,
                ],
                // We now represent all dues for this member, not a single account/installment
                'account_info' => $installments->count() . ' installment(s) due',
            ]);
        }

        return response()->json([
            'date' => $today->format('Y-m-d'),
            'collection_tasks' => $consolidatedTasks->sortBy('due_date'),
        ]);
    }
}

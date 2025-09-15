<?php

namespace App\Http\Controllers;

use App\Models\CapitalInvestment;
use App\Models\Expense;
use App\Models\GeneralLedger;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanRepayment;
use App\Models\LoanRepaymentSchedule;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsClosure;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ResetTenantController extends Controller
{
    /**
     * Display the tenant data reset confirmation page.
     */
    public function index()
    {
        return view('settings.reset-data');
    }

    /**
     * Perform the data reset for the current tenant.
     */
    /*public function reset(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        // --- SECURITY CHECK: Confirm user's password ---
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password you entered is incorrect.',
            ]);
        }

        // --- THE DELETION LOGIC ---
        // We will delete data in an order that respects foreign key constraints.
        // The BelongsToTenant scope will automatically constrain all these deletions
        // to the current user's tenant.
        try {
            DB::transaction(function () {

                // 1. Delete all media files associated with members
                $memberIds = Member::pluck('id');
                Media::where('model_type', Member::class)->whereIn('model_id', $memberIds)->delete();

                // 2. Delete all Journal Entries (this is the safest way as it cascades)
                // Since all transactions are linked to a Journal, deleting the journal
                // is a clean way to remove all LedgerEntry records.
                // We find all journals whose 'transactionable' parent is being deleted.
                GeneralLedger::whereIn('transactionable_type', [
                    LoanAccount::class,
                    LoanApplication::class, // For fees
                    SavingsTransaction::class,
                    Expense::class,
                    LoanRepayment::class,
                    CapitalInvestment::class, // Add CapitalInvestment if you have it
                    SavingsClosure::class,
                ])->delete();

                // 3. Delete the transactional records themselves.
                // The order here matters less if journals are gone, but it's good practice.
                DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Temporarily disable checks for mass deletion

                SavingsClosure::truncate();
                Expense::truncate();
                CapitalInvestment::truncate(); // Add if you have it
                LoanAccount::truncate(); // Deletes accounts, schedules, repayments
                SavingsAccount::truncate(); // Deletes accounts, transactions
                LoanApplication::truncate();
                Member::truncate(); // Finally, delete all members

                DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Re-enable checks
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred during the reset process: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', 'All transactional data for your organization has been successfully reset.');
    }*/

    public function reset(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password you entered is incorrect.',
            ]);
        }

        $user = auth()->user();
        $tenantId = $user->tenant_id; // Get the current tenant ID

        try {
            DB::transaction(function () use ($tenantId) {

                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                // --- 1. DELETE MEDIA ---
                // This approach still requires fetching member IDs, but this dataset is much smaller
                // than transactions. For extreme scale, this could also be a chunked delete.
                $memberIds = Member::where('tenant_id', $tenantId)->pluck('id');

                if ($memberIds->isNotEmpty()) {
                    // Find all media records associated with the members of this tenant.
                    $mediaQuery = Media::where('model_type', Member::class)->whereIn('model_id', $memberIds);

                    // Process in chunks of 200 to keep memory usage low.
                    // For each chunk, we get the models and then delete them one by one.
                    $mediaQuery->chunkById(200, function ($mediaItems) {
                        foreach ($mediaItems as $mediaItem) {
                            // Calling delete() on the model instance triggers the file deletion.
                            $mediaItem->delete();
                        }
                        // Optional: Add a small sleep to prevent overwhelming the server on huge deletes.
                        // sleep(1);
                    });
                }

                // --- 2. EFFICIENTLY DELETE CHILD RECORDS FIRST ---
                // These are direct, memory-efficient queries. They never load the data into PHP.
                // The `whereIn` queries are constrained by subqueries that are themselves scoped.

                // Delete Ledger Entries
                DB::table('ledger_entries')->whereIn('general_ledger_id', function ($query) use ($tenantId) {
                    $query->select('id')->from('general_ledgers')->where('tenant_id', $tenantId);
                })->delete();

                // Delete General Ledgers (Journals)
                DB::table('general_ledgers')->where('tenant_id', $tenantId)->delete();

                // Delete Loan Repayments and Schedules
                DB::table('loan_repayments')->whereIn('loan_account_id', function ($query) use ($tenantId) {
                    $query->select('id')->from('loan_accounts')->where('tenant_id', $tenantId);
                })->delete();
                DB::table('loan_repayment_schedules')->whereIn('loan_account_id', function ($query) use ($tenantId) {
                    $query->select('id')->from('loan_accounts')->where('tenant_id', $tenantId);
                })->delete();

                // Delete Savings Transactions
                DB::table('savings_transactions')->whereIn('savings_account_id', function ($query) use ($tenantId) {
                    $query->select('id')->from('savings_accounts')->where('tenant_id', $tenantId);
                })->delete();

                // --- 3. DELETE PARENT RECORDS ---
                // These queries are now safe because all their children have been deleted.
                DB::table('savings_closures')->where('tenant_id', $tenantId)->delete();
                DB::table('expenses')->where('tenant_id', $tenantId)->delete();
                DB::table('capital_investments')->where('tenant_id', $tenantId)->delete();
                DB::table('loan_accounts')->where('tenant_id', $tenantId)->delete();
                DB::table('savings_accounts')->where('tenant_id', $tenantId)->delete();
                DB::table('loan_applications')->where('tenant_id', $tenantId)->delete();
                DB::table('members')->where('tenant_id', $tenantId)->delete();

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            });

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->back()->with('error', 'An error occurred during the reset process: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', 'All transactional data for your organization has been successfully reset.');
    }
}

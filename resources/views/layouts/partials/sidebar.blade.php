<nav class="sidebar">
    <div class="sidebar-header">
        <a href="{{ url('/') }}" class="sidebar-brand">
            Micro<span>Finance</span>
        </a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav" id="sidebarNav">
            <li class="nav-item nav-category">Main</li>
            <li class="nav-item {{ active_class(['dashboard']) }}">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="link-icon" data-lucide="home"></i>
                    <span class="link-title">Dashboard</span>
                </a>
            </li>

            {{-- ======================================================= --}}
            {{-- SUPER ADMIN SECTION --}}
            {{-- ======================================================= --}}
            @role('Super Admin')
            <li class="nav-item nav-category">Platform Management</li>
            <li class="nav-item {{ active_class(['super-admin/tenants*']) }}">
                <a href="{{ route('super-admin.tenants.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="users"></i>
                    <span class="link-title">Manage Tenants</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['super-admin/subscription-plans*']) }}">
                <a href="{{ route('super-admin.subscription-plans.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="package"></i>
                    <span class="link-title">Subscription Plans</span>
                </a>
            </li>
            @endrole

            {{-- ======================================================= --}}
            {{-- ORGANIZATION ADMIN SECTION --}}
            {{-- ======================================================= --}}
            @role('Organization Admin')
            <li class="nav-item nav-category">Organization Setup</li>
            <li class="nav-item {{ active_class(['branches*']) }}">
                <a href="{{ route('branches.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="git-branch"></i>
                    <span class="link-title">Branches</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['staff*']) }}">
                <a href="{{ route('staff.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="user-cog"></i>
                    <span class="link-title">Staff</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['organization/profile*']) }}">
                <a href="{{ route('organization.profile.edit') }}" class="nav-link">
                    <i class="link-icon" data-lucide="briefcase"></i>
                    <span class="link-title">Organization Profile</span>
                </a>
            </li>
            @endrole

            {{-- ======================================================= --}}
            {{-- SHARED & FUTURE SECTIONS --}}
            {{-- ======================================================= --}}
            <li class="nav-item nav-category">Microfinance</li>
            <li class="nav-item {{ active_class(['members*']) }}">
                <a href="{{ route('members.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="user-check"></i>
                    <span class="link-title">Members</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['loan-products*', 'loan-applications*', 'loan-accounts*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#loans" role="button" aria-expanded="{{ is_active_route(['loan-products*', 'loan-applications*', 'loan-accounts*']) }}" aria-controls="loans">
                    <i class="link-icon" data-lucide="dollar-sign"></i>
                    <span class="link-title">Loans</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['loan-products*', 'loan-applications*', 'loan-accounts*']) }}" data-bs-parent="#sidebarNav" id="loans">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('organization.loan-products.index') }}" class="nav-link {{ active_class(['loan-products*']) }}">Loan Products</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('loan-applications.index') }}" class="nav-link {{ active_class(['loan-applications*']) }}">Loan Applications</a>
                        </li>
                        {{-- ADD THIS NEW LINK --}}
                        <li class="nav-item">
                            <a href="{{ route('loan-accounts.index') }}" class="nav-link {{ active_class(['loan-accounts*']) }}">Active Loans</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#savings" role="button" aria-expanded="false" aria-controls="savings">
                    <i class="link-icon" data-lucide="piggy-bank"></i>
                    <span class="link-title">Savings</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse" data-bs-parent="#sidebarNav" id="savings">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('organization.savings-products.index') }}" class="nav-link {{ active_class(['savings-products*']) }}">
                                Savings Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('savings-accounts.index') }}" class="nav-link {{ active_class(['savings-accounts*']) }}">All Accounts</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item nav-category">Transactions</li>
            <li class="nav-item {{ active_class(['collection-center*']) }}">
                <a href="{{ route('collection-center.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="combine"></i>
                    <span class="link-title">Collection Center</span>
                </a>
            </li>


            <li class="nav-item nav-category">Reports</li>
            <li class="nav-item {{ active_class(['reports*']) }}">
                <a class="nav-link" data-bs-toggle="collapse" href="#reports" role="button" aria-expanded="{{ is_active_route(['reports*']) }}" aria-controls="reports">
                    <i class="link-icon" data-lucide="bar-chart-2"></i>
                    <span class="link-title">Reports</span>
                    <i class="link-arrow" data-lucide="chevron-down"></i>
                </a>
                <div class="collapse {{ show_class(['reports*']) }}" data-bs-parent="#sidebarNav" id="reports">
                    <ul class="nav sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('reports.collection-sheet') }}" class="nav-link {{ active_class(['reports/collection-sheet']) }}">
                                Daily Collection Sheet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.disbursement-report') }}" class="nav-link {{ active_class(['reports/disbursement-report']) }}">
                                Disbursement Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.collection-report') }}" class="nav-link {{ active_class(['reports/collection-report']) }}">
                                Collection Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.par-report') }}" class="nav-link {{ active_class(['reports/par-report']) }}">
                                Portfolio at Risk (PAR)
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            {{-- ======================================================= --}}
            {{-- ACCOUNTING SECTION --}}
            {{-- ======================================================= --}}
            <li class="nav-item nav-category">Accounting</li>

            <li class="nav-item {{ active_class(['accounting/capital-investments*']) }}">
                <a href="{{ route('accounting.capital-investments.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="trending-up"></i>
                    <span class="link-title">Capital & Investments</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['accounting/expenses*']) }}">
                <a href="{{ route('accounting.expenses.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="dollar-sign"></i>
                    <span class="link-title">Expense Management</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['accounting/chart-of-accounts*']) }}">
                <a href="{{ route('accounting.chart-of-accounts.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="book-copy"></i>
                    <span class="link-title">Chart of Accounts</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['accounting/general-ledger*']) }}">
                <a href="{{ route('accounting.general-ledger') }}" class="nav-link">
                    <i class="link-icon" data-lucide="book-open"></i>
                    <span class="link-title">General Ledger</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['accounting/manual-entries*']) }}">
                <a href="{{ route('accounting.manual-entries.create') }}" class="nav-link">
                    <i class="link-icon" data-lucide="edit"></i>
                    <span class="link-title">Manual Journal Entry</span>
                </a>
            </li>

            {{-- ======================================================= --}}
            {{-- FINANCIAL STATEMENTS SECTION --}}
            {{-- ======================================================= --}}
            <li class="nav-item nav-category">Financial Statements</li>

            <li class="nav-item {{ active_class(['accounting/trial-balance*']) }}">
                <a href="{{ route('accounting.trial-balance') }}" class="nav-link">
                    <i class="link-icon" data-lucide="scale"></i>
                    <span class="link-title">Trial Balance</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['accounting/income-statement*']) }}">
                <a href="{{ route('accounting.income-statement') }}" class="nav-link">
                    <i class="link-icon" data-lucide="file-line-chart"></i>
                    <span class="link-title">Income Statement</span>
                </a>
            </li>
            <li class="nav-item {{ active_class(['accounting/balance-sheet*']) }}">
                <a href="{{ route('accounting.balance-sheet') }}" class="nav-link">
                    <i class="link-icon" data-lucide="file-spreadsheet"></i>
                    <span class="link-title">Balance Sheet</span>
                </a>
            </li>

            @role('Organization Admin')
            <li class="nav-item nav-category">Settings</li>
            <li class="nav-item {{ active_class(['settings*']) }}">
                <a href="{{ route('settings.reset.index') }}" class="nav-link">
                    <i class="link-icon" data-lucide="alert-triangle"></i>
                    <span class="link-title">Reset Data</span>
                </a>
            </li>
            @endrole
        </ul>
    </div>
</nav>

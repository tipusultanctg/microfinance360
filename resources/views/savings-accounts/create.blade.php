@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Open New Savings Account</h6>
            <form class="forms-sample" method="POST" action="{{ route('savings-accounts.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="member_id" class="form-label">Member</label>
                    <select class="form-select @error('member_id') is-invalid @enderror" id="member_id" name="member_id" required>
                        <option value="" disabled @if(!$selectedMember) selected @endif>Select a member...</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @if($selectedMember && $selectedMember->id == $member->id) selected @endif>
                                {{ $member->name }} ({{ $member->member_uid }})
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="savings_product_id" class="form-label">Savings Product</label>
                    <select class="form-select @error('savings_product_id') is-invalid @enderror" id="savings_product_id" name="savings_product_id" required>
                        <option value="" disabled selected>Select a product...</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('savings_product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="initial_deposit" class="form-label">Initial Deposit Amount</label>
                    <input type="number" step="0.01" class="form-control @error('initial_deposit') is-invalid @enderror" id="initial_deposit" name="initial_deposit" value="{{ old('initial_deposit', 0) }}" required>
                    @error('initial_deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="cash_account_id" class="form-label">Deposit Into (Cash/Bank Account) <span class="text-danger">*</span></label>
                    <select name="cash_account_id" class="form-select @error('cash_account_id') is-invalid @enderror" required>
                        @foreach($cashAccounts as $cashAccount)
                            <option value="{{ $cashAccount->id }}">{{ $cashAccount->name }}</option>
                        @endforeach
                    </select>
                    @error('cash_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary me-2">Open Account</button>
                <a href="{{ route('savings-accounts.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection

@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Edit Loan Application #{{ $loanApplication->id }}</h6>

            {{-- IMPORTANT: Add enctype for file uploads --}}
            <form class="forms-sample" method="POST" action="{{ route('loan-applications.update', $loanApplication->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="member_id" class="form-label">Member</label>
                    <select class="form-select @error('member_id') is-invalid @enderror" id="member_id" name="member_id" required>
                        <option value="" disabled>Select a member...</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected(old('member_id', $loanApplication->member_id) == $member->id)>
                                {{ $member->name }} ({{ $member->member_uid }})
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="loan_product_id" class="form-label">Loan Product</label>
                    <select class="form-select @error('loan_product_id') is-invalid @enderror" id="loan_product_id" name="loan_product_id" required>
                        <option value="" disabled>Select a product...</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('loan_product_id', $loanApplication->loan_product_id) == $product->id)>
                                {{ $product->name }} ({{ $product->interest_rate }}% p.a.)
                            </option>
                        @endforeach
                    </select>
                    @error('loan_product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="requested_amount" class="form-label">Requested Amount</label>
                        <input type="number" step="0.01" class="form-control @error('requested_amount') is-invalid @enderror" id="requested_amount" name="requested_amount" value="{{ old('requested_amount', $loanApplication->requested_amount) }}" required>
                        @error('requested_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="requested_term" class="form-label">Requested Term (in installments)</label>
                        <input type="number" class="form-control @error('requested_term') is-invalid @enderror" id="requested_term" name="requested_term" value="{{ old('requested_term', $loanApplication->requested_term) }}" required>
                        @error('requested_term')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="purpose" class="form-label">Purpose of Loan</label>
                    <textarea class="form-control" name="purpose" id="purpose" rows="4">{{ old('purpose', $loanApplication->purpose) }}</textarea>
                </div>

                <hr>

                {{-- --- NEW SECTION to show existing and add new documents --- --}}
                <div class="mb-3">
                    <h6 class="card-title mt-4">Attached Documents</h6>
                    @if($loanApplication->getMedia('loan_documents')->count() > 0)
                        <ul class="list-group mb-3">
                            @foreach($loanApplication->getMedia('loan_documents') as $document)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $document->file_name }}
                                    <a href="{{ $document->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>No documents are currently attached.</p>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="documents" class="form-label">Upload New Documents</label>
                    <input type="file" class="form-control" id="documents" name="documents[]" multiple>
                    <small class="form-text text-muted">You can select multiple files. Uploading will add them to the existing list.</small>
                    @error('documents.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                <a href="{{ route('loan-applications.show', $loanApplication->id) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection

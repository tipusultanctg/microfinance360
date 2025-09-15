@extends('layouts.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('members.index') }}">Members</a></li>
            <li class="breadcrumb-item active" aria-current="page">New Member Registration</li>
        </ol>
    </nav>

    <form class="forms-sample" method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- =================================== --}}
            {{-- LEFT COLUMN: CORE INFO & PHOTO --}}
            {{-- =================================== --}}
            <div class="col-md-4 grid-margin">
                {{-- Primary Info Card --}}
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Primary Information</h6>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                            <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id" required>
                                <option value="" selected disabled>Select branch...</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="registration_date" class="form-label">Registration Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('registration_date') is-invalid @enderror" id="registration_date" name="registration_date" value="{{ old('registration_date', now()->format('Y-m-d')) }}" required>
                            @error('registration_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Photo & Documents Card --}}
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Photo & Documents</h6>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Member Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            @error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="kyc_documents" class="form-label">KYC Documents</label>
                            <input type="file" class="form-control" id="kyc_documents" name="kyc_documents[]" multiple>
                            <small class="form-text text-muted">You can select multiple files.</small>
                            @error('kyc_documents.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- =================================== --}}
            {{-- RIGHT COLUMN: ADDITIONAL DETAILS --}}
            {{-- =================================== --}}
            <div class="col-md-8 grid-margin">
                {{-- Personal Details Card --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Personal Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="father_name" class="form-label">Father's Name</label><input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}"></div>
                            <div class="col-md-6 mb-3"><label for="mother_name" class="form-label">Mother's Name</label><input type="text" name="mother_name" class="form-control" value="{{ old('mother_name') }}"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="gender" class="form-label">Gender</label><select name="gender" class="form-select"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
                            <div class="col-md-6 mb-3"><label for="date_of_birth" class="form-label">Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="marital_status" class="form-label">Marital Status</label><select name="marital_status" class="form-select"><option value="single">Single</option><option value="married">Married</option><option value="divorced">Divorced</option></select></div>
                            <div class="col-md-6 mb-3"><label for="spouse" class="form-label">Spouse's Name</label><input type="text" name="spouse" class="form-control" value="{{ old('spouse') }}"></div>
                            <div class="col-md-6 mb-3"><label for="religion" class="form-label">Religion</label><input type="text" name="religion" class="form-control" value="{{ old('religion') }}"></div>
                        </div>
                    </div>
                </div>

                {{-- Address & Contact Card --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Address & Contact</h6>
                        <div class="mb-3"><label for="phone" class="form-label">Phone Number</label><input type="text" class="form-control" name="phone" value="{{ old('phone') }}"></div>
                        <div class="mb-3"><label for="present_address" class="form-label">Present Address</label><textarea name="present_address" class="form-control" rows="2">{{ old('present_address') }}</textarea></div>
                        <div class="mb-3"><label for="permanent_address" class="form-label">Permanent Address</label><textarea name="permanent_address" class="form-control" rows="2">{{ old('permanent_address') }}</textarea></div>
                    </div>
                </div>

                {{-- Employment Card --}}
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Employment Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="occupation" class="form-label">Occupation</label><input type="text" name="occupation" class="form-control" value="{{ old('occupation') }}"></div>
                            <div class="col-md-6 mb-3"><label for="workplace" class="form-label">Workplace</label><input type="text" name="workplace" class="form-control" value="{{ old('workplace') }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Submission Buttons --}}
        <div class="mt-3">
            <button type="submit" class="btn btn-primary me-2">Register Member</button>
            <a href="{{ route('members.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection

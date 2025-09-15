@extends('layouts.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('super-admin.tenants.index') }}">Tenants</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Tenant</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Create New Tenant and Admin User</h6>
                    <form class="forms-sample" method="POST" action="{{ route('super-admin.tenants.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="organization_name" class="form-label">Organization Name</label>
                            <input type="text" class="form-control @error('organization_name') is-invalid @enderror" id="organization_name" name="organization_name" value="{{ old('organization_name') }}" required>
                            @error('organization_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="subscription_plan_id" class="form-label">Subscription Plan</label>
                            <select class="form-select" id="subscription_plan_id" name="subscription_plan_id">
                                <option value="">None</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('subscription_plan_id') == $plan->id)>
                                        {{ $plan->name }} (${{ $plan->price }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" @selected(old('status') == 'active')>Active</option>
                                <option value="suspended" @selected(old('status') == 'suspended')>Suspended</option>
                            </select>
                        </div>
                        <hr>
                        <h6 class="mt-4 mb-3">Organization Admin Account</h6>
                        <div class="mb-3">
                            <label for="admin_name" class="form-label">Admin Name</label>
                            <input type="text" class="form-control @error('admin_name') is-invalid @enderror" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required>
                            @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Admin Email</label>
                            <input type="email" class="form-control @error('admin_email') is-invalid @enderror" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>
                            @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="admin_password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('admin_password') is-invalid @enderror" id="admin_password" name="admin_password" required>
                            @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="admin_password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="admin_password_confirmation" name="admin_password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Create Tenant</button>
                        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

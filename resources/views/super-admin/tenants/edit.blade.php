@extends('layouts.master')

@section('content')
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('super-admin.tenants.index') }}">Tenants</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Tenant</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Edit Tenant: {{ $tenant->name }}</h6>
                    <form class="forms-sample" method="POST" action="{{ route('super-admin.tenants.update', $tenant->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Organization Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $tenant->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- --- NEW FIELD --- --}}
                        <div class="mb-3">
                            <label for="subscription_plan_id" class="form-label">Subscription Plan</label>
                            <select class="form-select" id="subscription_plan_id" name="subscription_plan_id">
                                <option value="">None</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('subscription_plan_id', $tenant->subscription_plan_id) == $plan->id)>
                                        {{ $plan->name }} (${{ $plan->price }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" @selected(old('status', $tenant->status) == 'active')>Active</option>
                                <option value="suspended" @selected(old('status', $tenant->status) == 'suspended')>Suspended</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

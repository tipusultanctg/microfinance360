<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Use the session to get the tenant_id. This avoids the database query
        // and breaks the infinite loop.
        if (session()->has('tenant_id')) {
            $builder->where($model->getTable() . '.tenant_id', session('tenant_id'));
        }
    }
}

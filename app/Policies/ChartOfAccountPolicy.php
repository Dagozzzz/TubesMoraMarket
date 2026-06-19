<?php

namespace App\Policies;

use App\Models\ChartOfAccount;
use App\Models\User;

class ChartOfAccountPolicy
{
    public function before(?User $user, string $ability): bool
    {
        return true;
    }

    public function viewAny(?User $user = null): bool
    {
        return true;
    }

    public function view(?User $user, ChartOfAccount $chartOfAccount): bool
    {
        return true;
    }

    public function create(?User $user = null): bool
    {
        return true;
    }

    public function update(?User $user, ChartOfAccount $chartOfAccount): bool
    {
        return true;
    }

    public function delete(?User $user, ChartOfAccount $chartOfAccount): bool
    {
        return true;
    }

    public function deleteAny(?User $user = null): bool
    {
        return true;
    }

    public function restore(?User $user, ChartOfAccount $chartOfAccount): bool
    {
        return true;
    }

    public function restoreAny(?User $user = null): bool
    {
        return true;
    }

    public function forceDelete(?User $user, ChartOfAccount $chartOfAccount): bool
    {
        return true;
    }

    public function forceDeleteAny(?User $user = null): bool
    {
        return true;
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function getFilteredUser(array $filters): LengthAwarePaginator
    {
        $query = User::query();

        $allowedColumns = ['id','name','email','job_title','organization'];
        foreach ($filters as $key => $searchValue) {
            if (!empty($searchValue) && in_array($key, $allowedColumns, true)) {
                $query->where($key, 'like', '%' . $searchValue . '%');
            }
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $sortField = $filters['sort_field'] ?? 'id';

        $query->orderBy($sortField, $sortDirection);

        return $query->paginate(5);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);

        $user->update($data);

        return $user;
    }
}

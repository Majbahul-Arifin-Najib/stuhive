<?php

namespace App\Actions;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterUser
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $role = Role::from($data['role']);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $role,
            ]);

            match ($role) {
                Role::Student => $user->student()->create([
                    'student_id' => $data['student_id'],
                    'department' => Arr::get($data, 'department'),
                ]),
                Role::Faculty => $user->faculty()->create([
                    'initial' => Str::upper($data['initial']),
                    'designation' => $data['designation'],
                    'desk_no' => Arr::get($data, 'desk_no'),
                ]),
                Role::Admin => null,
            };

            return $user;
        });
    }
}

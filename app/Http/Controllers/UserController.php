<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles', 'permissions')->get();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(function ($p) {
            $parts = explode('_', $p->name, 3);
            return $parts[2] ?? ($parts[1] ?? 'otros');
        })->sortKeys();

        return view('usuarios.gestion', compact('users', 'roles', 'permissions'));
    }

    public function getUserRolesPermisos(User $user)
    {
        return response()->json([
            'role_ids' => $user->roles->pluck('id'),
            'permission_ids' => $user->getAllPermissions()->pluck('id'),
        ]);
    }

    public function syncRolesPermisos(Request $request)
    {
        $validatedData = DB::transaction(function () use ($request) {
            $data = $request->validate([
                'usuario_id' => 'required|exists:users,id',
                'rol_id.*' => 'nullable|exists:roles,id',
                'permiso_id.*' => 'nullable|exists:permissions,id',
            ]);

            $user = User::findOrFail($data['usuario_id']);
            $roleIds = $data['rol_id'] ?? [];

            $user->syncRoles($roleIds);

            if (!empty($roleIds)) {
                $user->syncPermissions([]);
            } else {
                $user->syncPermissions($data['permiso_id'] ?? []);
            }

            return $data;
        });

        return redirect()->back()->with('success', 'Roles y permisos sincronizados exitosamente.');
    }

    public function asignarRolesPermisos(Request $request)
    {
        $validatedData = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'rol_id.*' => 'nullable|exists:roles,id',
            'permiso_id.*' => 'nullable|exists:permissions,id',
        ]);

        $userId = $validatedData['usuario_id'];
        $roleIds = $validatedData['rol_id'] ?? [];
        $permissionIds = $validatedData['permiso_id'] ?? [];

        $user = User::findOrFail($userId);

        if (!empty($roleIds)) {
            $roles = Role::whereIn('id', $roleIds)->get();
            foreach ($roles as $role) {
                $user->assignRole($role);
            }

            foreach ($permissionIds as $permissionId) {
                $permission = Permission::findOrFail($permissionId);
                foreach ($roleIds as $roleId) {
                    $role = Role::findOrFail($roleId);
                    $role->givePermissionTo($permission);
                }
            }
        } else {
            foreach ($permissionIds as $permissionId) {
                $permission = Permission::findOrFail($permissionId);
                $user->givePermissionTo($permission);
            }
        }

        return redirect()->back()->with('success', 'Roles y permisos asignados exitosamente.');
    }

    public function removerRolesPermisos(Request $request)
    {
        $validatedData = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'rol_id.*' => 'nullable|exists:roles,id',
            'permiso_id.*' => 'nullable|exists:permissions,id',
        ]);

        $user = User::findOrFail($validatedData['usuario_id']);

        foreach (($validatedData['rol_id'] ?? []) as $roleId) {
            $role = Role::findOrFail($roleId);
            $user->removeRole($role);
        }

        foreach (($validatedData['permiso_id'] ?? []) as $permissionId) {
            $permission = Permission::findOrFail($permissionId);
            $user->revokePermissionTo($permission);
        }

        return redirect()->back()->with('success', 'Roles y permisos removidos exitosamente.');
    }
}

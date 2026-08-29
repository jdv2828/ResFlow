<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->get();
        $permissions = Permission::all();
        $groupedPerms = $permissions->groupBy(fn($p) => explode('_', $p->name, 3)[2] ?? explode('_', $p->name, 3)[1] ?? 'otros')->sortKeys();

        return view('roles.index', compact('roles', 'groupedPerms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions(Permission::whereIn('id', $validated['permissions'])->get());
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->route('roles.index')->with('error', 'No se puede modificar el rol admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $validated['name']]);

        if ($request->has('permissions')) {
            $role->syncPermissions(Permission::whereIn('id', $validated['permissions'])->get());
        }

        return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return redirect()->route('roles.index')->with('error', 'No se puede eliminar el rol admin.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
    }
}

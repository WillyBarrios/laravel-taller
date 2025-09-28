<?php

namespace App\Policies;

use App\Models\Usuario;

class UsuarioPolicy
{
    /**
     * Determine whether the authenticated user can update the given usuario record.
     * Regla: Puede actualizar si:
     *  - Es admin (rol == 'admin')
     *  - O es el mismo usuario (mismo id)
     */
    public function update(?Usuario $authUser, Usuario $target): bool
    {
        if (!$authUser) {
            return false; // no autenticado
        }
        // Normalizar rol por si acaso
        $role = strtolower((string)$authUser->rol);
        return $role === 'admin' || $authUser->id === $target->id;
    }
}

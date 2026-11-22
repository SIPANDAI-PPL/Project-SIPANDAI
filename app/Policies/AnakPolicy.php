<?php

namespace App\Policies;

use App\Models\Anak;
use App\Models\User;

// Fitur: PBI-5 Kelola Data Dasar Anak (NFR-04: pembatasan akses per kelas)
class AnakPolicy
{
    /**
     * Guru hanya boleh melihat dan mengelola anak di kelas yang diampunya.
     * Guru lain dan Admin ditolak (403).
     */
    public function kelola(User $user, Anak $anak): bool
    {
        return $user->peran === 'guru'
            && $anak->kelas?->guru_id === $user->id;
    }
}

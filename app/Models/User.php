<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $authPasswordName = 'kata_sandi';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'nama_pengguna',
        'email',
        'kata_sandi',
        'peran',
        'nip',
        'aktif',
        'wajib_ganti_sandi',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kata_sandi' => 'hashed',
            'aktif' => 'boolean',
            'wajib_ganti_sandi' => 'boolean',
        ];
    }

    public function ruteDashboard(): string
    {
        return $this->peran === 'admin' ? 'admin.dashboard' : 'guru.dashboard';
    }
}

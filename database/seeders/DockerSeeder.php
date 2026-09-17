<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DockerSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SetorSeeder::class);
        // Never reset an existing installation's credentials.
        if (Usuario::exists()) {
            return;
        }
        $password = env('ADMIN_PASSWORD');
        if (!$password || strlen($password) < 12) {
            throw new \RuntimeException('Configure ADMIN_PASSWORD com pelo menos 12 caracteres.');
        }
        Usuario::create([
            'name' => 'Administrador',
            'username' => env('ADMIN_USERNAME', 'admin'),
            'setor' => 'Administrativo',
            'password' => Hash::make($password),
            'permissao' => 'Administrador',
        ]);
    }
}

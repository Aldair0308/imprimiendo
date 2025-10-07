<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Administrador Principal',
                'email' => 'admin@imprimeindo.com',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'permissions' => [
                    'manage_printers',
                    'manage_users',
                    'view_reports',
                    'manage_settings',
                    'manage_finances',
                    'system_maintenance'
                ],
                'is_active' => true,
                'last_login' => null,
                'last_activity' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Operador de Sistema',
                'email' => 'operador@imprimeindo.com',
                'password' => Hash::make('operador123'),
                'role' => 'operator',
                'permissions' => [
                    'manage_printers',
                    'view_reports',
                    'manage_print_jobs'
                ],
                'is_active' => true,
                'last_login' => null,
                'last_activity' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Técnico de Mantenimiento',
                'email' => 'tecnico@imprimeindo.com',
                'password' => Hash::make('tecnico123'),
                'role' => 'technician',
                'permissions' => [
                    'manage_printers',
                    'system_maintenance',
                    'view_reports'
                ],
                'is_active' => true,
                'last_login' => null,
                'last_activity' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        foreach ($admins as $adminData) {
            Admin::create($adminData);
        }

        $this->command->info('✅ Administradores creados exitosamente');
        $this->command->info('');
        $this->command->info('🔐 Credenciales de acceso:');
        $this->command->info('');
        $this->command->info('👑 SUPER ADMINISTRADOR:');
        $this->command->info('   Email: admin@imprimeindo.com');
        $this->command->info('   Password: admin123');
        $this->command->info('');
        $this->command->info('👤 OPERADOR:');
        $this->command->info('   Email: operador@imprimeindo.com');
        $this->command->info('   Password: operador123');
        $this->command->info('');
        $this->command->info('🔧 TÉCNICO:');
        $this->command->info('   Email: tecnico@imprimeindo.com');
        $this->command->info('   Password: tecnico123');
        $this->command->info('');
        $this->command->info('⚠️  IMPORTANTE: Cambia estas contraseñas en producción');
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando la siembra de datos para Imprimeindo...');
        $this->command->info('');

        // Ejecutar seeders en orden específico
        $this->call([
            AdminSeeder::class,
            PrinterSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Siembra de datos completada exitosamente');
        $this->command->info('🎯 El sistema Imprimeindo está listo para usar');
        $this->command->info('');
        $this->command->info('🌐 Accede al panel administrativo en: /admin/login');
        $this->command->info('📱 Accede al sistema principal en: /');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Printer;
use Carbon\Carbon;

class PrinterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $printers = [
            [
                'name' => 'Impresora Principal',
                'model' => 'EPSON L325',
                'ip_address' => '192.168.1.100',
                'port' => 9100,
                'location' => 'Planta Baja - Área de Servicio Principal',
                'description' => 'Impresora principal del sistema, ubicada en el área de mayor tráfico',
                'is_active' => true,
                'supports_color' => true,
                'supports_duplex' => false,
                'priority' => 1,
                'status' => 'online',
                'queue_count' => 0,
                'completed_jobs' => 0,
                'total_pages_printed' => 0,
                'last_activity' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Impresora Secundaria',
                'model' => 'EPSON L355',
                'ip_address' => '192.168.1.101',
                'port' => 9100,
                'location' => 'Primer Piso - Área de Respaldo',
                'description' => 'Impresora de respaldo para alta demanda y trabajos especiales',
                'is_active' => true,
                'supports_color' => true,
                'supports_duplex' => true,
                'priority' => 2,
                'status' => 'online',
                'queue_count' => 0,
                'completed_jobs' => 0,
                'total_pages_printed' => 0,
                'last_activity' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Impresora Express',
                'model' => 'EPSON L375',
                'ip_address' => '192.168.1.102',
                'port' => 9100,
                'location' => 'Área de Atención Rápida',
                'description' => 'Impresora dedicada para trabajos urgentes y de pocas páginas',
                'is_active' => true,
                'supports_color' => false,
                'supports_duplex' => false,
                'priority' => 1,
                'status' => 'online',
                'queue_count' => 0,
                'completed_jobs' => 0,
                'total_pages_printed' => 0,
                'last_activity' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Impresora Mantenimiento',
                'model' => 'EPSON L365',
                'ip_address' => '192.168.1.103',
                'port' => 9100,
                'location' => 'Área Técnica',
                'description' => 'Impresora en mantenimiento preventivo - Temporalmente fuera de servicio',
                'is_active' => false,
                'supports_color' => true,
                'supports_duplex' => true,
                'priority' => 3,
                'status' => 'maintenance',
                'queue_count' => 0,
                'completed_jobs' => 0,
                'total_pages_printed' => 0,
                'last_activity' => Carbon::now()->subHours(24),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        foreach ($printers as $printerData) {
            Printer::create($printerData);
        }

        $this->command->info('✅ Impresoras de ejemplo creadas exitosamente');
        $this->command->info('📊 Total de impresoras: ' . count($printers));
        $this->command->info('🟢 Impresoras activas: ' . collect($printers)->where('is_active', true)->count());
        $this->command->info('🔧 Impresoras en mantenimiento: ' . collect($printers)->where('status', 'maintenance')->count());
    }
}
<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@muamanager.id'],
            [
                'name'     => 'Admin MUA Manager',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // Create demo MUA user
        $mua = User::firstOrCreate(
            ['email' => 'rina@muamanager.id'],
            [
                'name'        => 'Rina Makeup Artist',
                'password'    => Hash::make('password'),
                'role'        => 'mua',
                'phone'       => '081234567890',
                'studio_name' => 'Rina Beauty Studio',
            ]
        );

        // Create services
        $serviceData = [
            ['name' => 'Bridal Makeup',     'price' => 1500000, 'duration' => 180, 'description' => 'Makeup pengantin lengkap'],
            ['name' => 'Party Makeup',       'price' => 500000,  'duration' => 90,  'description' => 'Makeup pesta & acara spesial'],
            ['name' => 'Photoshoot Makeup',  'price' => 750000,  'duration' => 120, 'description' => 'Makeup untuk sesi foto'],
            ['name' => 'Wisuda Makeup',      'price' => 350000,  'duration' => 60,  'description' => 'Makeup untuk hari wisuda'],
        ];

        foreach ($serviceData as $svc) {
            Service::firstOrCreate(
                ['user_id' => $mua->id, 'name' => $svc['name']],
                array_merge($svc, ['user_id' => $mua->id, 'is_active' => true])
            );
        }

        // Create sample clients
        $clientData = [
            ['name' => 'Siti Rahayu',  'phone' => '08112345678', 'email' => 'siti@email.com'],
            ['name' => 'Dewi Pratiwi', 'phone' => '08198765432', 'email' => 'dewi@email.com'],
            ['name' => 'Ayu Lestari',  'phone' => '08155556666', 'email' => null],
        ];

        $clients = [];
        foreach ($clientData as $c) {
            $clients[] = Client::firstOrCreate(
                ['user_id' => $mua->id, 'name' => $c['name']],
                array_merge($c, ['user_id' => $mua->id])
            );
        }

        // Create sample bookings
        $services = Service::where('user_id', $mua->id)->get();

        if (Booking::where('user_id', $mua->id)->count() === 0) {
            foreach ($clients as $i => $client) {
                $service     = $services[$i % $services->count()];
                $bookingDate = now()->addDays($i + 1)->setHour(10)->setMinute(0);

                $booking = Booking::create([
                    'user_id'      => $mua->id,
                    'client_id'    => $client->id,
                    'service_id'   => $service->id,
                    'booking_date' => $bookingDate,
                    'duration'     => $service->duration,
                    'price'        => $service->price,
                    'status'       => 'confirmed',
                    'location'     => 'Jakarta Selatan',
                ]);

                Invoice::create([
                    'booking_id'     => $booking->id,
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT),
                    'subtotal'       => $service->price,
                    'tax'            => 0,
                    'total'          => $service->price,
                    'status'         => 'unpaid',
                    'due_date'       => $bookingDate->toDateString(),
                ]);
            }
        }
    }
}

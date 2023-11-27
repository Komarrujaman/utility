<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use GuzzleHttp\Client;

class getDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-device';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Get All Device dari API Antares
        $client = new Client();
        $headers = [
            'X-M2M-Origin' => 'b07f83b1409132e9:84c6cc0b97b86892',
            'Accept' => 'application/json'
        ];

        $limit = 2000; // Jumlah item per permintaan
        $offset = 0;  // Offset awal

        $allDevices = [];

        do {
            // Setiap iterasi, update offset pada URL
            $url = "https://platform.antares.id:8443/~/antares-cse/antares-id/pdam_serang/?fu=1&ty=3&lim={$limit}&ofst={$offset}";

            $response = $client->get($url, [
                'headers' => $headers
            ]);

            $data = json_decode($response->getBody(), true);

            // Ambil device name saja
            $devices = array_map(function ($item) {
                return basename($item);
            }, $data['m2m:uril']);

            // Gabungkan hasil ke dalam array utama
            $allDevices = array_merge($allDevices, $devices);

            // Update offset untuk iterasi berikutnya
            $offset += $limit;
        } while (count($devices) === $limit); // Terus lakukan selama jumlah perangkat dalam satu permintaan mencapai batas

        // Simpan perangkat ke dalam database atau lakukan tindakan lain
        foreach ($allDevices as $device) {
            if (!Device::where('device_name', $device)->exists()) {
                Device::create(['device_name' => $device]);
            }
        }

        $this->info('Get Device From Antares Completed');
    }
}

<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lastUplink extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'counter',
        'snr',
        'rssi',
        'time',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public static function getLastUplink()
    {
        //get device dari db
        $devices = Device::device();
        $client = new Client();
        $result = []; // Array untuk menyimpan hasil dari setiap perangkat

        foreach ($devices as $device) {
            $id = $device['id'];
            $device_name = $device['device_name'];

            try {
                $response = $client->get("https://platform.antares.id:8443/~/antares-cse/antares-id/pdam_serang/" . $device_name . "/la", [
                    'headers' => [
                        'X-M2M-Origin' => 'b07f83b1409132e9:84c6cc0b97b86892',
                        'Accept' => 'application/json'
                    ]
                ]);

                // Cek apakah status response adalah 200
                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getBody(), true);
                    $con = json_decode($data['m2m:cin']['con']);
                    $counter = $con->counter;
                    $snr = $con->radio->hardware->snr;
                    $rssi = $con->radio->hardware->rssi;
                    $time = $data['m2m:cin']['lt'];

                    // Simpan hasil ke dalam array
                } else {
                    $counter = 'no data';
                    $snr = 'no data';
                    $rssi = 'no data';
                    $time = 'no data';
                }
                $result[] = [
                    'device' => $device_name,
                    'counter' => $counter,
                    'snr' => $snr,
                    'rssi' => $rssi,
                    'time' => $time
                ];
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                // Tangani exception jika terjadi kesalahan pada permintaan HTTP
                // Anda dapat menambahkan log atau tindakan lain sesuai kebutuhan
                // Tambahkan data "no data" ke array hasil
                if ($e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                    $counter = 'no data';
                    $snr = 'no data';
                    $rssi = 'no data';
                    $time = 'no data';
                } else {
                    // Tangani error lain jika diperlukan
                    // Misalnya, jika terjadi kesalahan selain 404
                    // $timestamps[] = "error occurred";
                }
            }

            $existingData = lastUplink::where('device_id', $id)->where('time', $time)->first();

            if (!$existingData) {
                $data = lastUplink::create([
                    'device_id' => $id,
                    'counter' => $counter,
                    'snr' => $snr,
                    'rssi' => $rssi,
                    'time' => $time
                ]);
            }
        }

        // Kembalikan array hasil di luar loop
        return $result;
    }
}

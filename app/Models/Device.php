<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['device_name'];

    public function firstUplink()
    {
        return $this->hasMany(FirstUplink::class, 'device_id');
    }

    public function lastUplink()
    {
        return $this->hasMany(lastUplink::class, 'device_id');
    }

    public static function uplink()
    {
        $data = Device::select('devices.*')
            ->addSelect(DB::raw('(SELECT counter FROM first_uplinks WHERE device_id = devices.id ORDER BY created_at DESC LIMIT 1) AS first_counter'))
            ->addSelect(DB::raw('(SELECT snr FROM first_uplinks WHERE device_id = devices.id ORDER BY created_at DESC LIMIT 1) AS first_snr'))
            ->addSelect(DB::raw('(SELECT rssi FROM first_uplinks WHERE device_id = devices.id ORDER BY created_at DESC LIMIT 1) AS first_rssi'))
            ->addSelect(DB::raw('(SELECT time FROM first_uplinks WHERE device_id = devices.id ORDER BY created_at DESC LIMIT 1) AS first_time'))
            ->addSelect(DB::raw('(SELECT counter FROM last_uplinks WHERE device_id = devices.id ORDER BY created_at DESC LIMIT 1) AS last_counter'))
            ->addSelect(DB::raw('(SELECT snr FROM last_uplinks WHERE device_id = devices.id ORDER BY created_at DESC LIMIT 1) AS last_snr'))
            ->addSelect(DB::raw('(SELECT rssi FROM last_uplinks WHERE device_id = devices.id ORDER BY created_at DESC LIMIT 1) AS last_rssi'))
            ->addSelect(DB::raw('(SELECT time FROM last_uplinks WHERE device_id = devices.id ORDER BY created_at DESC LIMIT 1) AS last_time'))
            ->get();

        return $data;
    }

    public static function device()
    {
        $all = Self::all();
        $device = $all->map(function ($fromDB) {
            return [
                'id' => $fromDB->id,
                'device_name' => $fromDB->device_name,
            ];
        });
        return $device;
    }

    public static function AntaresDevice()
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
        // Menonaktifkan sementara pembatasan integritas referensial
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Hapus perangkat yang tidak ada di respons API
        Device::whereNotIn('device_name', $allDevices)->delete();

        // Menyalakan kembali pembatasan integritas referensial
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return $allDevices;
    }
}

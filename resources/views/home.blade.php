<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <title>Counter Monitor</title>
</head>

<body>
    <div class="container mt-5">
        <div class="card mt-5 border shadow">
            <div class="card-header">
                <h3>Counter Monitor</h1>
                    <a href="{{route('get-device')}}" class="btn btn-success">Update Device</a>
                    <a href="{{route('get-first-uplink')}}" class="btn btn-primary">Get First Uplink</a>
                    <a href="{{route('get-last-uplink')}}" class="btn btn-secondary">Get Last Uplink</a>
            </div>
            <div class="card-body">
                <table id="table" class="table table-bordered table-responsive table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Device</th>
                            <th>First Counter</th>
                            <th>Last Counter</th>
                            <th>First Uplink</th>
                            <th>Last Uplink</th>
                            <th>Uptime</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($device as $device )
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$device->device_name}}</td>
                            <td>{{$device->first_counter}}</td>
                            <td>{{$device->last_counter}}</td>
                            @if ($device->first_time != 'no data')
                            <td>{{ date('d/m/Y - H:i:s', strtotime($device->first_time)) }}</td>
                            @else
                            <td>No Data</td>
                            @endif

                            @if ($device->last_time != 'no data')
                            <td>{{ date('d/m/Y - H:i:s', strtotime($device->last_time)) }}</td>
                            @else
                            <td>No Data</td>
                            @endif

                            <?php
                            if ($device->first_time != 'no data' && $device->last_time != 'no data') {
                                $firstTime = new DateTime($device->first_time);
                                $lastTime = new DateTime($device->last_time);

                                // Hitung selisih antara dua waktu
                                $interval = $firstTime->diff($lastTime);

                                // Tampilkan selisih dalam format hari
                                $selisihHari = $interval->days;
                            } else {
                                $selisihHari = 'No Data';
                            }

                            // Logika penentuan kelas dan label warna
                            $class = '';
                            $label = '';

                            if ($selisihHari === 'No Data') {
                                $class = 'bg-secondary';
                                $label = 'No Uplink';
                            } elseif ($selisihHari >= 3 && $device->last_counter >= 3) {
                                $class = 'bg-success';
                                $label = 'Lolos';
                            } elseif ($selisihHari <= 3 && $device->last_counter >= 3) {
                                $class = 'bg-success';
                                $label = 'Lolos';
                            } elseif ($selisihHari >= 3 && $device->last_counter <= 2) {
                                $class = 'bg-danger';
                                $label = 'Tidak Lolos';
                            } elseif ($selisihHari <= 1) {
                                $class = 'bg-warning';
                                $label = 'Uplink < 2 Hari';
                            }
                            ?>

                            <td>{{$selisihHari}}</td>

                            <td>
                                <div class="badge {{ $class }}">{{ $label }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        new DataTable('#table');
    </script>
</body>

</html>
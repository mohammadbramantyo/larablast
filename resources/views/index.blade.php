<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Records</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Upload CSV</h1>
        <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="csv_file" required>
            <button type="submit">Upload</button>
        </form>

        <!-- Filter -->
        <form method="GET" action="{{ route('dashboard') }}" class="container mt-4">
            <div class="row mb-3">
                <!-- Minimal Age -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_age">Minimal Age:</label>
                        <input type="number" name="min_age" id="min_age" value="{{ request('min_age') }}" class="form-control">
                    </div>
                </div>
                <!-- Maximal Age -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_age">Maximal Age:</label>
                        <input type="number" name="max_age" id="max_age" value="{{ request('max_age') }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <!-- Kota Rumah -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kota_rmh">Kota Rumah:</label>
                        <select name="kota_rmh" id="kota_rmh" class="form-control">
                            <option value="">--Select Kota--</option>
                            @foreach ($kotaOptions as $kota)
                            <option value="{{ $kota }}" {{ $selectedKotarmh == $kota ? 'selected':'' }}>
                                {{ $kota }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Kecamatan Rumah -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kec_rmh">Kecamatan Rumah:</label>
                        <select name="kec_rmh" id="kec_rmh" class="form-control">
                            <option value="">--Select Kecamatan--</option>
                            @foreach ($kecOptions as $kec)
                            <option value="{{ $kec }}" {{ $selectedKecrmh == $kec ? 'selected':'' }}>
                                {{ $kec }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Kota Perusahaan -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kota_perush">Domisili Perusahaan:</label>
                        <select name="kota_perush" id="kota_perush" class="form-control">
                            <option value="">--Select Kota--</option>
                            @foreach ($kotaperushOptions as $kota)
                            <option value="{{ $kota }}" {{ $selectedKotaperush == $kota ? 'selected':'' }}>
                                {{ $kota }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>





        <h1 class="mb-4">Data Records</h1>
        <form method="GET" action="{{ route('export') }}" class="d-inline">
            <input type="hidden" name="min_age" value="{{ request('min_age') }}">
            <input type="hidden" name="max_age" value="{{ request('max_age') }}">
            <input type="hidden" name="kota_rmh" value="{{ request('kota_rmh') }}">
            <input type="hidden" name="kec_rmh" value="{{ request('kec_rmh') }}">
            <input type="hidden" name="kota_perush" value="{{ request('kota_perush') }}">
            <button type="submit" class="btn btn-success">Export CSV</button>
        </form>

        <p class="p-2">Showing {{ $master_data->count() }} of {{ $master_data->total() }} Results.</p>

        <div class="table-container overflow-x-scroll">
            <table class="table table-bordered table-striped p-2 overflow-scroll">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>DOB</th>
                        <th>HP Utama</th>
                        <th>HP Kedua</th>
                        <th>Alamat Rumah</th>
                        <th>Kecamatan Rumah</th>
                        <th>Kota Rumah</th>
                        <th>Perusahaan</th>
                        <th>Jabatan</th>
                        <th>Alamat Perusahaan</th>
                        <th>Kota Perusahaan</th>
                        <th>Kode Pos</th>
                        <th>Telp Rumah</th>
                        <th>Telp Kantor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($master_data as $data)
                    <tr>
                        <td>{{ $loop->iteration + ($master_data->currentPage() - 1) * $master_data->perPage() }}</td>
                        <td>{{ $data->nama }}</td>
                        <td>{{ $data->dob }}</td>
                        <td>{{ $data->hp_utama }}</td>
                        <td>{{ $data->hp_2 }}</td>
                        <td>{{ $data->alamat_rumah }}</td>
                        <td>{{ $data->kec_rmh }}</td>
                        <td>{{ $data->kota_rmh }}</td>
                        <td>{{ $data->perusahaan }}</td>
                        <td>{{ $data->jabatan }}</td>
                        <td>{{ $data->alamat_perush }}</td>
                        <td>{{ $data->kota_perush }}</td>
                        <td>{{ $data->kode_pos }}</td>
                        <td>{{ $data->telp_rumah }}</td>
                        <td>{{ $data->telp_kantor }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $master_data->links('pagination::bootstrap-4') }}
    </div>
</body>

</html>
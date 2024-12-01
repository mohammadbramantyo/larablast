@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">


    <div id="chart-container" class="container flex justify-start gap-4 max-h-96 p-4">
        <!-- Job Pie Chart -->
        <div class="mt-6">
            <h2 class="text-lg text-center">Jabatan</h2>
            <canvas id="jobChart"></canvas>
        </div>
        <div class="mt-6">
            <h2 class="text-lg text-center">Domisili</h2>
            <canvas id="cityChart"></canvas>
        </div>

    </div>


    <!-- Filter -->
    <div class="p-6 bg-white shadow-md rounded-lg">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-6">
            <div class="w-full sm:w-1/2 lg:w-1/4 mb-4">
                <!-- Minimal Age -->
                <div class="form-group">
                    <label for="min_age" class="block text-sm font-medium text-gray-700">Minimal Age:</label>
                    <input type="number" name="min_age" id="min_age" value="{{ request('min_age') }}" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="w-full sm:w-1/2 lg:w-1/4 mb-4">
                <!-- Maximal Age -->
                <div class="form-group">
                    <label for="max_age" class="block text-sm font-medium text-gray-700">Maximal Age:</label>
                    <input type="number" name="max_age" id="max_age" value="{{ request('max_age') }}" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="w-full sm:w-1/2 lg:w-1/4 mb-4">
                <!-- Kota Rumah -->
                <div class="form-group">
                    <label for="kota_rmh" class="block text-sm font-medium text-gray-700">Kota Rumah:</label>
                    <select name="kota_rmh" id="kota_rmh" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">--Select Kota Domisili--</option>
                        @foreach ($kota_rmh_options as $kota_rmh)
                        <option value="{{ $kota_rmh }}" {{ $selectedKotarmh == $kota_rmh ? 'selected':'' }}>
                            {{ $kota_rmh }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="w-full sm:w-1/2 lg:w-1/4 mb-4">
                <!-- Kota perusahaan -->
                <div class="form-group">
                    <label for="kota_perush" class="block text-sm font-medium text-gray-700">Kota Perushaan:</label>
                    <select name="kota_perush" id="kota_perush" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">--Select Kota Perusahaan--</option>
                        @foreach ($kota_perush_options as $kota_perush)
                        <option value="{{ $kota_perush }}" {{ $selectedKotaperush == $kota_perush ? 'selected':'' }}>
                            {{ $kota_perush }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="w-full sm:w-1/2 lg:w-1/4 mb-4">
                <!-- Jabatan -->
                <div class="form-group">
                    <label for="jabatan" class="block text-sm font-medium text-gray-700">Jabatan:</label>
                    <select name="jabatan" id="jabatan" class="form-control mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">--Select Jabatan--</option>
                        @foreach ($jabatan_options as $jabatan)
                        <option value="{{ $jabatan }}" {{ $selectedJabatan == $jabatan ? 'selected':'' }}>
                            {{ $jabatan }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="w-full sm:w-auto mt-4 flex gap-3">
                <button type="submit" class="btn btn-primary py-2 px-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">Filter</button>

                <a href="{{ route('dashboard') }}" class="btn btn-secondary py-2 px-4 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Clear</a>
            </div>

        </form>
    </div>

    <h1 class="text-4xl p-4">Leads Table</h1>
    <div id="info-container" class="flex justify-between mt-2">
        <p class="p-2">Showing {{ $master_data->count() }} of {{ $master_data->total() }} Results.</p>
        <div id="button-container" class=" flex gap-1 justify-end p-2">
            <form method="GET" action="{{ route('export') }}" class="d-inline">
                <input type="hidden" name="min_age" value="{{ request('min_age') }}">
                <input type="hidden" name="max_age" value="{{ request('max_age') }}">
                <input type="hidden" name="kota_rmh" value="{{ request('kota_rmh') }}">
                <input type="hidden" name="kec_rmh" value="{{ request('kec_rmh') }}">
                <input type="hidden" name="kota_perush" value="{{ request('kota_perush') }}">
                <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition">Export Data</button>
            </form>

            @include('components.uploadForm')
        </div>
    </div>

    <div class="table-container overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-200 text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-2 py-1 text-left">No.</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Nama</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">DOB</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">HP Utama</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">HP Kedua</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Alamat Rumah</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Kecamatan Rumah</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Kota Rumah</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Perusahaan</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Jabatan</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Alamat Perusahaan</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Kota Perusahaan</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Kode Pos</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Telp Rumah</th>
                    <th class="border border-gray-300 px-2 py-1 text-left">Telp Kantor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($master_data as $data)
                <tr class="odd:bg-white even:bg-gray-50">
                    <td class="border border-gray-300 px-2 py-1">{{ $loop->iteration + ($master_data->currentPage() - 1) * $master_data->perPage() }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->nama }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->dob }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->hp_utama }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->hp_2 }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->alamat_rumah }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->kec_rmh }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->kota_rmh }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->perusahaan }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->jabatan }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->alamat_perush }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->kota_perush }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->kode_pos }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->telp_rumah }}</td>
                    <td class="border border-gray-300 px-2 py-1">{{ $data->telp_kantor }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="border border-gray-300 px-2 py-1 text-center">No data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2 text-sm">
        {{ $master_data->links() }}
    </div>


</div>
@endsection



@push('scripts')
<!-- Include Chart.js only for the dashboard page -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Parse data
    var jobLabels = @json($job_labels);
    var jobCounts = @json($job_counts);
    var cityLabels = @json($city_labels);
    var cityCounts = @json($city_counts);


    // Job Pie Chart
    var jobCtx = document.getElementById('jobChart').getContext('2d');
    new Chart(jobCtx, {
        type: 'pie',
        data: {
            labels: jobLabels,
            datasets: [{
                label: 'Jumlah',
                data: jobCounts,
                backgroundColor: ["#f47068", "#ffb3ae", "#fff4f1", "#1697a6", "#0e606b", "#ffc24b"],
            }]
        }
    });


    // city chart
    var jobCtx = document.getElementById('cityChart').getContext('2d');
    new Chart(jobCtx, {
        type: 'pie',
        data: {
            labels: cityLabels,
            datasets: [{
                label: 'Jumlah',
                data: cityCounts,
                backgroundColor: ["#f47068", "#ffb3ae", "#fff4f1", "#1697a6", "#0e606b", "#ffc24b"],
            }]
        }
    });
</script>


@endpush
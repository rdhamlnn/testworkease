@extends('mekanik.master')

@section('title', 'Dashboard Mekanik')

@section('styles')
<!-- CSS Libraries -->
<link rel="stylesheet" href="{{ asset('assets/modules/jqvmap/dist/jqvmap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/weather-icon/css/weather-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/weather-icon/css/weather-icons-wind.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/modules/summernote/summernote-bs4.css') }}">
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary"><i class="far fa-user"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Laporan Harian Mekanik</h4>
                    </div>
                    <div class="card-body">{{ $totalLaporanHarian ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger"><i class="far fa-newspaper"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Laporan Pemakaian Barang</h4>
                    </div>
                    <div class="card-body">{{ $totalLaporanBarang ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning"><i class="far fa-file"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Semua Laporan</h4>
                    </div>
                    <div class="card-body">{{ ($totalLaporanHarian ?? 0) + ($totalLaporanBarang ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-calendar-alt"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Laporan Bulan Ini</h4>
                    </div>
                    <div class="card-body">{{ $laporanBulanIni ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Trend Laporan Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Trend Laporan per Bulan</h4>
                </div>
                <div class="card-body">
                    <canvas id="trendLaporanChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Material Usage Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Pemakaian Barang per Unit</h4>
                </div>
                <div class="card-body">
                    <canvas id="materialUsageChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Aktivitas Terbaru</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama Unit</th>
                                    <th>Keluhan/Kerusakan</th>
                                    <th>Tindakan Perbaikan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities ?? [] as $index => $activity)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($activity->tanggal)->format('d/m/Y') }}</td>
                                    <td>{{ $activity->nama_unit }}</td>
                                    <td>{{ $activity->keluhan_kerusakan }}</td>
                                    <td>{{ $activity->tindakan_perbaikan }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada aktivitas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@section('scripts')
<!-- JS Libraries -->
<script src="{{ asset('assets/modules/chart.min.js') }}"></script>
<script src="{{ asset('assets/modules/summernote/summernote-bs4.js') }}"></script>

<script>
// Data dari Controller (Real Database)
const materialUsagePerUnitData = @json($materialUsagePerUnit ?? (object)[]);
const monthlyLaporanTrendData = @json($monthlyLaporanTrend ?? []);

// Month names for labels
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Trend Laporan Chart (Line)
const trendLaporanCtx = document.getElementById('trendLaporanChart').getContext('2d');
const trendLaporanChart = new Chart(trendLaporanCtx, {
    type: 'line',
    data: {
        labels: monthlyLaporanTrendData.map(item => monthNames[item.month - 1] + ' ' + item.year),
        datasets: [{
            label: 'Jumlah Laporan',
            data: monthlyLaporanTrendData.map(item => item.total),
            borderColor: '#1B3C88',
            backgroundColor: 'rgba(27, 60, 136, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Material Usage Chart (Bar)
const materialUsageCtx = document.getElementById('materialUsageChart').getContext('2d');
const materialUsageChart = new Chart(materialUsageCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(materialUsagePerUnitData),
        datasets: [{
            label: 'Total Harga (Rp)',
            data: Object.values(materialUsagePerUnitData),
            backgroundColor: [
                '#1B3C88',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#17a2b8',
                '#6c757d'
            ],
            borderColor: '#fff',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});


</script>

<!-- Page Specific JS File -->
<script src="{{ asset('assets/js/page/index-0.js') }}"></script>
@endsection

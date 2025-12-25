@extends('logistik.master')

@section('title', 'Dashboard Logistik')

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
                <div class="card-icon bg-primary"><i class="fas fa-clipboard-check"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>WO Diterima</h4>
                    </div>
                    <div class="card-body">{{ $totalWODiterima ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger"><i class="fas fa-shopping-cart"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Perlu Diproses</h4>
                    </div>
                    <div class="card-body">{{ $perluDiproses ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning"><i class="fas fa-box-open"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Barang Masuk</h4>
                    </div>
                    <div class="card-body">{{ $barangMasuk ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-hand-holding"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Barang Keluar</h4>
                    </div>
                    <div class="card-body">{{ $barangKeluar ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Work Order Trend Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Trend WO Diterima per Bulan</h4>
                </div>
                <div class="card-body">
                    <canvas id="woTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Permintaan Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Status Work Order</h4>
                </div>
                <div class="card-body">
                    <canvas id="statusWOChart" height="200"></canvas>
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
                                    <th>No. Work Order</th>
                                    <th>Divisi Pengaju</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities ?? [] as $index => $activity)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $activity->no_surat_pengajuan }}</td>
                                    <td>{{ $activity->divisi_pengaju }}</td>
                                    <td>{{ $activity->unit }}</td>
                                    <td>
                                        @if($activity->status == 'Selesai')
                                            <span class="badge badge-success">{{ $activity->status }}</span>
                                        @elseif($activity->status == 'Ditolak')
                                            <span class="badge badge-danger">{{ $activity->status }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ $activity->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($activity->tanggal)->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada aktivitas</td>
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
const monthlyWOTrendData = @json($monthlyWOTrend ?? []);
const statusWOData = @json($statusWO ?? []);

// Helper function untuk format bulan
function formatMonthData(data) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const labels = [];
    const values = [];
    
    data.forEach(item => {
        labels.push(months[item.month - 1] + ' ' + item.year);
        values.push(item.total);
    });
    
    return { labels, values };
}

// Work Order Trend Chart (Line)
const woTrendCtx = document.getElementById('woTrendChart');
if (woTrendCtx) {
    const woTrendData = formatMonthData(monthlyWOTrendData);
    const woTrendChart = new Chart(woTrendCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: woTrendData.labels.length > 0 ? woTrendData.labels : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Work Order',
                data: woTrendData.values.length > 0 ? woTrendData.values : [0, 0, 0, 0, 0, 0],
                borderColor: '#1B3C88',
                backgroundColor: 'rgba(27, 60, 136, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
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
}

// Status WO Chart (Doughnut)
const statusWOCtx = document.getElementById('statusWOChart');
if (statusWOCtx) {
    const statusWOChart = new Chart(statusWOCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusWOData),
            datasets: [{
                data: Object.values(statusWOData),
                backgroundColor: [
                    '#1B3C88',
                    '#28a745',
                    '#ffc107',
                    '#17a2b8',
                    '#6c757d',
                    '#dc3545'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}
</script>

<!-- Page Specific JS File -->
<script src="{{ asset('assets/js/page/index-0.js') }}"></script>
@endsection


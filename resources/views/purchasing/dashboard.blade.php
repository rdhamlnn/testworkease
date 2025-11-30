@extends('purchasing.master')

@section('title', 'Dashboard Purchasing')

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
                <div class="card-icon bg-primary"><i class="fas fa-shopping-cart"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Permintaan</h4>
                    </div>
                    <div class="card-body">{{ $totalPermintaan ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger"><i class="fas fa-clock"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Menunggu Approval</h4>
                    </div>
                    <div class="card-body">{{ $menungguApproval ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning"><i class="fas fa-cash-register"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Dibeli</h4>
                    </div>
                    <div class="card-body">{{ $dibeli ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-truck"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Dikirim</h4>
                    </div>
                    <div class="card-body">{{ $dikirim ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Trend Permintaan Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Trend Permintaan per Bulan</h4>
                </div>
                <div class="card-body">
                    <canvas id="permintaanTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Pembelian Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Status Pembelian</h4>
                </div>
                <div class="card-body">
                    <canvas id="statusPembelianChart" height="200"></canvas>
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
                                    <th>No. Permintaan</th>
                                    <th>No. Work Order</th>
                                    <th>Total Estimasi</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities ?? [] as $index => $activity)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $activity->no_permintaan_barang ?? '-' }}</td>
                                    <td>{{ $activity->suratPengajuan->no_surat_pengajuan ?? '-' }}</td>
                                    <td>Rp {{ number_format($activity->total_estimasi_harga ?? 0, 0, ',', '') }}</td>
                                    <td>
                                        @if($activity->status == 'Disetujui Atasan')
                                            <span class="badge badge-success">{{ $activity->status }}</span>
                                        @elseif($activity->status == 'Ditolak Atasan')
                                            <span class="badge badge-danger">{{ $activity->status }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ $activity->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($activity->tanggal_permintaan)->format('d/m/Y') }}</td>
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
const monthlyPermintaanTrendData = @json($monthlyPermintaanTrend ?? []);
const statusPembelianData = @json($statusPembelian ?? []);

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

// Trend Permintaan Chart (Line)
const permintaanTrendCtx = document.getElementById('permintaanTrendChart');
if (permintaanTrendCtx) {
    const permintaanTrendData = formatMonthData(monthlyPermintaanTrendData);
    const permintaanTrendChart = new Chart(permintaanTrendCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: permintaanTrendData.labels.length > 0 ? permintaanTrendData.labels : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Permintaan',
                data: permintaanTrendData.values.length > 0 ? permintaanTrendData.values : [0, 0, 0, 0, 0, 0],
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

// Status Pembelian Chart (Doughnut)
const statusPembelianCtx = document.getElementById('statusPembelianChart');
if (statusPembelianCtx) {
    const statusPembelianChart = new Chart(statusPembelianCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusPembelianData),
            datasets: [{
                data: Object.values(statusPembelianData),
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


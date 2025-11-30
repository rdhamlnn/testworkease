@extends('atasan.master')

@section('title', 'Dashboard Atasan')

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
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary"><i class="fas fa-clock"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Menunggu Approval</h4>
                    </div>
                    <div class="card-body">{{ $menungguApproval ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Disetujui</h4>
                    </div>
                    <div class="card-body">{{ $disetujui ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger"><i class="fas fa-times-circle"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Ditolak</h4>
                    </div>
                    <div class="card-body">{{ $ditolak ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Trend Approval Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Trend Approval per Bulan</h4>
                </div>
                <div class="card-body">
                    <canvas id="approvalTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Persentase Approval Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Persentase Approval/Reject</h4>
                </div>
                <div class="card-body">
                    <canvas id="approvalPercentageChart" height="200"></canvas>
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
const monthlyApprovalTrendData = @json($monthlyApprovalTrend ?? []);
const approvalPercentageData = @json($approvalPercentage ?? []);

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

// Trend Approval Chart (Line)
const approvalTrendCtx = document.getElementById('approvalTrendChart');
if (approvalTrendCtx) {
    const approvalTrendData = formatMonthData(monthlyApprovalTrendData);
    const approvalTrendChart = new Chart(approvalTrendCtx.getContext('2d'), {
        type: 'line',
        data: {
            labels: approvalTrendData.labels.length > 0 ? approvalTrendData.labels : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Approval',
                data: approvalTrendData.values.length > 0 ? approvalTrendData.values : [0, 0, 0, 0, 0, 0],
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

// Persentase Approval Chart (Doughnut)
const approvalPercentageCtx = document.getElementById('approvalPercentageChart');
if (approvalPercentageCtx) {
    const total = approvalPercentageData['Disetujui'] + approvalPercentageData['Ditolak'];
    const disetujuiPercent = total > 0 ? (approvalPercentageData['Disetujui'] / total * 100).toFixed(1) : 0;
    const ditolakPercent = total > 0 ? (approvalPercentageData['Ditolak'] / total * 100).toFixed(1) : 0;
    
    const approvalPercentageChart = new Chart(approvalPercentageCtx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Disetujui', 'Ditolak'],
            datasets: [{
                data: [approvalPercentageData['Disetujui'] || 0, approvalPercentageData['Ditolak'] || 0],
                backgroundColor: ['#28a745', '#dc3545'],
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' (' + (label === 'Disetujui: ' ? disetujuiPercent : ditolakPercent) + '%)';
                            return label;
                        }
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


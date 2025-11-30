@extends('admin.master')

@section('title', 'Dashboard Admin')

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
                        <h4>Work Order</h4>
                    </div>
                    <div class="card-body">{{ $totalWorkOrder ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger"><i class="far fa-newspaper"></i></div>
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
                <div class="card-icon bg-warning"><i class="far fa-file"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Laporan Pemakaian Barang Mekanik</h4>
                    </div>
                    <div class="card-body">{{ $totalLaporanBarang ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success"><i class="fas fa-circle"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total User</h4>
                    </div>
                    <div class="card-body">{{ $totalAkun ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Work Order Status Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Status Work Order</h4>
                </div>
                <div class="card-body">
                    <canvas id="workOrderStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- User Distribution Chart -->
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Distribusi User per Divisi</h4>
                </div>
                <div class="card-body">
                    <canvas id="userDistributionChart" height="200"></canvas>
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
                    <div class="activities">
                        @forelse($recentActivities as $activity)
                        <div class="activity">
                            <div class="activity-icon bg-primary text-white shadow-primary">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="activity-detail">
                                <div class="mb-2">
                                    <span class="text-job">Work Order {{ ucfirst($activity->status) }}</span>
                                    <span class="bullet"></span>
                                    <span class="text-job">{{ \Carbon\Carbon::parse($activity->tanggal)->diffForHumans() }}</span>
                                </div>
                                <p>Work Order #{{ $activity->no_work_order ?? 'WO-' . date('Y', strtotime($activity->tanggal)) . '-' . str_pad($activity->id_surat_pengajuan, 3, '0', STR_PAD_LEFT) }} - {{ $activity->nama_lengkap }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="activity">
                            <div class="activity-detail">
                                <p class="text-muted text-center">Tidak ada aktivitas terbaru</p>
                            </div>
                        </div>
                        @endforelse
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
const workOrderStatusData = @json($workOrderStatus);
const userDistributionData = @json($userDistribution);

// Work Order Status Chart (Doughnut)
const workOrderStatusCtx = document.getElementById('workOrderStatusChart').getContext('2d');
const workOrderStatusChart = new Chart(workOrderStatusCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(workOrderStatusData),
        datasets: [{
            data: Object.values(workOrderStatusData),
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#17a2b8',
                '#6c757d'
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

// User Distribution Chart (Bar)
const userDistributionCtx = document.getElementById('userDistributionChart').getContext('2d');
const userDistributionChart = new Chart(userDistributionCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(userDistributionData),
        datasets: [{
            label: 'Jumlah User',
            data: Object.values(userDistributionData),
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
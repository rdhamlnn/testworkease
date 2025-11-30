@extends('admin.master')

@section('content')
<div class="container-fluid">
    <!-- Dashboard Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">User Dashboard</h4>
            </div>
        </div>
    </div>

    <!-- Welcome Message -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Selamat Datang, {{ $user->name }}!</h5>
                    <p class="card-text">Email: {{ $user->email }}</p>
                    <p class="card-text">Role: {{ $user->role ?? 'User' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics Cards -->
    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-blue me-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number">1</h3>
                            <p class="stats-label">Profile</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Settings Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-green me-3">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number">5</h3>
                            <p class="stats-label">Settings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Messages Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-yellow me-3">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number">12</h3>
                            <p class="stats-label">Messages</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notifications Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon icon-red me-3">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number">3</h3>
                            <p class="stats-label">Notifications</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #3b82f6;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .stats-icon i {
        font-size: 1.5rem;
        color: white;
    }
    
    .stats-number {
        color: #1f2937;
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
    }
    
    .stats-label {
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .icon-red {
        background-color: #dc2626;
    }
    
    .icon-yellow {
        background-color: #d97706;
    }
    
    .icon-green {
        background-color: #059669;
    }
    
    .icon-blue {
        background-color: #2563eb;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .stats-card {
            padding: 20px;
            border-radius: 12px;
        }
        
        .stats-number {
            font-size: 2rem;
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
        }
        
        .stats-icon i {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 576px) {
        .stats-number {
            font-size: 1.8rem;
        }
        
        .stats-label {
            font-size: 0.8rem;
        }
    }
</style>
@endpush

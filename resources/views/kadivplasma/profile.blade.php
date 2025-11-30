@extends('kadivplasma.master')

@section('title', 'Profile')

@section('styles')
<style>
    .btn-update {
        background-color: #1B3C88 !important;
        border-color: #1B3C88 !important;
        color: white !important;
        position: relative !important;
        z-index: 1 !important;
        transition: all 0.2s ease !important;
    }

    .btn-update:hover {
        background-color: #16316F !important;
        border-color: #16316F !important;
        color: white !important;
        opacity: 1 !important;
        transform: none !important;
    }

    .btn-change-password {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: white !important;
        position: relative !important;
        z-index: 1 !important;
        transition: all 0.2s ease !important;
    }

    .btn-change-password:hover {
        background-color: #5a6268 !important;
        border-color: #545b62 !important;
        color: white !important;
        opacity: 1 !important;
        transform: none !important;
    }

    .btn-back {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        color: white !important;
        position: relative !important;
        z-index: 1 !important;
        transition: all 0.2s ease !important;
    }

    .btn-back:hover {
        background-color: #218838 !important;
        border-color: #1e7e34 !important;
        color: white !important;
        opacity: 1 !important;
        transform: none !important;
    }

    .btn-outline-secondary {
        position: relative !important;
        z-index: 1 !important;
        pointer-events: auto !important;
    }

    .btn-outline-secondary:hover {
        opacity: 1 !important;
        transform: none !important;
    }

    /* Hide browser's native password reveal button */
    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-strong-password-auto-fill-button,
    input[type="password"]::-webkit-inner-spin-button,
    input[type="password"]::-webkit-outer-spin-button {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
        position: absolute !important;
        right: -9999px !important;
    }

    /* Hide password reveal button for Chrome/Edge */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* For Firefox */
    input[type="password"] {
        -moz-appearance: textfield;
    }

    /* Ensure input-group button is properly positioned */
    .input-group .btn-outline-secondary {
        z-index: 10 !important;
    }

    .profile-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .profile-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .profile-card .card-body form {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .profile-card .form-group {
        margin-bottom: 1.5rem;
    }

    .row {
        align-items: stretch;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Profile</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('kadivplasma.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Profile</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('kadivplasma.dashboard') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card profile-card">
                    <div class="card-header">
                        <h4>Informasi Profile</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('kadivplasma.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama_lengkap">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                               value="{{ $user->nama_lengkap ?? session('nama_lengkap') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="divisi">Divisi</label>
                                        <input type="text" class="form-control" id="divisi" name="divisi" 
                                               value="{{ $user->nama_divisi ?? 'Plasma' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_hp">No. HP</label>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                               value="{{ $user->no_hp ?? session('no_hp') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jabatan">Jabatan</label>
                                        <input type="text" class="form-control" id="jabatan" name="jabatan" 
                                               value="{{ $user->jabatan ?? session('jabatan', 'Kadiv Plasma') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required>{{ $user->alamat ?? session('alamat') }}</textarea>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-update">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card profile-card">
                    <div class="card-header">
                        <h4>Ubah Password</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('kadivplasma.profile.change-password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="form-group">
                                <label for="current_password">Password Lama</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye" id="current_password_icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="new_password">Password Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye" id="new_password_icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                            <i class="fas fa-eye" id="new_password_confirmation_icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="mt-4">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Pastikan password baru minimal 6 karakter.
                                    </small>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-change-password">
                                    <i class="fas fa-key"></i> Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + '_icon');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    $('#new_password_confirmation').on('keyup', function() {
        var password = $('#new_password').val();
        var confirmPassword = $(this).val();
        
        if (password !== confirmPassword) {
            $(this).addClass('is-invalid');
            $(this).removeClass('is-valid');
        } else {
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
        }
    });
</script>
@endsection


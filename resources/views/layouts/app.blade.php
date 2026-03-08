<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- CSS Files - Load in order: Framework → Icons → Custom -->
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <!-- Icon Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    @php
        $user = auth()->user();
        $isAdminLayout = $user && in_array($user->role, ['admin', 'staff', 'owner']);
    @endphp

    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    
    <!-- Client-facing styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/client/verification/verification.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/client/car/car.css') }}">
   
    <!-- Admin styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/user/user.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/cars/car.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/verification/verification.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin/dashboard.css') }}">

    <link rel="stylesheet" href="{{asset('assets/css/app.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/home.css')}}">
</head>
<body class="{{ $isAdminLayout ? 'admin-body' : '' }}">

    <!-- Navigation -->
    @include('layouts.navigation')

    <!-- Main Content -->
    <main class="{{ $isAdminLayout ? 'admin-main' : 'client-main' }}">
        <div class="page">
            {{ $slot }}
        </div>
        
    </main>

    <!-- JavaScript Files - Load in order: Libraries → Framework → Custom -->
    
    <!-- jQuery (load first if other scripts depend on it) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle (includes Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   

    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            if (sidebar && toggleBtn) {
                function toggleSidebar() {
                    const isOpen = sidebar.classList.contains('show');
                    sidebar.classList.toggle('show');
                    if (overlay) overlay.style.display = isOpen ? 'none' : 'block';
                }
                
                toggleBtn.addEventListener('click', toggleSidebar);
                if (overlay) overlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>
    

    @stack('scripts') 

    <script src="{{asset('assets/js/global.js')}}"></script>
    

</body>
</html>
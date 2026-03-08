@php
    $user = Auth::user();
    $isAdminLayout = $user && in_array($user->role, ['admin', 'staff', 'owner']);
@endphp

@if($isAdminLayout)
    @include('layouts.partials.admin-sidebar')
@else
    @include('layouts.partials.client-header')
@endif

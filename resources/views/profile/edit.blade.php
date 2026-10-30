@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola informasi profil dan pengaturan keamanan akun Anda.</p>
    </div>

    <div class="space-y-6">
        <div class="bg-white p-4 sm:p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="bg-white p-4 sm:p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="bg-white p-4 sm:p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection

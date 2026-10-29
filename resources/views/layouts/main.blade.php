@extends('layouts.app')

@section('content')
    @include('partials.navbar')

    <!-- Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 flex-1 w-full">
        @include('partials.alert')

        @yield('main-content')
    </main>

    @include('partials.footer')

    @include('components.confirm-modal')
    @include('partials.pwa-prompt')
@endsection

@push('scripts')
    <script>
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdownMenu');
            const button = document.getElementById('userMenuButton');
            if (dropdown && button && !button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
@endpush

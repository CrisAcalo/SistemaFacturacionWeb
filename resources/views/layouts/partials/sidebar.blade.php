<!-- Backdrop para móvil (ahora solo se muestra en pantallas pequeñas) -->
<div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-20 bg-black/50 lg:hidden" @click="sidebarOpen = false">
</div>

<!-- Sidebar -->
<aside
    class="fixed inset-y-0 left-0 z-30 flex-shrink-0 w-64 overflow-y-auto transition-transform duration-300 transform bg-sidebar-bg
           lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <!-- Logo -->
    <div class="flex items-center justify-center h-16 bg-sidebar-bg shadow-lg">
        <a class="text-lg font-bold text-white" href="{{ route('dashboard') }}" wire:navigate>
            <i class="text-2xl text-primary bi bi-heptagon-half"></i>
            <span class="ml-2">FacturaPro</span>
        </a>
    </div>

    <!-- Menú de Navegación -->
    <nav class="mt-8">
        {{-- CAMBIO: Usamos __() para la traducción --}}
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>{{ __('app.dashboard') }}</span>
        </x-sidebar-link>

        <div class="px-6 py-4">
            <hr class="border-gray-700">
        </div>

        @can('manage users')
            <x-sidebar-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                <i class="bi bi-people-fill"></i>
                <span>Clientes</span>
            </x-sidebar-link>
        @endcan

        @can('manage products')
            <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                <i class="bi bi-box-seam-fill"></i>
                <span>Productos</span>
            </x-sidebar-link>
        @endcan

        @can('manage invoices')
            <x-sidebar-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Facturación</span>
            </x-sidebar-link>
        @endcan
        @can('manage invoices')
            <x-sidebar-link :href="route('invoices.create')" :active="request()->routeIs('invoices.create')">
                <i class="bi bi-receipt-cutoff"></i>
                <span>Nueva Factura</span>
            </x-sidebar-link>
        @endcan

        @can('manage payments')
            <x-sidebar-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">
                <i class="bi bi-credit-card-fill"></i>
                <span>Gestión de Pagos</span>
            </x-sidebar-link>
        @endcan

        @can('view audits')
            <div class="px-6 py-4">
                <hr class="border-gray-700">
            </div>

            <x-sidebar-link :href="route('audits.index')" :active="request()->routeIs('audits.*')">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Auditoría</span>
            </x-sidebar-link>
        @endcan

        @can('manage tokens')
            <x-sidebar-link :href="route('tokens.index')" :active="request()->routeIs('tokens.*')">
                <i class="bi bi-key-fill"></i>
                <span>API Tokens</span>
            </x-sidebar-link>
        @endcan
    </nav>
</aside>

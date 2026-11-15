<x-app-layout>
    <x-toast />

    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight" style="color:#a3b18a;">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">Spins disponibles</h2>
                    </header>

                    <p class="mt-2 text-sm text-gray-600">
                        Tu as actuellement <strong>{{ $availableSpins }}</strong> spin(s) disponible(s).
                    </p>

                    @if($availableSpins > 0)
                        <a href="{{ route('spin.spin') }}" class="mt-3 inline-block text-sm text-indigo-600 hover:underline">
                            Aller tourner la roue →
                        </a>
                    @endif
                </section>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">Historique de mes commandes</h2>
                    </header>

                    <div class="mt-4 space-y-3">
                        @forelse($orders as $order)
                            <div class="flex justify-between items-center border-b pb-2 text-sm text-gray-700">
                                <span>Commande #{{ $order->id }} — {{ $order->nb_articles }} article(s)</span>
                                <span>{{ number_format($order->total, 0, ',', ' ') }} Ar — {{ $order->created_at->format('d/m/Y') }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Aucune commande pour le moment.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Antes de continuar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que acabamos de enviarte por correo? Si no recibiste el correo, con gusto te enviaremos otro. ') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                {{ __('Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste en la configuración de tu perfil.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf

                <div>
                    <x-button type="submit">
                        {{ __('Re-enviar email de verificacion.') }}
                    </x-button>
                </div>
            </form>

            <div>
                
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf

                    <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 ms-2">
                        {{ __('Cerrar sesion') }}
                    </button>
                </form>
            </div>
        </div>
    </x-authentication-card>

</x-guest-layout>
<div>
    <x-action-section>
        <x-slot name="title">
            {{ __('Autenticación de dos factores') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Agregue seguridad adicional a su cuenta utilizando la autenticación de dos factores.') }}
        </x-slot>

        <x-slot name="content">
            <h3 class="text-lg font-medium text-gray-100">
                @if ($this->enabled)
                    @if ($showingConfirmation)
                        {{ __('Termine de habilitar la autenticación de dos factores.') }}
                    @else
                        {{ __('Ha habilitado la autenticación de dos factores.') }}
                    @endif
                @else
                    {{ __('No ha habilitado la autenticación de dos factores.') }}
                @endif
            </h3>

            <div class="mt-3 max-w-xl text-sm text-gray-300">
                <p>
                    {{ __('Cuando la autenticación de dos factores está habilitada, se le solicitará un token aleatorio seguro durante la autenticación. Puede recuperar este token desde la aplicación Google Authenticator de su teléfono.') }}
                </p>
            </div>

            @if ($this->enabled)
                @if ($showingQrCode)
                    <div class="mt-4 max-w-xl text-sm text-gray-300">
                        <p class="font-semibold">
                            @if ($showingConfirmation)
                                {{ __('Para terminar de habilitar la autenticación de dos factores, escanee el siguiente código QR usando la aplicación de autenticación de google "Authenticator" de su teléfono o ingrese la clave de configuración y proporcione el código OTP generado.') }}
                            @else
                                {{ __('La autenticación de dos factores ahora está habilitada. Escanee el siguiente código QR usando la aplicación de autenticación de su teléfono o ingrese la clave de configuración.') }}
                            @endif
                        </p>
                    </div>

                    <div class="mt-4 p-2 inline-block bg-white">
                        {!! $this->user->twoFactorQrCodeSvg() !!}
                    </div>

                    <div class="mt-4 max-w-xl text-sm text-gray-300">
                        <p class="font-semibold">
                            {{ __('Clave de configuración') }}: {{ decrypt($this->user->two_factor_secret) }}
                        </p>
                    </div>

                    @if ($showingConfirmation)
                        <div class="mt-4">
                            <x-label for="code" value="{{ __('Código') }}" />

                            <x-input id="code" type="text" name="code" class="block mt-1 w-1/2" inputmode="numeric" autofocus autocomplete="one-time-code"
                                wire:model="code"
                                wire:keydown.enter="confirmTwoFactorAuthentication" />

                            <x-input-error for="code" class="mt-2" />
                        </div>
                    @endif
                @endif

                @if ($showingRecoveryCodes)
                    <div class="mt-4 max-w-xl text-sm text-gray-300">
                        <p class="font-semibold">
                            {{ __('Guarde estos códigos de recuperación en un administrador de contraseñas seguro. Se pueden utilizar para recuperar el acceso a su cuenta si pierde su dispositivo de autenticación de dos factores.') }}
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-800 text-gray-100 rounded-lg">
                        @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                            <div>{{ $code }}</div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('autoregistro-notificacion') }}" class="text-blue-500 hover:text-blue-700 font-medium">
                            {{ __('Finalizar Registro') }}
                        </a>
                    </div>
                @endif
            @endif

            <div class="mt-5">
                @if (! $this->enabled)
                    <x-confirms-password wire:then="enableTwoFactorAuthentication">
                        <x-button type="button" wire:loading.attr="disabled">
                            {{ __('Ver código QR') }}
                        </x-button>
                    </x-confirms-password>
                @else
                    @if ($showingRecoveryCodes)
                        <x-confirms-password wire:then="regenerateRecoveryCodes">
                            <x-secondary-button class="me-3">
                                {{ __('Regenerar códigos de recuperación') }}
                            </x-secondary-button>
                        </x-confirms-password>
                    @elseif ($showingConfirmation)
                        <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                            <x-button type="button" class="me-3" wire:loading.attr="disabled">
                                {{ __('Confirmar') }}
                            </x-button>
                        </x-confirms-password>
                    @else
                        <x-confirms-password wire:then="showRecoveryCodes">
                            <x-secondary-button class="me-3">
                                {{ __('Mostrar códigos de recuperación') }}
                            </x-secondary-button>
                        </x-confirms-password>
                    @endif
                @endif
            </div>
        </x-slot>
    </x-action-section>
</div>

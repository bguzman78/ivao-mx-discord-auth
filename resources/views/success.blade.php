
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Bienvenido a bordo | IVAO México</title>

    <link
        rel="stylesheet"
        href="{{ asset('css/ivao-auth-theme.css') }}"
    >
</head>

<body>
    @php
        $name = $verification['name'] ?? '';
        $vid = $verification['vid'] ?? '';
        $division = $verification['division'] ?? '';

        $divisionName = $division === 'MX'
            ? 'México (MX)'
            : ($division ?: 'No disponible');

        $roles = collect($verification['roles'] ?? [])
            ->map(fn ($id) => $roleLabels[(string) $id] ?? null)
            ->filter()
            ->values();
    @endphp

    <main class="mx-welcome">
        <div class="mx-welcome__container">

            <!-- Same logo used by IVAO Mexico login -->
            <img
                src="{{ asset('assets/imgs/mxivao_logo_white.webp') }}"
                alt="IVAO México Oficial"
                class="mx-welcome__logo"
            >

            <section class="mx-welcome__card">

                <div
                    class="mx-welcome__check"
                    aria-hidden="true"
                >
                    ✓
                </div>

                <p class="mx-welcome__eyebrow">
                    Verificación completada
                </p>

                <h1 class="mx-welcome__title">
                    ¡Bienvenido a bordo{{ $name ? ', ' . $name : '' }}!
                </h1>

                <p class="mx-welcome__description">
                    Tu cuenta de IVAO se ha vinculado
                    correctamente con Discord.

                    Ya puedes acceder al servidor oficial
                    de IVAO México.
                </p>

                <div class="mx-welcome__profile">

                    <div class="mx-welcome__detail">
                        <span class="mx-welcome__label">
                            IVAO VID
                        </span>
                        <strong>{{ $vid }}</strong>
                    </div>

                    <div class="mx-welcome__detail">
                        <span class="mx-welcome__label">
                            División
                        </span>
                        <strong>{{ $divisionName }}</strong>
                    </div>

                    <div class="mx-welcome__roles">
                        <span class="mx-welcome__roles-title">
                            Roles procesados
                        </span>

                        <div class="mx-welcome__role-list">
                            @forelse ($roles as $role)
                                <span
                                    @class([
                                        'mx-welcome__role',
                                        'mx-welcome__role--member' => $role === 'MX-Member',
                                        'mx-welcome__role--staff' => $role === 'MX-Staff',
                                        'mx-welcome__role--foreign' => $role === 'F-Member',
                                    ])
                                >
                                    {{ $role }}
                                </span>
                            @empty
                                <span class="mx-welcome__role">
                                    Sin roles para mostrar
                                </span>
                            @endforelse
                        </div>
                    </div>

                </div>

                @if (!empty($inviteUrl))
                    <a
                        href="{{ $inviteUrl }}"
                        class="mx-welcome__button"
                        rel="noopener noreferrer"
                    >
                        Entrar al Discord oficial ↗
                    </a>
                @endif

                <p class="mx-welcome__footer">
                    ¡Nos vemos en frecuencia! ✈️🇲🇽
                    <br>
                    IVAO México Oficial
                </p>

            </section>
        </div>
    </main>
</body>
</html>

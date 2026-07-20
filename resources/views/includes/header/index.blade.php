
<header class="header">
    <div class="header__inner">
        <nav>
            <a href="{{ route('home') }}">Главная</a>
            <a href="{{ route('about.index') }}">О нас</a>
            <a href="{{ route('qr.index') }}">QR</a>
        </nav>
        <div class="header__options">
            <button class="btn" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: { name: 'modal-form' } }));">
                обратная связь
            </button>
        </div>
    </div>
</header>

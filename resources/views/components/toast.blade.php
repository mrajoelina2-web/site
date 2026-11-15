@if(session('success') || session('error'))
    <div id="toast-notification" style="
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        padding: 14px 24px;
        border-radius: 8px;
        font-family: system-ui, sans-serif;
        font-weight: bold;
        color: white;
        background: {{ session('success') ? '#5c6b3f' : '#c0392b' }};
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        animation: slideDown 0.4s ease-out;
    ">
        {{ session('success') ?? session('error') }}
    </div>

    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translate(-50%, -20px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>

    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast-notification');
            if (toast) {
                toast.style.animation = 'fadeOut 0.5s ease-out forwards';
                setTimeout(() => toast.remove(), 500);
            }
        }, 3000);
    </script>
@endif
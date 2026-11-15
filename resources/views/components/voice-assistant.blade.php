<div id="voice-assistant" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999;">
    <button id="voice-btn" style="
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #5c6b3f;
        border: none;
        color: white;
        font-size: 22px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    ">🎤</button>
    <p id="voice-feedback" style="
        position: absolute;
        bottom: 66px;
        right: 0;
        background: #1f2318;
        color: #eae7dc;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        white-space: nowrap;
        display: none;
        font-family: system-ui, sans-serif;
    "></p>
</div>

<script>
(function () {
    const btn = document.getElementById('voice-btn');
    const feedback = document.getElementById('voice-feedback');

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        btn.style.display = 'none'; // navigateur non compatible, on cache le bouton
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    recognition.continuous = false;
    recognition.interimResults = false;

    // Commandes reconnues -> URL correspondante (adapte les routes à ton site)
    const commandes = [
        { motsCles: ['catalogue', 'boutique', 'produits', 'articles'], url: "{{ route('products.index') }}" },
        { motsCles: ['panier'], url: "{{ route('cart.index') }}" },
        { motsCles: ['spin', 'roue', 'tourner'], url: "{{ route('spin.spin') }}" },
        { motsCles: ['profil', 'compte', 'mes commandes'], url: "{{ route('profile.edit') }}" },
    ];

    function showFeedback(message, duration = 2500) {
        feedback.textContent = message;
        feedback.style.display = 'block';
        setTimeout(() => { feedback.style.display = 'none'; }, duration);
    }

    btn.addEventListener('click', () => {
        recognition.start();
        showFeedback('🎧 Je t\'écoute...', 4000);
        btn.style.background = '#e94560';
    });

    recognition.addEventListener('result', (event) => {
        const transcript = event.results[0][0].transcript.toLowerCase();
        showFeedback('"' + transcript + '"');

        const commandeTrouvee = commandes.find(c =>
            c.motsCles.some(mot => transcript.includes(mot))
        );

        if (commandeTrouvee) {
            setTimeout(() => {
                window.location.href = commandeTrouvee.url;
            }, 800);
        } else {
            showFeedback('Commande non reconnue : "' + transcript + '"');
        }
    });

    recognition.addEventListener('end', () => {
        btn.style.background = '#5c6b3f';
    });

    recognition.addEventListener('error', (event) => {
        showFeedback('Erreur micro : ' + event.error);
        btn.style.background = '#5c6b3f';
    });
})();
</script>
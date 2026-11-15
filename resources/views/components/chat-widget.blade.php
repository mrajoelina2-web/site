<style>
    #cw-toggle {
        position: fixed; bottom: 24px; left: 24px; z-index: 9999;
        width: 56px; height: 56px; border-radius: 50%; border: none;
        background: #5c6b3f; color: white; font-size: 24px; cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    #cw-panel {
        position: fixed; bottom: 92px; left: 24px; z-index: 9999;
        width: 340px; max-width: calc(100vw - 32px);
        height: 460px; max-height: calc(100vh - 120px);
        display: none; flex-direction: column;
        background: rgba(31, 35, 24, 0.97); border: 1px solid #565c4b;
        border-radius: 16px; box-shadow: 0 12px 32px rgba(0,0,0,0.5);
        overflow: hidden; font-family: system-ui, sans-serif; color: #eae7dc;
    }
    #cw-panel.open { display: flex; }
    .cw-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 14px; background: #3a3f33; font-weight: bold; color: #a3b18a;
    }
    .cw-head button { background: none; border: none; color: #eae7dc; cursor: pointer; font-size: 16px; margin-left: 8px; }
    #cw-messages { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
    .cw-msg { max-width: 85%; padding: 8px 12px; border-radius: 12px; font-size: 14px; line-height: 1.4; white-space: pre-line; }
    .cw-bot { background: #3a3f33; align-self: flex-start; }
    .cw-user { background: #5c6b3f; align-self: flex-end; }
    .cw-link { display: inline-block; margin-top: 6px; color: #a3b18a; font-weight: bold; }
    #cw-chips { display: flex; flex-wrap: wrap; gap: 6px; padding: 0 12px 8px; }
    #cw-chips button {
        background: none; border: 1px solid #a3b18a; color: #a3b18a;
        border-radius: 14px; padding: 4px 10px; font-size: 12px; cursor: pointer;
    }
    .cw-input { display: flex; gap: 6px; padding: 10px; border-top: 1px solid #565c4b; }
    .cw-input input {
        flex: 1; min-width: 0; padding: 8px 10px; border-radius: 8px;
        border: 1px solid #565c4b; background: #2b2f26; color: #eae7dc; font-size: 14px;
    }
    .cw-input button { border: none; border-radius: 8px; padding: 0 12px; cursor: pointer; background: #5c6b3f; color: white; font-size: 16px; }
    #cw-mic.cw-rec { background: #e94560; }
</style>

<button id="cw-toggle" aria-label="Ouvrir l'assistant">💬</button>

<div id="cw-panel">
    <div class="cw-head">
        <span>🎖️ Assistant Équip'Style</span>
        <span>
            <button id="cw-clear" title="Effacer la discussion">🗑</button>
            <button id="cw-close" title="Fermer">✕</button>
        </span>
    </div>
    <div id="cw-messages"></div>
    <div id="cw-chips"></div>
    <div class="cw-input">
        
        <input id="cw-text" type="text" maxlength="300" placeholder="Écris ta question..." autocomplete="off">
        <button id="cw-send" type="button">➤</button>
    </div>
</div>

<script>
(function () {
    const KEY = 'equip_chat_v1';
    const DEFAULT_CHIPS = ['Voir les catégories', 'Comment marche la roue ?', 'Comment acheter ?', 'Mes commandes'];

    const panel = document.getElementById('cw-panel');
    const toggle = document.getElementById('cw-toggle');
    const messages = document.getElementById('cw-messages');
    const chips = document.getElementById('cw-chips');
    const input = document.getElementById('cw-text');
    const micBtn = document.getElementById('cw-mic');

    let history = [];
    try { history = JSON.parse(sessionStorage.getItem(KEY) || '[]'); } catch (e) { history = []; }

    function save() {
        try { sessionStorage.setItem(KEY, JSON.stringify(history.slice(-40))); } catch (e) {}
    }

    function addMessage(from, text, link, store) {
        const div = document.createElement('div');
        div.className = 'cw-msg cw-' + from;
        div.textContent = text;

        if (link && link.url && /^(https?:\/\/|\/)/.test(link.url)) {
            div.appendChild(document.createElement('br'));
            const a = document.createElement('a');
            a.href = link.url;
            a.textContent = link.label;
            a.className = 'cw-link';
            div.appendChild(a);
        }

        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;

        if (store !== false) {
            history.push({ from: from, text: text, link: link || null });
            save();
        }
        return div;
    }

    function showChips(list) {
        chips.innerHTML = '';
        (list && list.length ? list : DEFAULT_CHIPS).forEach(function (label) {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = label;
            b.addEventListener('click', function () { send(label); });
            chips.appendChild(b);
        });
    }

    function welcome() {
        addMessage('bot', "Bonjour ! Je suis l'assistant Équip'Style. Pose-moi une question ou choisis une suggestion.");
        showChips(DEFAULT_CHIPS);
    }

    function restore() {
        messages.innerHTML = '';
        if (history.length === 0) { welcome(); return; }
        history.forEach(function (m) { addMessage(m.from, m.text, m.link, false); });
        showChips(DEFAULT_CHIPS);
    }

    async function send(text) {
        text = (text || '').trim();
        if (!text) return;

        addMessage('user', text);
        input.value = '';
        chips.innerHTML = '';

        const typing = addMessage('bot', '...', null, false);

        try {
            const res = await fetch("{{ route('chat.reply', [], false) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ message: text }),
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            typing.remove();
            addMessage('bot', data.reply, data.link);
            showChips(data.suggestions);
        } catch (e) {
            typing.remove();
            addMessage('bot', "Désolé, une erreur est survenue. Réessaie dans un instant.");
            showChips(DEFAULT_CHIPS);
        }
    }

    toggle.addEventListener('click', function () {
        panel.classList.toggle('open');
        if (panel.classList.contains('open')) input.focus();
    });
    document.getElementById('cw-close').addEventListener('click', function () { panel.classList.remove('open'); });
    document.getElementById('cw-clear').addEventListener('click', function () {
        history = [];
        save();
        restore();
    });
    document.getElementById('cw-send').addEventListener('click', function () { send(input.value); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); send(input.value); }
    });

   
   

    restore();
})();
</script>
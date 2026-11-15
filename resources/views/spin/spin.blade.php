<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Équip'Style — Spin</title>
<style>
    body { font-family: system-ui, sans-serif; color: #eae7dc; margin: 0; }
    .page { padding: 24px 12px 40px; }

    .spin-card {
        background: rgba(58, 63, 51, 0.85);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 24px;
        padding: 28px 20px;
        box-shadow: 0 12px 32px rgba(0,0,0,0.4);
        max-width: 400px;
        margin: 0 auto;
        text-align: center;
        box-sizing: border-box;
    }

    .back-link { color: #a3b18a; text-decoration: none; font-size: 14px; }
    h1 { color: #a3b18a; font-size: 24px; margin: 12px 0; }

    p.spin-count {
        display: inline-block;
        background: #5c6b3f;
        color: white;
        padding: 6px 18px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 14px;
        margin: 0 0 10px 0;
    }

    .wheel-pointer {
        width: 0;
        height: 0;
        border-left: 16px solid transparent;
        border-right: 16px solid transparent;
        border-top: 24px solid #eae7dc;
        margin: 0 auto -6px auto;
        position: relative;
        z-index: 5;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.4));
    }

    .wheel {
        --wheel: min(300px, 68vw);
        width: var(--wheel);
        height: var(--wheel);
        border-radius: 50%;
        margin: 16px auto;
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
        background: conic-gradient(
            #4a4f3e 0deg 60deg,
            #6b4f2c 60deg 120deg,
            #5c6b3f 120deg 180deg,
            #8a8a63 180deg 240deg,
            #3a3f33 240deg 300deg,
            #2f332a 300deg 360deg
        );
        border: 6px solid #eae7dc;
        box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        transition: transform 4s cubic-bezier(.17,.67,.12,.99);
    }

    .wheel-icon {
        position: absolute;
        width: calc(var(--wheel) * 0.194);
        top: 50%;
        left: 50%;
        transform:
            rotate(var(--angle))
            translate(calc(var(--wheel) * 0.306))
            rotate(calc(-1 * var(--angle)));
        margin-top: calc(var(--wheel) * -0.056);
        margin-left: calc(var(--wheel) * -0.097);
    }

    button#spinBtn {
        background: linear-gradient(135deg, #7a8c54, #5c6b3f);
        border: none;
        color: white;
        padding: 14px 42px;
        font-size: 17px;
        font-weight: bold;
        border-radius: 30px;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(92, 107, 63, 0.5);
    }
    button#spinBtn:disabled { background: #555; box-shadow: none; cursor: not-allowed; }

    #result { margin-top: 18px; font-size: 15px; font-weight: 600; line-height: 1.5; }
</style>
</head>
<body>

<x-video-bg />
<x-nav />
<x-toast />

<div class="page">
    <div class="spin-card">
        <a href="{{ route('cart.index') }}" class="back-link">← Retour au panier pour valider l'achat</a>

        <h1>🎖️ Ta roue de récompenses</h1>
        <p class="spin-count">Spins disponibles : <strong id="spin-count">{{ $spin->available_spins }}</strong></p>

        <div class="wheel-pointer"></div>
        <div class="wheel" id="wheel">
            <img src="{{ asset('image/rewards/coupon_oups.svg') }}" class="wheel-icon" style="--angle: -60deg" alt="">
            <img src="{{ asset('image/rewards/coupon10.svg') }}" class="wheel-icon" style="--angle: 0deg" alt="">
            <img src="{{ asset('image/rewards/coupon25.svg') }}" class="wheel-icon" style="--angle: 60deg" alt="">
            <img src="{{ asset('image/rewards/coupon_extra.svg') }}" class="wheel-icon" style="--angle: 120deg" alt="">
            <img src="{{ asset('image/rewards/coupon50.svg') }}" class="wheel-icon" style="--angle: 180deg" alt="">
            <img src="{{ asset('image/rewards/coupon_free.svg') }}" class="wheel-icon" style="--angle: 240deg" alt="">
        </div>

        <button id="spinBtn" {{ $spin->available_spins <= 0 ? 'disabled' : '' }}>Tourner</button>
        <p id="result"></p>
    </div>
</div>

<script>
    const wheel = document.getElementById('wheel');
    const spinBtn = document.getElementById('spinBtn');
    const result = document.getElementById('result');
    const spinCountEl = document.getElementById('spin-count');
    const cartUrl = "{{ route('cart.index') }}";

    const NB_SECTEURS = 6;
    const ANGLE_SECTEUR = 360 / NB_SECTEURS;
    let currentRotation = 0;

    function redirectToCart(delayMs) {
        setTimeout(() => { window.location.href = cartUrl; }, delayMs);
    }

    spinBtn.addEventListener('click', async () => {
        spinBtn.disabled = true;
        result.textContent = '';

        try {
            const res = await fetch("{{ route('spin.play') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            });
            const data = await res.json();

            if (data.error) {
                result.textContent = 'Erreur : ' + data.error;
                spinBtn.disabled = false;
                return;
            }

            // Angle exact pour que la flèche s'arrête sur le bon secteur
            const sectorCenter = data.reward.index * ANGLE_SECTEUR + (ANGLE_SECTEUR / 2);
            const targetAngle = (360 - sectorCenter) % 360;
            const currentVisualAngle = currentRotation % 360;
            const deltaToAlign = (targetAngle - currentVisualAngle + 360) % 360;

            currentRotation += (5 * 360) + deltaToAlign;
            wheel.style.transform = `rotate(${currentRotation}deg)`;

            setTimeout(() => {
                spinCountEl.textContent = data.reward.remaining_spins;
                const noMoreSpins = data.reward.remaining_spins <= 0;

                if (data.reward.type === 'coupon') {
                    result.innerHTML = `
                        🎁 Tu as gagné : <strong>${data.reward.label}</strong> (code : ${data.reward.code})<br>
                        <p style="margin:10px 0;">Ce coupon a été appliqué automatiquement à ton panier. Veux-tu l'utiliser maintenant ?</p>
                        <button id="use-coupon-now" style="background:#5c6b3f; color:white; border:none; padding:8px 16px; border-radius:6px; cursor:pointer; margin-right:8px;">Oui, l'utiliser</button>
                        <button id="keep-coupon-later" style="background:none; border:1px solid #a3b18a; color:#a3b18a; padding:8px 16px; border-radius:6px; cursor:pointer;">Non, garder pour plus tard</button>
                    `;

                    document.getElementById('use-coupon-now').addEventListener('click', () => {
                        window.location.href = cartUrl;
                    });

                    document.getElementById('keep-coupon-later').addEventListener('click', async () => {
                        await fetch("{{ route('cart.removeCoupon') }}", {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        });
                        result.innerHTML = '✅ Coupon conservé — code : <strong>' + data.reward.code + '</strong>.<br><small>Retour au panier...</small>';
                        redirectToCart(2500);
                    });

                } else if (data.reward.type === 'extra_spin') {
                    result.innerHTML = '🔄 <strong>Tirage supplémentaire !</strong> Retente ta chance, ce spin ne t\'a rien coûté.';
                    spinBtn.disabled = false;

                } else if (data.reward.type === 'none') {
                    result.innerHTML = '😅 ' + data.reward.label + ' Retente ta chance la prochaine fois.';

                    if (noMoreSpins) {
                        result.innerHTML += '<br><small>Retour au panier...</small>';
                        redirectToCart(4000);
                    } else {
                        spinBtn.disabled = false;
                    }

                } else {
                    result.innerHTML = '🎁 Tu as gagné : ' + data.reward.label;

                    if (noMoreSpins) {
                        result.innerHTML += '<br><small>Retour au panier...</small>';
                        redirectToCart(4000);
                    } else {
                        spinBtn.disabled = false;
                    }
                }
            }, 4000);
        } catch (err) {
            result.textContent = 'Erreur réseau.';
            spinBtn.disabled = false;
        }
    });
</script>

</body>
</html>
<style>
    /* Cache l'icône native d'Edge pour éviter d'avoir deux yeux */
    input[type="password"]::-ms-reveal { display: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        // Enveloppe le champ dans un conteneur pour placer le bouton dessus
        var wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.width = '100%';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        input.style.width = '100%';
        input.style.paddingRight = '44px';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Afficher ou masquer le mot de passe');
        btn.textContent = '👁️';
        btn.style.cssText = 'position:absolute; top:50%; right:10px; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:18px; line-height:1; padding:4px;';
        wrapper.appendChild(btn);

        btn.addEventListener('click', function () {
            var visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            btn.textContent = visible ? '👁️' : '🙈';
        });
    });
});
</script>
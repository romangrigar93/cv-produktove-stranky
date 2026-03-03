<?php
/**
 * Godlike – Newsletter popup (Ecomail)
 * Vložit do functions.php tématu nebo child-tématu:
 * require_once get_stylesheet_directory() . '/godlike-popup.php';
 */

add_action('wp_footer', 'godlike_newsletter_popup');

function godlike_newsletter_popup() {
    // Nezobrazovat přihlášeným uživatelům (zákazníci, admini, editoři...)
    if ( is_user_logged_in() ) {
        return;
    }
?>
<div id="gl-popup-overlay" style="display:none;">
    <div id="gl-popup">
        <button id="gl-popup-close" aria-label="Zavřít">&times;</button>
        <div class="gl-popup-image">
            <img src="https://www.godlike.gg/wp-content/uploads/2024/04/godlike-apple-product-cover.jpg" alt="Godlike">
        </div>
        <div id="gl-popup-content">
            <p class="gl-popup-label">Vstup do galaxie maximálního soustředění.</p>
            <h2 class="gl-popup-title">Nastartuj mozek na plný výkon<br>a miř až ke hvězdám.</h2>
            <p class="gl-popup-desc">Přihlas se k odběru a získej</p>
            <div class="gl-popup-discount">100 Kč slevu</div>
            <p class="gl-popup-desc">na tvůj první nákup</p>
            <form id="gl-popup-form" method="post" action="https://godlike.ecomailapp.cz/public/subscribe/15/864b4f4872272bdd94aadbc81dfd8b18">
                <input type="email" name="email" placeholder="Tvůj e-mail" required>
                <button type="submit">Chci slevu</button>
            </form>
            <p id="gl-popup-msg" style="display:none;"></p>
        </div>
    </div>
</div>

<style>
    #gl-popup-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.7);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    #gl-popup-overlay.gl-visible {
        opacity: 1;
    }
    #gl-popup {
        position: relative;
        display: flex;
        overflow: hidden;
        background: linear-gradient(170deg, #121a2b 0%, #0b1222 100%);
        border: 1px solid rgba(216,182,79,0.2);
        border-radius: 18px;
        max-width: 720px;
        width: 100%;
        box-shadow:
            0 24px 80px rgba(0,0,0,0.6),
            0 0 80px rgba(216,182,79,0.06);
        transform: translateY(30px) scale(0.96);
        transition: transform 0.4s ease;
    }
    #gl-popup-overlay.gl-visible #gl-popup {
        transform: translateY(0) scale(1);
    }
    .gl-popup-image {
        flex: 0 0 280px;
        position: relative;
        overflow: hidden;
    }
    .gl-popup-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .gl-popup-image::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(90deg, transparent 60%, #0b1222 100%);
        pointer-events: none;
    }
    #gl-popup-content {
        flex: 1;
        padding: 44px 40px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    #gl-popup-close {
        position: absolute;
        top: 12px; right: 16px;
        background: none;
        border: none;
        color: rgba(255,255,255,0.5);
        font-size: 26px;
        cursor: pointer;
        line-height: 1;
        padding: 4px 8px;
        border-radius: 8px;
        transition: color 0.2s, background 0.2s;
        z-index: 2;
    }
    #gl-popup-close:hover {
        color: #ffffff;
    }
    .gl-popup-label {
        font-family: 'Poppins', sans-serif;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 2.5px;
        color: #d8b64f;
        margin: 0 0 14px;
    }
    .gl-popup-title {
        font-family: 'Raleway', sans-serif;
        font-weight: 900;
        font-size: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #ffffff;
        line-height: 1.4;
        margin: 0 0 18px;
    }
    .gl-popup-desc {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        color: rgba(255,255,255,0.65);
        margin: 0;
        line-height: 1.4;
    }
    .gl-popup-discount {
        font-family: 'Raleway', sans-serif;
        font-weight: 900;
        font-size: 46px;
        background: linear-gradient(135deg, #f5e6a3 0%, #d8b64f 50%, #c9a23e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 6px 0;
        line-height: 1.15;
    }
    #gl-popup-form {
        margin-top: 22px;
        display: flex;
        gap: 10px;
    }
    #gl-popup-form input[type="email"] {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        padding: 13px 16px;
        border-radius: 10px;
        border: 1px solid rgba(216,182,79,0.25);
        background: rgba(255,255,255,0.06);
        color: #ffffff;
        outline: none;
        transition: border-color 0.2s;
        flex: 1;
        min-width: 0;
        box-sizing: border-box;
    }
    #gl-popup-form input[type="email"]::placeholder {
        color: rgba(255,255,255,0.35);
    }
    #gl-popup-form input[type="email"]:focus {
        border-color: #d8b64f;
    }
    #gl-popup-form button {
        font-family: 'Raleway', sans-serif;
        font-weight: 800;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        padding: 13px 22px;
        border-radius: 10px;
        border: none;
        background: linear-gradient(135deg, #59e391 0%, #3cc974 100%);
        color: #0b1222;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        white-space: nowrap;
        box-sizing: border-box;
    }
    #gl-popup-form button:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 24px rgba(89,227,145,0.3);
    }
    #gl-popup-msg {
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        margin-top: 14px;
        color: #59e391;
    }
    #gl-popup-msg.gl-error {
        color: #ff6b6b;
    }

    @media (max-width: 620px) {
        #gl-popup {
            flex-direction: column;
            max-width: 400px;
        }
        .gl-popup-image {
            flex: 0 0 auto;
            height: 180px;
        }
        .gl-popup-image::after {
            background: linear-gradient(180deg, transparent 40%, #0b1222 100%);
        }
        #gl-popup-content {
            padding: 24px 28px 32px;
        }
        .gl-popup-title {
            font-size: 17px;
        }
        .gl-popup-discount {
            font-size: 36px;
        }
        .gl-popup-label {
            font-size: 10px;
        }
        #gl-popup-form {
            flex-direction: column;
        }
        #gl-popup-close {
            font-size: 32px;
            padding: 4px 10px;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8);
        }
        #gl-popup-close:hover {
            background: rgba(255,255,255,0.18);
        }
    }
</style>

<script>
(function(){
    var KEY_SUB = 'gl_popup_subscribed';      // odeslal email → nikdy nezobrazovat
    var KEY_DISMISS = 'gl_popup_dismissed';  // odkřížkl → zobrazit znovu za 1 den
    var DELAY_MS = 8000;
    var DAY_MS = 86400000;

    // Odeslal email → konec navždy
    // if (localStorage.getItem(KEY_SUB)) return; // DEBUG: odkomentuj pro produkci

    // Odkřížkl → zobrazit znovu až po 1 dni
    // var dismissed = localStorage.getItem(KEY_DISMISS); // DEBUG: odkomentuj pro produkci
    // if (dismissed && (Date.now() - parseInt(dismissed, 10)) < DAY_MS) return; // DEBUG: odkomentuj pro produkci

    var overlay = document.getElementById('gl-popup-overlay');
    var closeBtn = document.getElementById('gl-popup-close');
    var form = document.getElementById('gl-popup-form');
    var msg = document.getElementById('gl-popup-msg');

    function showPopup() {
        overlay.style.display = 'flex';
        requestAnimationFrame(function(){
            requestAnimationFrame(function(){
                overlay.classList.add('gl-visible');
            });
        });
    }

    function hidePopup() {
        overlay.classList.remove('gl-visible');
        setTimeout(function(){ overlay.style.display = 'none'; }, 400);
    }

    function dismissPopup() {
        localStorage.setItem(KEY_DISMISS, Date.now().toString());
        hidePopup();
    }

    setTimeout(showPopup, DELAY_MS);

    closeBtn.addEventListener('click', dismissPopup);

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) dismissPopup();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') dismissPopup();
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var email = form.querySelector('input[type="email"]').value;
        var btn = form.querySelector('button');
        btn.disabled = true;
        btn.textContent = 'Odesílám...';

        var formData = new FormData();
        formData.append('email', email);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            mode: 'no-cors'
        }).then(function() {
            localStorage.setItem(KEY_SUB, '1');
            form.style.display = 'none';
            msg.style.display = 'block';
            msg.textContent = 'Díky! Sleva je na cestě do tvé schránky.';
            setTimeout(hidePopup, 3000);
        }).catch(function() {
            msg.style.display = 'block';
            msg.classList.add('gl-error');
            msg.textContent = 'Něco se nepovedlo. Zkus to prosím znovu.';
            btn.disabled = false;
            btn.textContent = 'Chci slevu';
        });
    });
})();
</script>
<?php
}

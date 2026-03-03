<?php
/**
 * Godlike – Newsletter popup s lokální DB kontrolou
 * Ukládá emaily do WordPress DB a kontroluje duplicity
 * Funguje i na WP Cloud a managed hostingu
 */

// 🔑 NASTAV SVŮ ECOMAIL ÚDAJE ZDE:
define('GODLIKE_ECOMAIL_LIST_URL', 'https://godlike.ecomailapp.cz/public/subscribe/15/864b4f4872272bdd94aadbc81dfd8b18'); // Z původního kódu

// Vytvoř DB tabulku při aktivaci
register_activation_hook(__FILE__, 'godlike_create_subscribers_table');

function godlike_create_subscribers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'godlike_subscribers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Zavolej to i teď pro jistotu (pokud se plugin aktivoval ručně)
add_action('init', 'godlike_create_subscribers_table');

// AJAX handler pro přidání subscribera
add_action('wp_ajax_godlike_subscribe', 'godlike_handle_subscribe');
add_action('wp_ajax_nopriv_godlike_subscribe', 'godlike_handle_subscribe');

function godlike_handle_subscribe() {
    global $wpdb;

    // Validace emailu
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Neplatný email']);
        return;
    }

    // Kontrola duplicity v lokální DB
    $table_name = $wpdb->prefix . 'godlike_subscribers';
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT email FROM $table_name WHERE email = %s",
        $email
    ));

    if ($existing) {
        // ⚠️ Email už existuje - pošli info a označ jako "už přihlášený"
        wp_send_json_error([
            'message' => 'Tento email je už registrovaný.',
            'duplicate' => true
        ]);
        return;
    }

    // Ulož email do DB
    $inserted = $wpdb->insert(
        $table_name,
        ['email' => $email],
        ['%s']
    );

    if ($inserted === false) {
        wp_send_json_error(['message' => 'Chyba při ukládání, zkus to znovu']);
        return;
    }

    // ✅ Úspěch - email uložen
    wp_send_json_success([
        'message' => 'Díky! Sleva je na cestě.',
        'ecomail_url' => GODLIKE_ECOMAIL_LIST_URL // Pošli URL pro browser
    ]);
}

add_action('wp_footer', 'godlike_newsletter_popup');

function godlike_newsletter_popup() {
    if ( is_user_logged_in() ) {
        return;
    }

    // Nezobrazuj popup na stránkách košíku, pokladny a potvrzení objednávky
    if ( function_exists('is_cart') && is_cart() ) return;
    if ( function_exists('is_checkout') && is_checkout() ) return;
    if ( is_page( array('kosik', 'pokladna') ) ) return;
    if ( isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'order-received') !== false ) return;
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
            <form id="gl-popup-form">
                <input type="email" name="email" placeholder="Tvůj e-mail" required>
                <button type="submit">Chci slevu</button>
            </form>
            <p id="gl-popup-msg" style="display:none;"></p>
        </div>
    </div>
</div>

<div id="gl-popup-trigger" style="display:none;">
    <span class="gl-trigger-text">Ušetři<br>100 Kč</span>
    <div class="gl-trigger-pulse"></div>
</div>

<style>
    /* TVŮJ PŮVODNÍ STYLING (zachován na 100%) */
    #gl-popup-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.7); z-index: 999999;
        display: flex; align-items: center; justify-content: center;
        padding: 20px; opacity: 0; transition: opacity 0.4s ease;
    }
    #gl-popup-overlay.gl-visible { opacity: 1; }
    #gl-popup {
        position: relative; display: flex; overflow: hidden;
        background: linear-gradient(170deg, #121a2b 0%, #0b1222 100%);
        border: 1px solid rgba(216,182,79,0.2); border-radius: 18px;
        max-width: 720px; width: 100%;
        box-shadow: 0 24px 80px rgba(0,0,0,0.6), 0 0 80px rgba(216,182,79,0.06);
        transform: translateY(30px) scale(0.96); transition: transform 0.4s ease;
    }
    #gl-popup-overlay.gl-visible #gl-popup { transform: translateY(0) scale(1); }
    .gl-popup-image { flex: 0 0 280px; position: relative; overflow: hidden; }
    .gl-popup-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .gl-popup-image::after {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(90deg, transparent 60%, #0b1222 100%); pointer-events: none;
    }
    #gl-popup-content { flex: 1; padding: 44px 40px 40px; display: flex; flex-direction: column; justify-content: center; }
    #gl-popup-close {
        position: absolute; top: 12px; right: 16px; background: none; border: none;
        color: rgba(255,255,255,0.5); font-size: 26px; cursor: pointer; line-height: 1;
        padding: 4px 8px; border-radius: 8px; transition: color 0.2s, background 0.2s; z-index: 2;
    }
    .gl-popup-label { font-family: 'Poppins', sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 2.5px; color: #d8b64f; margin: 0 0 14px; }
    .gl-popup-title { font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff; line-height: 1.4; margin: 0 0 18px; }
    .gl-popup-desc { font-family: 'Poppins', sans-serif; font-size: 14px; color: rgba(255,255,255,0.65); margin: 0; line-height: 1.4; }
    .gl-popup-discount { font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 46px; background: linear-gradient(135deg, #f5e6a3 0%, #d8b64f 50%, #c9a23e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 6px 0; line-height: 1.15; }
    #gl-popup-form { margin-top: 22px; display: flex; gap: 10px; }
    #gl-popup-form input[type="email"] { font-family: 'Poppins', sans-serif; font-size: 14px; padding: 13px 16px; border-radius: 10px; border: 1px solid rgba(216,182,79,0.25); background: rgba(255,255,255,0.06); color: #ffffff; outline: none; transition: border-color 0.2s; flex: 1; min-width: 0; box-sizing: border-box; }
    #gl-popup-form button { font-family: 'Raleway', sans-serif; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 1.2px; padding: 13px 22px; border-radius: 10px; border: none; background: linear-gradient(135deg, #59e391 0%, #3cc974 100%); color: #0b1222; cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; white-space: nowrap; box-sizing: border-box; }
    #gl-popup-msg { font-family: 'Poppins', sans-serif; font-size: 13px; margin-top: 14px; }
    #gl-popup-msg.success { color: #59e391; }
    #gl-popup-msg.error { color: #ff6b6b; }

    /* TVÉ PŮVODNÍ MEDIA QUERIES */
    @media (max-width: 620px) {
        #gl-popup { flex-direction: column; max-width: 400px; }
        .gl-popup-image { flex: 0 0 auto; height: 180px; }
        .gl-popup-image::after { background: linear-gradient(180deg, transparent 40%, #0b1222 100%); }
        #gl-popup-content { padding: 24px 28px 32px; }
        .gl-popup-title { font-size: 17px; }
        .gl-popup-discount { font-size: 36px; }
        .gl-popup-label { font-size: 10px; }
        #gl-popup-form { flex-direction: column; }
        #gl-popup-close { font-size: 32px; padding: 4px 10px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); }
    }

    /* STYL PRO KOLEČKO */
    #gl-popup-trigger {
        position: fixed; bottom: 20px; right: 20px;
        width: 70px; height: 70px; border-radius: 50%;
        background: linear-gradient(135deg, #d8b64f 0%, #c9a23e 100%);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; z-index: 999998;
        box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        animation: glBounce 2s infinite ease-in-out;
    }
    .gl-trigger-text {
        font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 10px;
        color: #0b1222; text-align: center; line-height: 1.1; text-transform: uppercase; z-index: 2;
    }
    .gl-trigger-pulse {
        position: absolute; width: 100%; height: 100%; border-radius: 50%;
        background: #d8b64f; opacity: 0.5; animation: glPulse 2s infinite; z-index: 1;
    }
    @keyframes glPulse { 0% { transform: scale(1); opacity: 0.5; } 100% { transform: scale(1.6); opacity: 0; } }
    @keyframes glBounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
</style>

<script>
(function(){
    var KEY_SUB = 'gl_popup_subscribed';
    var KEY_DISMISS = 'gl_popup_dismiss';
    var DELAY_MS = 8000;
    var TWO_DAYS_MS = 2 * 24 * 60 * 60 * 1000;

    // Pokud se uživatel přihlásil k odběru, nič nezobrazuj
    if (localStorage.getItem(KEY_SUB)) return;

    var overlay = document.getElementById('gl-popup-overlay');
    var trigger = document.getElementById('gl-popup-trigger');
    var closeBtn = document.getElementById('gl-popup-close');
    var form = document.getElementById('gl-popup-form');
    var msg = document.getElementById('gl-popup-msg');
    var wasAutoShown = false;

    // Načti data o předchozích zavřeních z localStorage
    var dismissData = null;
    try {
        dismissData = JSON.parse(localStorage.getItem(KEY_DISMISS));
    } catch(e) {}

    function showPopup() {
        trigger.style.display = 'none';
        overlay.style.display = 'flex';
        setTimeout(function(){ overlay.classList.add('gl-visible'); }, 50);
    }

    function hidePopup() {
        overlay.classList.remove('gl-visible');
        setTimeout(function(){ overlay.style.display = 'none'; }, 400);
    }

    function handleDismiss() {
        hidePopup();
        trigger.style.display = 'flex';

        // Počítej jen zavření auto-popupu (ne manuální otevření přes trigger)
        if (wasAutoShown) {
            var current = null;
            try { current = JSON.parse(localStorage.getItem(KEY_DISMISS)); } catch(e) {}
            var newCount = current ? current.count + 1 : 1;
            localStorage.setItem(KEY_DISMISS, JSON.stringify({
                count: newCount,
                timestamp: Date.now()
            }));
            wasAutoShown = false;
        }
    }

    function permanentlyHide() {
        localStorage.setItem(KEY_SUB, '1');
        localStorage.removeItem(KEY_DISMISS);
        hidePopup();
        trigger.style.display = 'none';
    }

    // Rozhodnutí: má se popup automaticky zobrazit?
    var shouldAutoShow = false;

    if (!dismissData) {
        // Nikdy nezavřel -> zobraz popup
        shouldAutoShow = true;
    } else if (dismissData.count === 1) {
        // Zavřel jednou -> zobraz znovu až po 2 dnech
        var elapsed = Date.now() - dismissData.timestamp;
        if (elapsed >= TWO_DAYS_MS) {
            shouldAutoShow = true;
        }
    }
    // count >= 2: nikdy automaticky nezobrazovat

    if (shouldAutoShow) {
        setTimeout(function() {
            wasAutoShown = true;
            showPopup();
        }, DELAY_MS);
    } else if (dismissData) {
        // Popup se nezobrazí, ale trigger tlačítko ano
        trigger.style.display = 'flex';
    }

    closeBtn.addEventListener('click', handleDismiss);
    trigger.addEventListener('click', function() {
        wasAutoShown = false;
        showPopup();
    });
    overlay.addEventListener('click', function(e) { if (e.target === overlay) handleDismiss(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') handleDismiss(); });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = form.querySelector('button');
        var emailInput = form.querySelector('input[type="email"]');
        var emailValue = emailInput.value;

        btn.disabled = true;
        btn.textContent = 'Moment...';
        msg.style.display = 'none';

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'godlike_subscribe',
                email: emailValue
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                var ecomailParams = new URLSearchParams();
                ecomailParams.append('email', emailValue);

                fetch(data.data.ecomail_url, {
                    method: 'POST',
                    body: ecomailParams,
                    mode: 'no-cors'
                }).then(function() {}).catch(function() {});

                form.style.display = 'none';
                msg.className = 'success';
                msg.style.display = 'block';
                msg.textContent = data.data.message;

                setTimeout(function() {
                    permanentlyHide();
                }, 3000);

            } else {
                msg.className = 'error';
                msg.style.display = 'block';
                msg.textContent = data.data.message;
                btn.disabled = false;
                btn.textContent = 'Chci slevu';

                if (data.data.duplicate) {
                    form.style.display = 'none';

                    setTimeout(function() {
                        permanentlyHide();
                    }, 3000);
                }
            }
        })
        .catch(function(error) {
            msg.className = 'error';
            msg.style.display = 'block';
            msg.textContent = 'Chyba spojení, zkus to znovu';
            btn.disabled = false;
            btn.textContent = 'Chci slevu';
        });
    });
})();
</script>
<?php
}

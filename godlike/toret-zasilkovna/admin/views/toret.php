<?php

?>

<script type="text/javascript">
    jQuery('document').ready(function(){
    document.getElementById("toret-copy").addEventListener("click", function(){
        var t = document.getElementById("toret-plugins-admin-diag").textContent;
        var fn = "ToretDiagnostika.txt";
        dwn(fn, t);
    }, false);
    function dwn(fn, t) {
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(t));
        element.setAttribute('download', fn);
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);}
    });
</script>

<style>

    :root {
        --wp--preset--color--contrast: #111111; --wp--preset--color--accent-1: #6b60ee; --wp--preset--color--accent-2: #f7f4fd; --wp--preset--color--accent-3: #471e89; --wp--preset--color--accent-4: #341664;
    }

.toret-plugins-admin-img {
    max-width: 1000px;
    width: 100%;
}
.toret-admin-buttons {
    display: flex;
    margin-top: 25px;
}
.toret-admin-buttons a, #toret-copy {
    color: white;
    background-color: #471e89;
    padding: 20px 35px;
    border-radius: 10px;
    text-decoration: none;
    margin-right: 10px;
}
.toret-admin-buttons a:last-child {
    background-color: #6b60ee;
}
.toret-admin-buttons a:hover   {
    opacity: 0.7;
}
.toret-plugins-admin-table {
    background-color: white;
    padding: 25px;
    margin: 25px 0;
    border-radius: 25px;
}
.toret-plugins-admin-table h2 {
    color: #471e89;
    margin-top: 0;
}
.toret-plugins-admin-table table {
    border: 2px solid #f8f7fc;
    width: 100%;
    border-collapse: collapse;
}

.toret-plugins-admin-table table thead {
    background-color: #f8f7fc;
}
.toret-plugins-admin-table table tr {
    border-bottom: 2px solid #f8f7fc;
}
.toret-plugins-admin-table table th, .toret-plugins-admin-table table td {
    padding: 15px;
    text-align: left;
}

.toret-plugins-admin-table h3 {
    margin-top: 30px;
}
.toret-plugins-admin-buy {
    color: white;
    padding: 8px 15px;
    background-color: #6b60ee;
    text-decoration: none;
    border-radius: 10px;
    text-transform: uppercase;
}
.toret-plugins-admin-buy:hover {
    color:white;
    background-color: #471e89;
}
.toret-plugins-admin-table .toret-plugins-admin-last {
    text-align: right;
}
.toret-plugins-admin-link {
    color: #471e89;
}
.toret-plugins-admin-link:hover {
    color: #6b60ee;
}
#toret-plugins-admin-diag {
    display:none;
}

.toret-top-banner {
    margin: 25px 0;
}

#toret-copy {
    border: none;
    margin-bottom: 10px;
    padding: 10px 20px;
    cursor: pointer;
}

#toret-copy:hover {
    background: #6b60ee;
}
.plugin-benefits__cols{
  display:grid;
  grid-template-columns:repeat(3, minmax(0,1fr));
  gap:clamp(14px,2.2vw,24px);
  align-items:stretch; /* všechny buňky stejně vysoké */
}

.plugin-benefits__card{
  display:flex;             /* aby se obsah uvnitř přizpůsobil */
  flex-direction:column;    /* obsah pod sebou */
}

@media (max-width:1200px){
  .plugin-benefits__cols{ grid-template-columns:repeat(2,1fr); }
}
@media (max-width:680px){
  .plugin-benefits__cols{ grid-template-columns:1fr; }
}

.plugin-benefits__card{
  background: var(--wp--preset--color--accent-2);
  border:1px solid rgba(0,0,0,.06);
  border-radius:12px;
  padding:28px 22px 24px;
  box-shadow:0 4px 12px rgba(0,0,0,.04);
}
.plugin-benefits__card:hover{
  box-shadow:0 6px 18px rgba(0,0,0,.07);
}

.plugin-benefits__icon{
  width:56px;
  height:56px;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  background: var(--wp--preset--color--accent-1);
  color:#fff;
  font-size:28px;
  margin-bottom:16px;
}
.plugin-benefits__icon .dashicons{
  font-size:28px;
  width:28px;
  height:28px;
  line-height:1;
}

.plugin-benefits__card h3{
  margin:0 0 10px;
  color: var(--wp--preset--color--contrast);
}
.plugin-benefits__card p{
  margin:0 0 12px;
  color:#333;
}

.plugin-benefits__link{
  display:inline-block;
  margin-top:4px;
  color: var(--wp--preset--color--accent-3);
  font-weight:600;
  text-decoration:none;
}
.plugin-benefits__link:hover{
  color: var(--wp--preset--color--accent-1);
  text-decoration:underline;
}



</style>

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

<div class="toret-top-banner">
    <a href="https://toret.cz/obchod/?utm_source=administrace&utm_medium=ppd&utm_campaign=banner-recenze" target="_blank">
        <img src="<?php echo WP_PLUGIN_URL; ?>/toret-zasilkovna/assets/images/recenze_banner.png" class="toret-plugins-admin-img" />
    </a>
</div>


    <div class="toret-admin-buttons">
        <a class="toret-muj-ucet" href="https://toret.cz/muj-ucet/" target="_blank">ZOBRAZIT MŮJ ÚČET</a>
        <a class="toret-muj-ucet" href="https://toret.cz/dokumentace-k-pluginum/" target="_blank">DOKUMENTACE</a>
        <a class="toret-podpora" href="https://toret.cz/podpora/" target="_blank">KONTAKTOVAT PODPORU</a>
    </div>

    <div class="toret-plugins-admin-table">
        <section class="wp-block-group alignwide plugin-benefits">
        <h2>Vylepšete si e-shop pomocí našich dalších pluginů</h2>

        <div class="plugin-benefits__cols">
            <!-- 1. Doručení -->
            <article class="plugin-benefits__card">
            <div class="plugin-benefits__icon">
                <span class="dashicons dashicons-migrate"></span>
            </div>
            <h3>Ušetřete čas při odesílání zásilek</h3>
            <p>Dopravní pluginy propojí váš e-shop přímo s dopravcem a budou zásilky odesílat za vás.</p>
            <a href="#doruceni" class="plugin-benefits__link">Zobrazit pluginy</a>
            </article>

            <!-- 2. Platby -->
            <article class="plugin-benefits__card">
            <div class="plugin-benefits__icon">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <h3>Více dokončených objednávek díky oblíbeným platebním metodám</h3>
            <p>Populární a bezpečné metody platby do pokladny přidáte s našimi platebními pluginy.</p>
            <a href="#platby" class="plugin-benefits__link">Zobrazit pluginy</a>
            </article>

            <!-- 3. Fakturace -->
            <article class="plugin-benefits__card">
            <div class="plugin-benefits__icon">
                <span class="dashicons dashicons-media-text"></span>
            </div>
            <h3>Fakturujte bez práce a bez chyb</h3>
            <p>Vystavení a odeslání faktury zákazníkovi i párování plateb za vás zvládne fakturační plugin.</p>
            <a href="#fakturace" class="plugin-benefits__link">Zobrazit pluginy</a>
            </article>

            <!-- 4. Marketing -->
            <article class="plugin-benefits__card">
            <div class="plugin-benefits__icon">
                <span class="dashicons dashicons-megaphone"></span>
            </div>
            <h3>Oslovte nové zákazníky přes srovnávače</h3>
            <p>S pomocí našich pluginů pro marketing je inzerce na srovnávačích hračka.</p>
            <a href="#marketing" class="plugin-benefits__link">Zobrazit pluginy</a>
            </article>

            <!-- 5. Přizpůsobení -->
            <article class="plugin-benefits__card">
            <div class="plugin-benefits__icon">
                <span class="dashicons dashicons-admin-settings"></span>
            </div>
            <h3>Přidejte do e-shopu nové funkce a zjednodušte si práci</h3>
            <p>Usnadněte si správu, vylaďte cestu zákazníka nebo splňte požadavky zákona s pluginy pro přizpůsobení e-shopu.</p>
            <a href="#prizpusobeni" class="plugin-benefits__link">Zobrazit pluginy</a>
            </article>
        </div>
        </section>


        <h3 id="doruceni">Pluginy pro dopravu a doručení</h3>
<table>
<thead>
<tr>
    <th>Jméno pluginu</th>
    <th>Popisek</th>
    <th></th>
</tr>
</thead>
<tbody>
    <?php /*    KOPÍROVAT    */ ?>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/adulto-cz/?utm_source=administrace&utm_medium=ppd&utm_campaign=adulto-cz" target="_blank" class="toret-plugins-admin-link">Adulto.cz</a>
        </td>
        <td>
            Jestliže prodáváte zboží, u kterého je nutné ověření věku zákazníka, můžete k tomu využít službu Adulto.cz a ověřit věk zákazníka pomocí bankovní identity přímo ve WooCommerce pokladně.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/adulto-cz/?utm_source=administrace&utm_medium=ppd&utm_campaign=adulto-cz" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/balikobot/?utm_source=administrace&utm_medium=ppd&utm_campaign=balikobot" target="_blank" class="toret-plugins-admin-link">Balíkobot</a>
        </td>
        <td>
            Oficiální WordPress plugin pro WooCommerce, který e-shop propojuje se službou Balíkobot.cz.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/balikobot/?utm_source=administrace&utm_medium=ppd&utm_campaign=balikobot" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/ceska-posta/?utm_source=administrace&utm_medium=ppd&utm_campaign=ceska-posta" target="_blank" class="toret-plugins-admin-link">Česká pošta</a>
        </td>
        <td>
            Propojte e-shop se systémem České pošty. Plugin podporuje služby Balíkovna, Balík Do ruky, Cenný balík, EMS, Doporučený balíček, Obyčejný balík a další. Odesílání je možné přes API nebo CSV export.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/ceska-posta/?utm_source=administrace&utm_medium=ppd&utm_campaign=ceska-posta" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/dpd/?utm_source=administrace&utm_medium=ppd&utm_campaign=dpd" target="_blank" class="toret-plugins-admin-link">DPD</a>
        </td>
        <td>
            Integrace dopravce DPD do WooCommerce. Na kliknutí či plně automaticky lze vytvářet zásilky v systému dopravce, generovat štítky, zasílat sledovací odkazy i objednávat svoz. Podporuje doručení na adresu, výdejních míst i boxů.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/dpd/?utm_source=administrace&utm_medium=ppd&utm_campaign=dpd" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/fofr/?utm_source=administrace&utm_medium=ppd&utm_campaign=fofr" target="_blank" class="toret-plugins-admin-link">FOFR</a>
        </td>
        <td>
            Implementujte dopravce FOFR do WooCommerce e-shopu a odesílejte běžné i nadrozměrné zásilky do ČR a EU na pár kliků. Snadná správa zásilek, generování sledovacích odkazů, tisk štítků, dobírka, doprava zdarma a další funkce.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/fofr/?utm_source=administrace&utm_medium=ppd&utm_campaign=fofr" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/gls/?utm_source=administrace&utm_medium=ppd&utm_campaign=gls" target="_blank" class="toret-plugins-admin-link">GLS</a>
        </td>
        <td>
            Propojte e-shop se systémem dopravce GLS a doručujte na adresu, do výdejních míst i boxů. Na kliknutí či plně automaticky odešlete data o zásilce, vygenerujete štítky i sledovací odkaz a objednáte svoz.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/gls/?utm_source=administrace&utm_medium=ppd&utm_campaign=gls" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/go-balik/?utm_source=administrace&utm_medium=ppd&utm_campaign=go-balik" target="_blank" class="toret-plugins-admin-link">GO balík</a>
        </td>
        <td>
            Propojte WooCommerce se službou Go balík, která nabízí dopravu s řadou oblíbených dopravců za výhodné ceny. Díky přímému napojení odešlete objednávky do systému, vytisknete štítky a sledujete zásilky přímo z administrace. Plugin umožňuje také automatizaci a pokročilé nastavení dostupnosti i cen.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/go-balik/?utm_source=administrace&utm_medium=ppd&utm_campaign=go-balik" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/one-by-allegro/?utm_source=administrace&utm_medium=ppd&utm_campaign=one-by-allegro" target="_blank" class="toret-plugins-admin-link">One by Allegro</a>
        </td>
        <td>
            Plugin pro přímé napojení na API dopravce One by Allegro pro doručení na adresu, do výdejen i boxů. Jednoduché odeslání dat do systému, objednání svozu, tisk štítků i sledování zásilek přímo z administrace a bohaté možnosti nastavení.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/one-by-allegro/?utm_source=administrace&utm_medium=ppd&utm_campaign=one-by-allegro" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/ppl/?utm_source=administrace&utm_medium=ppd&utm_campaign=ppl" target="_blank" class="toret-plugins-admin-link">PPL</a>
        </td>
        <td>
            Integrujte dopravce PPL do WooCommerce a doručujte na adresu, do výdejních míst i boxů. Na kliknutí nebo úplně automaticky odešlete data o zásilce, vygenerujete štítky i sledovací odkaz a objednáte svoz.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/ppl/?utm_source=administrace&utm_medium=ppd&utm_campaign=ppl" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/sledovani-zasilek-cz/?utm_source=administrace&utm_medium=ppd&utm_campaign=sledovani-zasilek-cz" target="_blank" class="toret-plugins-admin-link">Sledování Zásilek CZ</a>
        </td>
        <td>
            Pošlete zákazníkovi sledovací číslo balíku. Plugin podporuje dopravce Česká pošta, DPD, PPL, DHL, GLS, Zásilkovna, Uloženka, Geis, Slovenská Pošta a InTime.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/sledovani-zasilek-cz/?utm_source=administrace&utm_medium=ppd&utm_campaign=sledovani-zasilek-cz" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/sledovani-zasilek/?utm_source=administrace&utm_medium=ppd&utm_campaign=sledovani-zasilek-sk" target="_blank" class="toret-plugins-admin-link">Sledování Zásilek SK</a>
        </td>
        <td>
            Pošlete zákazníkovi sledovací číslo balíku. Plugin podporuje dopravce Česká pošta, DPD, PPL, DHL, GLS, Zásilkovna, Geis a Slovenská Pošta.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/sledovani-zasilek/?utm_source=administrace&utm_medium=ppd&utm_campaign=sledovani-zasilek-sk" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/slovenska-posta/?utm_source=administrace&utm_medium=ppd&utm_campaign=slovenska-posta" target="_blank" class="toret-plugins-admin-link">Slovenská pošta</a>
        </td>
        <td>
            Propojte svůj e-shop se Slovenskou poštou a posílejte zásilky na poštu, do boxů i na adresu. Kompletní správa zásilek z administrace, detailní nastavení a široká nabídka služeb vám usnadní každodenní práci s odesíláním.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/slovenska-posta/?utm_source=administrace&utm_medium=ppd&utm_campaign=slovenska-posta" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/toolkit/?utm_source=administrace&utm_medium=ppd&utm_campaign=toolkit" target="_blank" class="toret-plugins-admin-link">Toolkit</a>
        </td>
        <td>
            Přidejte do e-shopu nové funkce, které usnadní správu a pomůžou vyhovět zákonům. Plugin automaticky generuje přehled cen dopravy, zobrazuje dodací lhůtu, přidává stav objednávky Předáno dopravci a další.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/toolkit/?utm_source=administrace&utm_medium=ppd&utm_campaign=toolkit" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/toptrans/?utm_source=administrace&utm_medium=ppd&utm_campaign=toptrans" target="_blank" class="toret-plugins-admin-link">TOPTRANS</a>
        </td>
        <td>
            Propojte e-shop se systémem dopravce TOPTRANS, experta na nadrozměrné zásilky. Odesílejte na pár kliknutí či plně automaticky přímo z administrace, a generujte štítky i sledovací odkazy.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/toptrans/?utm_source=administrace&utm_medium=ppd&utm_campaign=toptrans" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/vlastni-pobocky/?utm_source=administrace&utm_medium=ppd&utm_campaign=vlastni-pobocky" target="_blank" class="toret-plugins-admin-link">Vlastní pobočky</a>
        </td>
        <td>
            Vytvořte vlastní síť poboček a způsoby dopravy přesně tak, jak potřebujete. Plugin nabízí široké možnosti nastavení cen, dobírku a příplatky, widget s mapou, správu skladu na úrovni poboček a řadu dalších funkcí.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/vlastni-pobocky/?utm_source=administrace&utm_medium=ppd&utm_campaign=vlastni-pobocky" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/woocommerce-zasilkovna/?utm_source=administrace&utm_medium=ppd&utm_campaign=zasilkovna" target="_blank" class="toret-plugins-admin-link">Zásilkovna</a>
        </td>
        <td>
            Propojte svůj e-shop s dopravcem Zásilkovna a odesílejte zásilky na adresu, do výdejních míst a boxů na pár kliknutí či plně automaticky. Plugin umožňuje sledování i tisk štítků přímo z administrace, podporuje zpětné zásilky a nabízí široké možnosti nastavení.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/woocommerce-zasilkovna/?utm_source=administrace&utm_medium=ppd&utm_campaign=zasilkovna" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <tr>
        <td>
            <a href="https://toret.cz/produkt/ybox24/?utm_source=administrace&utm_medium=ppd&utm_campaign=ybox24" target="_blank" class="toret-plugins-admin-link">ybox24</a>
        </td>
        <td>
            Propojte svůj e-shop s chytrými úložnými boxy ybox24 a nabídněte zákazníkům moderní způsob doručení i vyzvednutí zásilek.
        </td>
        <td class="toret-plugins-admin-last">
            <a href="https://toret.cz/produkt/ybox24/?utm_source=administrace&utm_medium=ppd&utm_campaign=ybox24" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
        </td>
    </tr>
    <?php /*        KONEC    */ ?>
</tbody>
</table>




<h3  id="platby">Pluginy pro platby</h3>
<table>
<thead>
    <tr>
        <th>Jméno pluginu</th>
        <th>Popisek</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
        <?php /*    KOPÍROVAT    */ ?>
        <tr>
            <td>
                <a href="https://toret.cz/produkt/woo-comgate/?utm_source=administrace&utm_medium=ppd&utm_campaign=Comgate" target="_blank" class="toret-plugins-admin-link">Comgate</a>
            </td>
            <td>
                Integrace populární platební brány Comgate nabízí moderní platební metody (platba kartou, bankovními tlačítky, elektronickými peněženkami aj.), podporu opakovaných plateb i platby na splátky. Platby mohou probíhat v režimu inline (pop-up okno) i redirect (přesměrování).
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/woo-comgate/?utm_source=administrace&utm_medium=ppd&utm_campaign=Comgate" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/csob/?utm_source=administrace&utm_medium=ppd&utm_campaign=csob" target="_blank" class="toret-plugins-admin-link">ČSOB</a>
            </td>
            <td>
                Přidejte si do e-shopu platební bránu ČSOB, která umožňuje platbu kartou, elektronickými peněženkami i bankovním tlačítkem ČSOB. Plugin podporuje také odložené platby či placení OneClick s uložením údajů z karty.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/csob/?utm_source=administrace&utm_medium=ppd&utm_campaign=csob" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/fio/?utm_source=administrace&utm_medium=ppd&utm_campaign=fio-banka" target="_blank" class="toret-plugins-admin-link">Fio banka</a>
            </td>
            <td>
                Propojte e-shop s platební bránou Fio a vaším Fio účtem. Plugin umožňuje platbu kartou či elektronickou peněženkou (Google Pay, Apple Pay). Velmi užitečné je automatické párování plateb převodem (na Fio účet) s příslušnými objednávkami.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/fio/?utm_source=administrace&utm_medium=ppd&utm_campaign=fio-banka" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/woo-gp-webpay/?utm_source=administrace&utm_medium=ppd&utm_campaign=GPwebpay" target="_blank" class="toret-plugins-admin-link">GP webpay</a>
            </td>
            <td>
                Integrace platební brány GP webpay umožňuje platbu kartou, elektronickými peněženkami, bankovními tlačítky i QR kódem.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/woo-gp-webpay/?utm_source=administrace&utm_medium=ppd&utm_campaign=GPwebpay" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/woo-gopay-inline-platebni-brana/?utm_source=administrace&utm_medium=ppd&utm_campaign=GoPayInline" target="_blank" class="toret-plugins-admin-link">GoPay Inline</a>
            </td>
            <td>
                Populární inline platební brána, ve které vaši zákazníci mohou zaplatit mnoha způsoby - od platební karty a elektronické peněženky přes bankovní tlačítka až po speciality jako je platba Bitcoinem, paysafecard, SMS či M-platby. Plugin podporuje opakované platby i refundace.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/woo-gopay-inline-platebni-brana/?utm_source=administrace&utm_medium=ppd&utm_campaign=GoPayInline" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/qr-platby/?utm_source=administrace&utm_medium=ppd&utm_campaign=qr-platby" target="_blank" class="toret-plugins-admin-link">QR platby</a>
            </td>
            <td>
                Implementujte do e-shopu možnost placení QR kódem a zrychlete platbu bankovním převodem. QR kód a platební údaje lze zobrazovat na děkovné stránce, v e-mailu, účtu zákazníka a na faktuře.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/qr-platby/?utm_source=administrace&utm_medium=ppd&utm_campaign=qr-platby" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/thepay-2-0/?utm_source=administrace&utm_medium=ppd&utm_campaign=thepay20" target="_blank" class="toret-plugins-admin-link">ThePay 2.0</a>
            </td>
            <td>
                Integrace platební brány ThePay 2.0 nabízí široké možnosti placení - od platby kartou přes platební tlačítka a elektronické peněženky až po opakované a odložené platby.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/thepay-2-0/?utm_source=administrace&utm_medium=ppd&utm_campaign=thepay20" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>
        <?php /*        KONEC    */ ?>
    </tbody>
</table>


<h3  id="fakturace">Pluginy pro fakturaci a účetnictví</h3>
<table>
<thead>
    <tr>
        <th>Jméno pluginu</th>
        <th>Popisek</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
        <?php /*    KOPÍROVAT    */ ?>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/woocommerce-fakturoid/?utm_source=administrace&utm_medium=ppd&utm_campaign=fakturoid" target="_blank" class="toret-plugins-admin-link">Fakturoid</a>
            </td>
            <td>
                Propojení e-shopu a fakturačního systému Fakturoid. Automatizujte tvorbu i odesílání faktur, proforem a dobropisů. Plugin umožňuje také automatické párování plateb převodem s objednávkami.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/woocommerce-fakturoid/?utm_source=administrace&utm_medium=ppd&utm_campaign=fakturoid" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/toret-fio/?utm_source=administrace&utm_medium=ppd&utm_campaign=fio" target="_blank" class="toret-plugins-admin-link">Fio banka</a>
            </td>
            <td>
                Propojte e-shop s vaším Fio účtem a automaticky párujte platby převodem s objednávkami. Kromě kontroly zaplacenosti objednávek nabízí plugin také integraci platební brány Fio.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/toret-fio/?utm_source=administrace&utm_medium=ppd&utm_campaign=fio" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/woo-idoklad/?utm_source=administrace&utm_medium=ppd&utm_campaign=idoklad" target="_blank" class="toret-plugins-admin-link">iDoklad</a>
            </td>
            <td>
                Propojení e-shopu a fakturačního systému iDoklad. Automatizujte tvorbu i odesílání faktur, proforem a dobropisů. Plugin umožňuje také automatické párování plateb převodem s objednávkami.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/woo-idoklad/?utm_source=administrace&utm_medium=ppd&utm_campaign=idoklad" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/order-numbers/?utm_source=administrace&utm_medium=ppd&utm_campaign=ordernumbers" target="_blank" class="toret-plugins-admin-link">Order Numbers</a>
            </td>
            <td>
                Plugin pro WooCommerce, který do e-shopu přidává postupné číslování objednávek a možnost vlastního číslování objednávek.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/order-numbers/?utm_source=administrace&utm_medium=ppd&utm_campaign=ordernumbers" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/qr-platby/?utm_source=administrace&utm_medium=ppd&utm_campaign=qr-platby" target="_blank" class="toret-plugins-admin-link">QR platby</a>
            </td>
            <td>
                Zobrazujte QR kód pro platby převodem na děkovné stránce, v e-mailu, účtu zákazníka i na fakturách z pluginů DF Invoices & Packing Slips for WooCommerce nebo WooCommerce PDF Invoicing and Packing Slips od Booster.io.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/qr-platby/?utm_source=administrace&utm_medium=ppd&utm_campaign=qr-platby" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/toolkit/?utm_source=administrace&utm_medium=ppd&utm_campaign=toolkit" target="_blank" class="toret-plugins-admin-link">Toolkit</a>
            </td>
            <td>
                Přidejte do e-shopu nové funkce, které usnadní správu a pomůžou vyhovět zákonům. Plugin do pokladny přidává firemní pole pro IČ, DIČ a IČ DPH. Při zadání IČ nabízí automatické vyplnění údajů o subjektu z ARES či RÚZ a přináší i další užitečné funkce.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/toolkit/?utm_source=administrace&utm_medium=ppd&utm_campaign=toolkit" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/toret-vyfakturuj/?utm_source=administrace&utm_medium=ppd&utm_campaign=vyfakturuj" target="_blank" class="toret-plugins-admin-link">Vyfakturuj</a>
            </td>
            <td>
                Propojení e-shopu a fakturačního systému Vyfakturuj. Automatizujte tvorbu i odesílání faktur, proforem a dobropisů. Plugin umožňuje také automatické párování plateb převodem s objednávkami.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/toret-vyfakturuj/?utm_source=administrace&utm_medium=ppd&utm_campaign=vyfakturuj" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <?php /*        KONEC    */ ?>
    </tbody>
</table>


<h3  id="marketing">Pluginy pro marketing</h3>
<table>
<thead>
    <tr>
        <th>Jméno pluginu</th>
        <th>Popisek</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
        <?php /*    KOPÍROVAT    */ ?>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/vokativ/?utm_source=administrace&utm_medium=ppd&utm_campaign=ceske-osloveni" target="_blank" class="toret-plugins-admin-link">České oslovení</a>
            </td>
            <td>
                Oslovujte své zákazníky správně – 5. pádem. Plugin automaticky upravuje oslovení zákazníka v e-mailu a sekci Můj účet.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/vokativ/?utm_source=administrace&utm_medium=ppd&utm_campaign=ceske-osloveni" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/glami-pixel-woocommerce/?utm_source=administrace&utm_medium=ppd&utm_campaign=glami-pixel" target="_blank" class="toret-plugins-admin-link">Glami Pixel</a>
            </td>
            <td>
                Implementace konverzního kódu pro srovnávač zboží Glami. Měření zobrazení stránek, produktů, přidání do košíku a dokončených objednávek.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/glami-pixel-woocommerce/?utm_source=administrace&utm_medium=ppd&utm_campaign=glami-pixel" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/woo-smart-emailing/?utm_source=administrace&utm_medium=ppd&utm_campaign=smartemailing" target="_blank" class="toret-plugins-admin-link">SmartEmailing</a>
            </td>
            <td>
                Propojte svůj e-shop s e-mailingovou platformou. S pomocí pluginu lze v pokladně zobrazit souhlas s přihlášením k odběru newsletteru. Získané kontakty můžete odesílat do různých seznamů odběratelů v systému SmartEmailing.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/woo-smart-emailing/?utm_source=administrace&utm_medium=ppd&utm_campaign=smartemailing" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/srovnavace-zbozi/?utm_source=administrace&utm_medium=ppd&utm_campaign=srovnavace-zbozi" target="_blank" class="toret-plugins-admin-link">Srovnávače zboží</a>
            </td>
            <td>
                Propojte svůj e-shop se srovnávači zboží. Plugin umožňuje měření konverzí, přejímání recenzí ze srovnávačů Heuréka a Zboží.cz, implementaci služeb Ověřeno zákazníky a Sklik retargeting.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/srovnavace-zbozi/?utm_source=administrace&utm_medium=ppd&utm_campaign=srovnavace-zbozi" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/xml-feeds/?utm_source=administrace&utm_medium=ppd&utm_campaign=xml-feeds" target="_blank" class="toret-plugins-admin-link">XML Feeds</a>
            </td>
            <td>
                Vytvářejte na pár kliknutí XML feedy pro inzerci na českých, zahraničních i světových srovnávačích zboží. Plugin nabízí široké možnosti přizpůsobení i automatické generování feedů.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/xml-feeds/?utm_source=administrace&utm_medium=ppd&utm_campaign=xml-feeds" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <?php /*        KONEC    */ ?>
    </tbody>
</table>


<h3  id="prizpusobeni">Pluginy pro přizpůsobení obchodu</h3>
<table>
<thead>
    <tr>
        <th>Jméno pluginu</th>
        <th>Popisek</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
        <?php /*    KOPÍROVAT    */ ?>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/breadcrumbs/?utm_source=administrace&utm_medium=ppd&utm_campaign=breadcrumbs" target="_blank" class="toret-plugins-admin-link">Drobečková navigace</a>
            </td>
            <td>
                Vytvářejte vlastní strukturu drobečkové navigace pomocí WordPress menu. Kombinujte všechny typy obsahu přesně tak, jak potřebujete.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/breadcrumbs/?utm_source=administrace&utm_medium=ppd&utm_campaign=breadcrumbs" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/dotaz-na-produkt/?utm_source=administrace&utm_medium=ppd&utm_campaign=dotaz-na-produkt" target="_blank" class="toret-plugins-admin-link">Dotaz na produkt</a>
            </td>
            <td>
                Přidejte ke svým WooCommerce produktům kontaktní formulář, přes který vám může zákazník napsat.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/dotaz-na-produkt/?utm_source=administrace&utm_medium=ppd&utm_campaign=dotaz-na-produkt" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/order-numbers/?utm_source=administrace&utm_medium=ppd&utm_campaign=ordernumbers" target="_blank" class="toret-plugins-admin-link">Order Numbers</a>
            </td>
            <td>
                Plugin pro WooCommerce, který do e-shopu přidává postupné číslování objednávek a možnost vlastního číslování objednávek.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/order-numbers/?utm_source=administrace&utm_medium=ppd&utm_campaign=ordernumbers" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/telefon/?utm_source=administrace&utm_medium=ppd&utm_campaign=telefon" target="_blank" class="toret-plugins-admin-link">Telefon</a>
            </td>
            <td>
                Plugin Telefon upravuje výchozí telefonní pole v pokladně, do kterého přidává možnost výběru státu, jeho telefonní předvolby a ověření správnosti tvaru čísla. Tím eliminuje chyby a předchází problémům s doručením.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/telefon/?utm_source=administrace&utm_medium=ppd&utm_campaign=telefon" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/toolkit/?utm_source=administrace&utm_medium=ppd&utm_campaign=toolkit" target="_blank" class="toret-plugins-admin-link">Toolkit</a>
            </td>
            <td>
                Přidejte do e-shopu nové funkce, které usnadní správu a pomůžou vyhovět zákonům. Plugin pomáhá s plněním požadavků spotřebitelského zákona, zákona o cenách i Nařízení o digitálních službách (DSA) a přidává i další užitečné funkce.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/toolkit/?utm_source=administrace&utm_medium=ppd&utm_campaign=toolkit" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/velkoobchod/?utm_source=administrace&utm_medium=ppd&utm_campaign=velkoobchod" target="_blank" class="toret-plugins-admin-link">Velkoobchod</a>
            </td>
            <td>
                Plugin pro velkoobchodní prodej ve WooCommerce. Přidává funkce registrace velkoodběratele, nastavení globálních i individuálních velkoobchodních slev, podmínek nákupu a zobrazení velkoobchodních cen.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/velkoobchod/?utm_source=administrace&utm_medium=ppd&utm_campaign=velkoobchod" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <tr>
            <td>
                <a href="https://toret.cz/produkt/vokativ/?utm_source=administrace&utm_medium=ppd&utm_campaign=ceske-osloveni" target="_blank" class="toret-plugins-admin-link">České oslovení</a>
            </td>
            <td>
                Oslovujte své zákazníky správně – 5. pádem. Plugin automaticky upravuje oslovení zákazníka v e-mailu a sekci Můj účet.
            </td>
            <td class="toret-plugins-admin-last">
                <a href="https://toret.cz/produkt/vokativ/?utm_source=administrace&utm_medium=ppd&utm_campaign=ceske-osloveni" target="_blank" class="toret-plugins-admin-buy">Koupit</a>
            </td>
        </tr>

        <?php /*        KONEC    */ ?>
    </tbody>
</table>



    </div>

    <h2>Diagnostika</h2>
    <p>V případě problému s pluginem si prosím pomocí tlačítka Stáhnout vygenerujte report a pošlete jej na <a href="mailto:podpora@toret.cz">podpora@toret.cz</a>.</p>
    <p>Pro detailnější diagnostiku problému je dobré poslat i chyby z WordPress debug nástroje. Návod jak jej aktivovat najdete v článku <a href="https://www.wplama.cz/jak-u-wordpressu-aktivovat-debug/" target="_blank">Jak u WordPressu aktivovat debug</a>.</p>
    <button id="toret-copy"><span class="dashicons dashicons-download"></span> Stáhnout</button>
    <div id="toret-plugins-admin-diag">
     
        ***************************************<br />
        Toret - Diagnostika<br />
        ***************************************<br /><br />

        WEB: <?php echo site_url(); ?><br />
        Plugin DIR: <?php echo WP_PLUGIN_URL; ?><br /><br />

        PHP: <?php if( function_exists('phpversion') ){ echo phpversion(); } ?><br />
        SOAP: <?php if (extension_loaded('soap')) { echo 'ACTIVE'; }?><br />
        CURL: <?php if (function_exists('curl_version')) { echo 'ACTIVE'; }?><br /><br />

        Woocommerce: <?php         $check = true;

                    if ( function_exists( 'is_multisite' ) && is_multisite() ) {
                        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                            $check = false;
                        }
                    } else {
                        if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                            $check = false;
                        }
                    }

                    if ( $check === true ) { echo WC_VERSION; }?><br />
        Wordpress: <?php echo bloginfo('version');?><br /><br />

        ***************************************<br />
        Plugin list<br />
        ***************************************<br /><br />

        <?php

        $plugins = get_option( 'active_plugins' );
        $html = '';

        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        foreach ( $plugins as $plugin ) {
        $plugin_data = get_plugin_data( ABSPATH . 'wp-content/plugins/' . $plugin );
        ?><?php echo $plugin_data['Name']; ?>  - <?php echo $plugin_data['Version']; ?>  - <?php echo $plugin_data['Author']; ?>  - <?php echo $plugin_data['TextDomain']; ?> <br />

        <?php
        }

        ?>

        <br /><br />

        ***************************************<br />
        Plugin Detail<br />
        ***************************************<br /><br />

        <?php do_action('toret_plugins_diag'); ?>

    </div>

</div>


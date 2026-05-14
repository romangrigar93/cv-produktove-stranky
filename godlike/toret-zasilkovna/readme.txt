=== WooCommerce Zasilkovna ===
Tested up to: 6.9
Version: 8.4.23
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
WC requires at least: 6.7.0
WC tested up to: 10.5.2
Requires PHP: 7.4
Requires at least: 6.2

WooCommerce intergration plugin to connect to Zásilkovna services.

== Description ==
WordPress plugin WooCommerce Zásilkovna implementuje tuto službu do e-shopu. Zákazník si v pokladně může pohodlně vybrat pobočku, kam chce objednávku poslat. WooCommerce Zásilkovna načítá pobočky a objednávky automaticky odesílá systému Zásilkovny.

Nákupem produktu získáte jednu unikátní licenci, která může být aktivována na jedné WordPress instalaci.Licence není časově omezená a umožňuje automatické aktualizace pluginu.

== Instalace ==
* Plugin je možné nainstalovat pomocí FTP, nebo nahráním Zip souboru v administraci

= Minimální požadavky =
* WordPress 6.2 nebo vyšší
* PHP version 7.4 nebo vyšší
* MySQL version 5.0 nebo vyšší

== Changelog ==
<a href="https://toret.cz/produkt/woocommerce-zasilkovna/" target="_blank">Changelog</a>

= 8.4.23 =
* Přidána ochrana proti opakovanému odeslání balíku při hromadné akci

= 8.4.22 =
* Opraveno zaokrouhlení dobírky

= 8.4.21 =
* Opravena konverze měn

= 8.4.20 =
* Úprava textace v nastavení pluginu

= 8.4.19 =
* Opravena konverze měn

= 8.4.18 =
* Opraveno načtení dat dopravců. Při selhání volání se aktuální data nesmažou

= 8.4.17 =
* Opravena konverze měn

= 8.4.16 =
* Opraveno odeslání zásilky do systému Packeta

= 8.4.15 =
* Opravena konverze měn
* Opraveno odeslání emailu o chybě při odeslání balíku

= 8.4.14 =
* Opravena inicializace pluginu

= 8.4.13 =
* Opraveno vytvoření tabulky pro log pluginu

= 8.4.12 =
* Nově se pro vytvoření balíku použije jméno z fakturační adresy pokud chybí jméno v adrese doručovací

= 8.4.11 =
* Opraveno uložení nastavení

= 8.4.10 =
* Opraveno stažení štítků

= 8.4.9 =
* Opravena kompatibilita s blokovou pokladnou

= 8.4.8 =
* Opraveno zaokrouhlení dobírky v CZ v jiných měnách než CZK

= 8.4.7 =
* Opraveno vytvoření tabulek v DB

= 8.4.6 =
* Opraveno vytvoření logu

= 8.4.5 =
* Aktualizace podpory blokové pokladny

= 8.4.4 =
* Aktualizace podpory blokové pokladny

= 8.4.3 =
* Aktualizace podpory blokové pokladny
* Opraven hromadný tisk štítků

= 8.4.2 =
* Aktualizace překladu

= 8.4.1 =
* Opraveno použití kupónu na dopravu zdarma

= 8.4.0 =
* Nově možnost rozdělit zásilku podle sumy všech produktů v objednávce, nejdelšího produktu a hmotnosti

= 8.3.3 =
* Upraveno načítání skriptu Zásilkovny

= 8.3.2 =
* Opraveno zobrazení metaboxu

= 8.3.1 =
* Opravena chyba při kontrole rozměrů produktu

= 8.3.0 =
* Nově možnost nastavit fixní cenu dobírky v cizí měně

= 8.2.2 =
* Opravena kontrola dostupnosti dobírky v pokladně pro zvolenou dopravu

= 8.2.1 =
* Opravena chyba ve výpočtu ceny dopravy

= 8.2.0 =
* Nově možnost rozdělit zásilku podle sumy všech produktů v objednávce nebo nejdelšího produktu v objednávce

= 8.1.6 =
* Aktualizace knihoven

= 8.1.5 =
* Optimalizace načítání skriptů v pokladně

= 8.1.5 =
* Přidán filtr zasilkovna_status_cron_checked_order_ids

= 8.1.4 =
* Opraven výpis detailu zvolené pobočky na děkovné stránce
* Opravena kontrola zvolené pobočky v pokladně

= 8.1.3 =
* Uprave popisek v nastavení pluginu Hmotnost obalu

= 8.1.2 =
* Opraven cron pro aktualizaci stavu zásilky

= 8.1.1 =
* Opravena chyba při neexistující položce objednávky

= 8.1.0 =
* Nově podpora pluginu X-Currency

= 8.0.10 =
* Opravena inicializace pluginu

= 8.0.9 =
* Opravena chyba v nastavení pluginu

= 8.0.8 =
* Opraveno nastavení vyřazených stavů zásilky z automatické kontroly

= 8.0.7 =
* Opravena kontrola zvolené pobočky v pokladně

= 8.0.6 =
* Opraveno načítaní skriptů
* Skryto interní meta data dopravních metod Zásilkovny

= 8.0.5 =
* Opravena podpora blokové pokladny

= 8.0.4 =
* Aktualizace dostupných stavů zásilky

= 8.0.3 =
* Aktualizace knihoven

= 8.0.2 =
* Opraven výpočet ceny dobírky
* Opraveno nastavení pluginu
* Opraveno zobrazení metaboxu
* Opraveno stažení štítku v detailu objednávky

= 8.0.1 =
* Nově možnost povolit log pro zápis dat pobočky k objednávce

= 8.0.0 =
* Nově podpory blokové pokladny
* Nově možnost nastavit paušální cenu dopravy v cizí měně
* Opraveno vyloučení dopravních metod Zásilkovny v pokladně na úrovni kategorie produktu
* Aktualizace knihoven
* AKtualizace API
* Upraven vzhled logu pluginu

= 7.3.17 =
* Opravena chyba při tisku štítku
* Opraveno uložení nastavení pluginu

= 7.3.16 =
* Opraveno automatická aktualizace stavu zásilky

= 7.3.15 =
* Opraveno vypnutí dopravy na úrovni variabilního produktu

= 7.3.14 =
* Přidán filtr zasilkovna_order_data s parametry $package_data, $order a $is_claim
* Upravena potřebná oprávnění pro nastavení pluginu

= 7.3.13 =
* Oprava chyb

= 7.3.12 =
* Opravena lokalizace odkazu na sledování zásilky

= 7.3.11 =
* Opraveno rozdělení objednávky do více balíků

= 7.3.10 =
* Sjednocení stylu odkazu na sledování zásilky

= 7.3.9 =
* Opravena konverze měny

= 7.3.8 =
* Opraveno zobrazení dobírky v pokladně

= 7.3.7 =
* Opravena dostupnost dobírky při opětovné platbě objednávky

= 7.3.6 =
* Upraven odkaz na sledování zásilky v emailu
* Nově možnost zvolit umístění odkazu na sledéní zásilky v emailu

= 7.3.5 =
* Opraveno předání ceny dobírky do Zásilkovny

= 7.3.4 =
* Opraveno předání ceny dobírky do Zásilkovny

= 7.3.3 =
* Opraveno odeslání dat do Zásilkovny

= 7.3.2 =
* Opraveno načítání překladu pluginu

= 7.3.1 =
* Opraveno načítání překladu pluginu

= 7.3.0 =
* Nově možnost nastavit stav daně u dopravních metod Zásilkovny

= 7.2.8 =
* Opraveno odeslání dat do Zásilkovny

= 7.2.7 =
* Opraven výpočet ceny dopravy

= 7.2.6 =
* Opraven výpočet ceny dopravy

= 7.2.5 =
* Opraveno vytvoření štítku pro dopravce Zásilkovna

= 7.2.4 =
* Opraveno načtení překladu pluginu

= 7.2.3 =
* Opraveno uložení zvolené dopravní metody v pokladně

= 7.2.2 =
* Opraveno ukládání nastavení pluginu

= 7.2.1 =
* Opraven tisk štítku z detailu objednávky

= 7.2.0 =
* Nově možnost automaticky zaokrouhlit hodnotu dobírky balíku na násobky 5 při odeslání do Maďarska

= 7.1.1 =
* Opraveno zobrazení dopravní metody v pokladně, pokud je cena dopravy nulová
* Opraveno zobrazení dobírky v pokladně, pokud je cena dobírky nulová

= 7.1.0 =
* Nově možnost nastavit vlastní ikonu pro tlačítko výběru pobočky v pokladně
* Opraven překlad
* Opraveno zobrazení akčních tlačítek u objednávky

= 7.0.0 =
* Nově rozdělení dopravců Zásilkovny do jednotlivých dopravních metod
* Nově možnost nastavit daňovou třídu poplatku za dobírku,
* Nově se jazyk pluginu přizpůsobí jazyku přihlášeného uživatele
* Nově podpora pro kupón s dopravou zdarma
* Optimalizace nastavení pluginu
* Opraveno skrytí dobírky v pokladně při překročení maximální hodnoty objednávky
* Opraveno ukládání nastavení pluginu

= 6.10.4 =
* Opravena kontrola dostupnosti objednávky při tvorbě odkazu na sledování zásilky
* Přidán filtr tzas_customer_details_order_info s paramtery $html a  $order_id

= 6.10.3 =
* Opraveno zobrazení tlačítek pro odeslání dat do Zásilkovny

= 6.10.2 =
* Opraveno nastavení dopravců v administraci

= 6.10.1 =
* Opravena kontrola dostupnosti dobírky pro zvolenou dopravní metodu

= 6.10.0 =
* Nově možnost vlastní poznámky k balíku

= 6.9.12 =
* Opraveno zobrazení tlačítek pro odeslání dat do Zásilkovny

= 6.9.11 =
* Opraveno zobrazení tlačítek pro odeslání dat do Zásilkovny

= 6.9.10 =
* Opraveno zobrazení dat pobočky v pokladně

= 6.9.9 =
* Opravena změna stavu objednávky na základě stavu zásilky

= 6.9.8 =
* Opraveno zobrazení dostupných dopravních metod pro odeslání do Zásilkovny pomocí Api

= 6.9.7 =
* Opraven maximální limit pro zobrazení dobírky v pokladně
* Opravena kompatibilita s FOX - Currency Switcher a dobírky

= 6.9.6 =
* Opravena kompatibilita s FOX - Currency Switcher
* Aktualizace API pro získání dopravních metod

= 6.9.5 =
* Opraveno zobrazení poplatku za dobírku

= 6.9.4 =
* Odstranění nepotřebných záznamů v databázi

= 6.9.3 =
* Opravena textace na děkovné stránce

= 6.9.2 =
* Opravena automatická změna stavu objednávky na základě stavu zásilky

= 6.9.1 =
* Opraveno ukládání nastavení cen dopravy

= 6.9.0 =
* Nově možnost skrýt dobírku v pokladně, pokud není podporována zvoleným výdejním místem
* Nově možnost skrýt ikonu dopravy u tlačítka pro výběr pobočky v pokladně
* Opraveno zobrazení možnosti výběru pobočky v pokladně

= 6.8.3 =
* Upraveno zobrazení možnosti výběru pobočky v pokladně
* Opraven zápis do logu

= 6.8.2 =
* Opraveno zobrazení poboček při omezení Z-BOXů
* Opraven typ štítku pro dopravce Zásilkovna

= 6.8.1 =
* Opraveno zobrazení tlačítka pro výběr pobočky v pokladně

= 6.8.0 =
* Nově možnost zvolit umístění tlačítka pro výběr pobočky a adresy v pokladně

= 6.7.7 =
* Opraveno zobrazení widgetu pro výběr adresy v pokladně

= 6.7.6 =
* Opravena lokalizace pluginu

= 6.7.5 =
* Optimalizace volání API

= 6.7.4 =
* Optimalizace volání API

= 6.7.3 =
* Opravena chyba při smazání zásilky

= 6.7.2 =
* Opravena chyba v administraci objednávky

= 6.7.1 =
* Optimalizace volání API

= 6.7.0 =
* Nově možnost omezit Z-BOX ve výběru pobočky na úrovni produktu, varianty nebo kategorie produktu

= 6.6.2 =
* Upravena kompatibilita s pluginy pro více měn
* Nově filtr zasilkovna_hd_check_error_text pro úpravu chybové hlášky při výběru adresy v pokladně

= 6.6.1 =
* Opravena kontrola zvolen pobočky v pokladně

= 6.6.0 =
* Nově možnost vynutit povinný výběr adresy z widgetu v pokladně

= 6.5.1 =
* Opraven výpočet daně u dobírky

= 6.5.0 =
* Nově možnost přidat text před a za číslo objednávky u reference balíku

= 6.4.10 =
* Přidán filtr zasilkovna_shipping_cost_final pro možnost úpravy ceny dopravy s parametry $cost, $country, $weight a $shipping_method

= 6.4.9 =
* Nově filtr formátu štítku toret_zasilkovna_ticket_format s parametry $format, $order_id a $id_dopravy

= 6.4.8 =
* Nově možnost smazat data balíku v podrobnostech objednávky

= 6.4.7 =
* Opravena odesílání dat do Zásilkovny

= 6.4.6 =
* Opraven odkaz na sledování zásilky do Dánska

= 6.4.5 =
* Opravena odesílání dat do Zásilkovny

= 6.4.4 =
* Upraveno načítání skriptů v pokladně

= 6.4.3 =
* Opravena změna pobočky v administraci objednávky

= 6.4.2 =
* Opravena možnost skrýt dopravce při překročení určité hodnoty košíků

= 6.4.1 =
* Opravena kompatibilita s pluginy pro více měn

= 6.4.0 =
* Nově možnost skrýt dopravce při překročení určité hodnoty košíků

= 6.3.12 =
* Přidán filtr zasilkovna_email_tracking_links pro zobrazení sledovacích odkazů v emailu

= 6.3.11 =
* Opraveno zobrazení dialogu pro výběr pobočky v administraci objednávky

= 6.3.10 =
* Upraveno načítání skriptů v pokladně

= 6.3.9 =
* Upraveno načítání skriptů v pokladně

= 6.3.8 =
* Opraveno automatické odeslání při změně stavu objednávky

= 6.3.7 =
* Opraveno zobrazení dialogu pro odeslaná balíku do Zásilkovny

= 6.3.6 =
* Přidána validace email adresy před odesláním balíku do Zásilkovny

= 6.3.5 =
* Opraveno opakované odeslání balíku do systému Zásilkovny

= 6.3.4 =
* Opraven tisk štítku
* Přidány možné stavy, při kterých se automaticky odešle zásilka

= 6.3.3 =
* Opravena kompatibilita s WooCommmerce

= 6.3.2 =
* Opravena kompatibilita s WooCommmerce

= 6.3.1 =
* Upraveno řazení štítků při hromadném tisku, pokud byl tisknut štítek dohromady se štítkem pro reklamačního asistenta

= 6.3.0 =
* Nově možnost smazat zásilku, pokud nebyla fyzicky podána

= 6.2.8 =
* Opravena kalkulace poplatku u dobírky

= 6.2.7 =
* Opraveno přiřazení výchozího formátu štítku

= 6.2.6 =
* Opraveno uložení cen podle hmotnosti v administraci

= 6.2.5 =
* Opravena podpora HPOS při ukládání dat objednávky  v administraci

= 6.2.4 =
* Opravena podpora HPOS

= 6.2.3 =
* Opravena chyba v administraci pluginu
* Optimalizace volání databáze

= 6.2.2 =
* Opravena chyba při výpočtu ceny dopravy

= 6.2.1 =
* Opravena kontrola stavu zásilky
* Opravena kompatibilita s PHP

= 6.2.0 =
* Nově možnost hromadně objednat reklamačního asistenta
* Nově možnost sledovat stav reklamačního asistenta
* Nově možnost tisknout štítky pro reklamačního asistenta společně se štítkem pro balík
* Nově možnost vytvořit reklamačního asistenta při vytváření balíku balíku
* Upravena URL pro načítání stavu zásilek (stará URL i nadále funkční)
* Sjednocení pravidel pro výpočet ceny dopravy dle hmotnosti, rozměrů a ceny objednávky
* Upraveno zobrazení notifikací o nezvolené pobočce v pokladně
* Opraveno zobrazení dobírky v pokladně

= 6.1.11 =
* Výchozí limit pro hmotnost zásilky upraven na 15kg

= 6.1.10 =
* Opraven tisk štítků pro dopravce Zásilkovna

= 6.1.9 =
* Opravena kompatibilita s Toret pluginy

= 6.1.8 =
* Opraveno css v administraci produktu

= 6.1.7 =
* Opravena kompatibilita výpočtu ceny dopravy se starší verzí pluginu

= 6.1.6 =
* Opravena kompatibilita s hromadných akcí
* Opraveno css v administraci produktu
* Opraven zápis do meta dat objednávky při opakovaném odesílání objednávky do Zásilkovny

= 6.1.5 =
* Opravena kompatibilita s pluginem Toret Toolkit

= 6.1.4 =
* Opraveno zobrazení dat pobočky v administraci objednávky

= 6.1.3 =
* Opraveno chybné volání pomocné funkce v administraci objednávky

= 6.1.2 =
* Opraveno chybné přiřazení dobírky k objednávkám do Maďarska

= 6.1.1 =
* Opravena změna dopravy v administraci objednávky
* Opravena změna ceny dopravy při změně dopravy v administraci
* Opravena kompatibilita s nižší verzí WooCommerce

= 6.1.0 =
* Nově podpora pluginu Booster Multi-Currency Switcher
* Nově možnost změnit dopravní metodu v administraci objednávky
* Nově možnost škálování ceny dopravy dle ceny objednávky nebo maximálního rozměru
* Nově podpora Aelia Currency Switcher
* Nově možnost zvolit aritmetické zaokrouhlení pro dobírku

= 6.0.4 =
* Opravena kompatibilita s pluginem Woo Doprava
* Opravena kompatibilita s pluginy pro překlad stránek

= 6.0.3 =
* Opraveno propsání adresy pobočky jako doručovací adresy

= 6.0.2 =
* Nově kontrola zvolené pobočky podle země doručení

= 6.0.1 =
* Opravena kompatibilita s High-Performance Order Storage

= 6.0.0 =
* Nově podpora High-Performance Order Storage
* Nově hook zasilkovna_packeta_disabled pro možnost omezit dopravu v pokladně
* Opravena kompatibilita s Toret pluginy

= 5.19.1 =
* Opraveno zobrazení dobírky v pokladně

= 5.19.0 =
* Nově se v pokladně nezobrazí dobírka, pokud není pro zvolenou dopravu vyplněna
* Opraven odkaz na sledování zásilky pro Slovinsko

= 5.18.7 =
* Opraveno předání hodnoty dobírky v případě měny HUF

= 5.18.6 =
* Opravena kritická chyba v PHP

= 5.18.5 =
* Opravena kompatibilita s PHP8.2

= 5.18.4 =
* Opravena URL k API
* Opraveno načítání dat dopravců pomocí API po aktivaci pluginu

= 5.18.3 =
* Oprava chyb v kódu

= 5.18.2 =
* Opravena kompatibilita s Toret Dropshipping

= 5.18.1 =
* Opravena kontrola zvolené pobočky

= 5.18.0 =
* Nově možnost v pokladně použít widget pro výběr adresy pro domácí doručení (pouze CZ a SK)

= 5.17.11 =
* Opraveno předání ID pobočky při odesílání dat balíku

= 5.17.10 =
* Oprava chyby při výpočtu daně dobírky, pokud není daň vyplněna

= 5.17.9 =
* Oprava chyby při výpočtu daně dobírky

= 5.17.8 =
* Optimalizace cronu pro aktualizaci stavu zásilek

= 5.17.7 =
* Opravena chyba při zjištování cílové země objednávky
* Opravena chyba při stažení štítků

= 5.17.6 =
* Opravena kompatibilita s Toret pluginy

= 5.17.5 =
* Nově možnost aktivovat kontrolu JS kontrolu, zda je zvolená pobočka v pokladně před odesláním objednávky

= 5.17.4 =
* Opravena kompatibilita s WooCommerce 7.5.0

= 5.17.3 =
* Opraven odkaz na sledování zásilky do Finska

= 5.17.2 =
* Přidán filtr zasilkovna_free_shipping_label pro možnost úpravy názvu dopravy s parametrem $label

= 5.17.1 =
* Opravena limitace hodnoty zásilky dle země doručení

= 5.17.0 =
* Nově se hodnota objednávky rozdělí rovnoměrně mezi jednotlivé balíky

= 5.16.0 =
* Nově možnost vynutit dopravu zdarma na úrovni produktu, varianty nebo kategorie produktu
* Opraven výpočet rozměrů balíku v administraci

= 5.15.5 =
* Opravena kompatibilita načítání skriptu pro hromadné akce s WooCommerce

= 5.15.4 =
* Nově ochrana dvojího podání zásilky při kliknutí na tlačítko v administraci

= 5.15.3 =
* Opravena chyba pi hromadném odeslání do Zásilkovny, pokud byla odeslána objednávka s dopravou mimo Zásilkovnu

= 5.15.2 =
* Nově možnost zakázat widget pro výběr pobočky při kliku na ikonu pomocí filtru zasilkovna_pickup_icon_class
* Nově možnost změnit nadpis pro zvolenou pobočku pomocí filtru zasilkovna_selected_point_label_filter

= 5.15.1 =
* Opravena kontrola rozměrů zásilky

= 5.15.0 =
* Nově možnost kontroly rozměrů objednávky v pokladně a před odesláním do systému Zásilkovny

= 5.14.2 =
* Opravena kontrola zvolené pobočky

= 5.14.1 =
* Opraveno zobrazení názvu dopravy v pokladně

= 5.14.0 =
* Nově možnost doplnit hodnotu balíku, pokud je hodnota objednávky nulová

= 5.13.2 =
* Opraveno zobrazení ikon v pokladně

= 5.13.0 =
* Nově možnost více desetinných míst v nastavení pluginu
* Nově možnost nastavit ikonu v pokladne pro jednotlivé dopravy

= 5.12.4 =
* Optimalizace cronu pro kontrolu stavu zásilky

= 5.12.3 =
* Opraven cron pro kontrolu stavu zásilky

= 5.12.2 =
* Opravena kontrola zvolené pobočky pro země mimo ČR a SK

= 5.12.1 =
* Vylepšena kompatibilita pro různé šablony pokladny

= 5.12.0 =
* Nově není na stránce Zaplatit objednávku dostupná možnost dobírky

= 5.11.8 =
* Opraven cron pro načítání dopravců

= 5.11.7 =
* Opraven odkaz na sledování zásilky

= 5.11.6 =
* Opravena konverze hmotnosti zásilky

= 5.11.5 =
* Opraven odkaz na sledování zásilky

= 5.11.4 =
* Opravena chyba při hromadném podání
* Opravena možnost podat objednávku bez zvolené pobočky

= 5.11.3 =
* Opraveno propojení s aplikací Toret WooCommerce Manager

= 5.11.2 =
* Opraven odkaz na sledování zásilky

= 5.11.1 =
* Opraveno odeslání do Zásilkovny, pokud v názvu společnsoti byl speciální znak

= 5.11.0 =
* Opravena konverze hmotnosti zásilky
* Nově možnost nastavit omezení hodnoty zásilky (pro účel pojištění) při překročení maximální povolené hodnoty
* Nově se skryje dobírka při překročení maximální povolené hodnoty dobírky

= 5.10.9 =
* Opraveno zobrazení zvolené pobočky v adminsitraci a děkovné stránce

= 5.10.8 =
* Opraveno odeslání do Zásilkovny, pokud v názvu obchodu byl speciální znak

= 5.10.7 =
* Opraven cron pro kontrolu stavu zásilky
* Opraven přepočet měn
* Opraveno automatické odesílání

= 5.10.6 =
* Opraven přepočet měn

= 5.10.5 =
* Opraveno zobrazení v podrobnostech objednávky

= 5.10.4 =
* Opraven převod měn

= 5.10.3 =
* Optimalizace Toret stránky v administraci
* Aktualizace propojení s aplikací Toret WooCommerce Manager, nově lze hromadně podat a tisknout (App verze 1.11.0)

= 5.10.2 =
* Nově nelze použít dobírku u objednávek rozdělených do více balíků
* Opravena kompatibilita s pluginem Weglot

= 5.10.1 =
* Opraveno propojení Toret WooCommerce Manager aplikací
* Opravena kompatibilita s pluginem Weglot

= 5.10.0 =
* Nově možnost převodu měny z USD na CZK
* Přidány země eurozóny pro převod měny na EUR
* Opraveny chyby při nevyplněných sazbách dopravy
* Opraven výpočet daně u dobírky a dopravy

= 5.9.4 =
* Opravena chyba při nevyplnění cen dopravce

= 5.9.3 =
* Opraven překlad pluginu

= 5.9.2 =
* Opravena možnost zakázat popup pro tisk štítku

= 5.9.1 =
* Oprava chyb při dotazu na neexistující nastavení

= 5.9.0 =
* Nově možnost rozdělit zásilku do více balíků
* Nově podpora PHP 8.0
* Nově podpora pluginu CURCY - Multi Currency for WooCommerce
* Opraveno zobrazení záložky Zásilkovna v nastavení produktu

= 5.8.14 =
* Oprava možnosti odesílat balík pomocí aplikace Toret WooCommerce Manager

= 5.8.13 =
* Oprava chyb v případě nedostupnosti potřebných dat

= 5.8.12 =
* Oprava kalkulace daně u dopravy

= 5.8.11 =
* Opravena kompatibilita se šablonami pokladny
* Nově se widget pro výběr pobočky otevře i po kliknutí na ikonu Zásilkovny

= 5.8.10 =
* Opravena kompatibilita se šablonami pokladny

= 5.8.9 =
* Opraveno načítání poboček při změně země

= 5.8.8 =
* Nově možnost skrýt záložku Zásilkovna z editace produktu, varianty produktu a kategorie

= 5.8.7 =
* Opravena url API knihovny

= 5.8.6 =
* Opravena podpora aktivace pluginu na multisite

= 5.8.5 =
* Opraveno opakované otevření widgetu pro výběr pobočky
* Opraven dialog pro výbeř formátu štítku
* Opraveno zobrazení textu na děkovné stránce

= 5.8.4 =
* Podání země z dopravní adresy pokud se liší od fakturační adresy
* Odstraní chyby na stránce se znovu zaplacením objednávky

= 5.8.3 =
* úprava zápisu do Zásilkovna logu

= 5.8.2 =
* Opravena kompatibilita s jQuery knihovnou

= 5.8.1 =
* Opravena kompatibilita automatického otevírání dialogu pro výběr pobočky s vícekrokovou pokladnou

= 5.8.0 =
* Nově možnost zvolit otevření dialogu pro výběr pobočky při volbě dopravy místo kliknutí na tlačítko

= 5.7.4 =
* Úprava hromadných akcí

= 5.7.3 =
* Oprava chyby při použití hromadných úprav v přehledu objednávek

= 5.7.2 =
* Aktualizace CZ, SK a PL překladů.
* Nově přidána možnost nastavení výchozího typu štítků pro Zásilkovnu a externí dopravce
* Nově možnost deaktivovat po-up okno pro tisk štítků
* Oprava proměnných

= 5.7.1 =
* oprava minimální verze WP

= 5.7.0 =
* Nově více formátů pro tisk štítku včetně výběru pozice štítku
* Nové nastavení pro funkci automatického načítání stavu zásilek
* Nově u objednávek na adresu se automaticky do Zásilkovny odesílá i pole Společnost
* Nově v detailu objednávky, e-mailu a na děkovné stránce se propisuje vybraná pobočka externího dopravce
* Úprava URL pro načítání dopravců (stará URL i nadále funkční)
* Přidána kompatibilita při použití Divi šablony pro pokladnu
* Drobné úpravy textace v administraci
* Aktualizace překladů CZ a SK

= 5.6.5 =
* úprava formátu štítku dopravce SK PACKETA HOME HD

= 5.6.4 =
* úprava Zásilkovna widgetu

= 5.6.3 =
* přejmenování funkce

= 5.6.2 =
* oprava chyby při hromadném tisku štítků

= 5.6.1 =
* úprava daně u příplatku za dobírku

= 5.6.0 =
* otestovaná kompatibilita s WordPress 5.8.1 a WooCommerce 5.7.1

= 5.5.3 =
* ošetření výpadků dopravců pro některé hostingy

= 5.5.2 =
* odstranení chyby - notice

= 5.5.1 =
* Změna načítání dopravce v administraci

= 5.5.0 =
* Možnost přidání hmotnosti obalového materiálu
* Povolení nastavení dopravy mimo státy EU
* Úprava stylů administrace
* Aktualizace EN, CZ a SK překladu

= 5.4.3 =
* oprava chyby v textu

= 5.4.2 =
* oprava zadávání ceny podle váhy

= 5.4.1 =
* vylepšení kompatibility

= 5.4.0 =
* úprava překladů
* úprava textů v administrace

= 5.3.2 =
* Oprava stránky toret plugins

= 5.3.1 =
* aktualizace PL překladu

= 5.3.0 =
* změna výchozího jazyku
* přidány překlady
* oprava zobrazenípoboček

= 5.2.5 =
* oprava kontgroly vybrané pobočky

= 5.2.4 =
* oprava podání zásilek přes hromadné tlačítko

= 5.2.3 =
* oprava chyby ve výpisu objednávek

= 5.2.2 =
* Oprava warning "max_weight"
* Odstranení chyby kdy nešel zobrazit detail objednávky
* Odstranení chyby kdy se získávala data košíku i když neexistoval
* Opravení kompatibility s Platiti
* oprava - PHP Warning: Undefined array key "plugins" in C:\xampp\apps\… …
* Změna výpočtu dopravy zdarma při použití kuponu
* Oprava podbarvení Hlavního nastavení
* Oprava ukládání nastavení změny stavu objednávky

= 5.2.1 =
* úprava ajax url

= 5.2.0 =
* filtrování výdejních míst

= 5.1.0 =
* změna fungování automatického odesílání do systému zásilkovny
* úprava konverze měn

= 5.0.0 =
* kompletní přepracování pluginu

= 4.3.5 =
* oprava ceny podle váhy při použití kg

= 4.3.4 =
* oprava několika php NOTICE

= 4.3.3 =
* možnost zadat cenu pro dopravu zdarma včetně DPH
* Oprava maximápní povolené hmotnosti
* úprava naččítání stavu objednávky ze zásilkovny

= 4.3.2 =
* přidán PL překlad
* přidány filtry "zasilkovna_create_ticket_xml" a "zasilkovna_claim_ticket_xml"

= 4.3.1 =
* změna stahování štítků

= 4.3.0 =
* přidána možnost zadávat ceny včetně DPH

= 4.2.2 =
* oprava vypínání dopravy podle váhy

= 4.2.1 =
* možnost vypnout zásilkovnu podle států
* přidány překlady
* možnost zadat nulu do ceny

= 4.2.0 =
* Doplněn opravený SK překlad
* upraveny CSS styly
* možnost zadat Váhu před odesláním do zásilkovny

= 4.1.7 =
* Doplněn opravený HU překlad

= 4.1.6 =
* změna zpracování štítků

= 4.1.5 =
* oprava odesílání do zásilkovny

= 4.1.4 =
* oprava překladů
* oprava dobírky
* úprava emailů
* úprava štítků

= 4.1.3 =
* přidán požadavek na verzi PHP 7.2

= 4.1.2 =
* oprava příplatku za dobírku

= 4.1.1 =
* přidána podpora vlastních čílek objednávek

= 4.1.0 =
* kompletní přepracování jádra
* přidání nových fcí tisku štítků a nastavení ceny
* minimální verze php 7.2

= 4.0.14 =
* Oprave kontroli knihovny soap

= 4.0.13 =
* zlepšení fce pro tisk štítů

= 4.0.12 =
* zlepšení kompatibility css

= 4.0.11 =
* oprava zablokování způsobu dopravy pro produkt s variantami

= 4.0.10 =
* odstranění dvojité deklarace fce

= 4.0.9 =
* odstranění dvojité deklarace fce

= 4.0.8 =
* Přidána možnost vypnutí zobrazení stavu zásilkovny

= 4.0.7 =
* Ošetření váhy při použití gramů - odesílání ticketu

= 4.0.6 =
* Ošetření váhy při použití gramů - widget

= 4.0.5 =
* Odstranění překlepu

= 4.0.4 =
* Přidán filtr na vlastní formát hromadného stisku štítků apply_filters('zasilkovna_group_print_format', $format);

= 4.0.3 =
* Oprava SK překladu

= 4.0.2 =
* Doplnění překladů DE a RO

= 4.0.1 =
* Oprava dat pobočky při opakované objednávce

= 4.0.0 =
* Vydání BETA verze

= 3.2.6 =
* Doplnění informačních textů

= 3.2.5 =
* Přidání překladů SK a PL

= 3.2.4 =
* Přidání překladů HU a EN
* Zlepšení kompatibility s WPML

= 3.2.3 =
* zlepšení kompatibility s WPML

= 3.2.2 =
* aktulizace možností doručení
* oprava překladu
* přidány filtry $html = apply_filters( 'zasilkovna_vystup_html', $html, $zasilkovna_mista[$zasilkovna_id] ); a $html = apply_filters( 'zasilkovna_vystup_html_email', $html, $zasilkovna_mista[$zasilkovna_id] );

= 3.2.1 =
* změna váhy doručení Bratislava

= 3.2.0 =
* změna kontroly licence
* oprava váhy v zásilkovně
* zobrazení "Zdarma" při ceně 0

= 3.1.0 =
* přidání možnosti vypnout dopravu v závislosti na produktu

= 3.0.8 =
* oprava tlačítka vybrat místo vyzvednutí

= 3.0.7 =
* oprava zobrazení dopravců podle váhy produktu

= 3.0.6 =
* oprava překlepu

= 3.0.5 =
* oprava aktivace dopravců

= 3.0.4 =
* přidáno ověření věku

= 3.0.3 =
* Oprava Call to a member function get() on null

= 3.0.2 =
* Přesunutí upozornění na aktualizaci 3.X

= 3.0.1 =
* Oprava zobrazování dopravců

= 3.0.0 =
* Přidání dalších zemí a dopravců
* Změna zadávání nových zemí a dopravců
* Přidána kontrola zda dopravce přijímá dobírku

= 2.0.18 =
* Zlepšení kompatibility s více stránkovou pokladnou

= 2.0.17 =
* Oprava ukládání cen

= 2.0.16 =
* Úprava textů v administraci

= 2.0.15 =
*Přidání filtru "dobirka_order_status"

= 2.0.14 =
*Drobná úprava textu

= 2.0.13 =
*Přidání filtru na úpravu dopravy zdarma 'zasilkovana_free_shipping_filter'

= 2.0.12 =
*Přidání filtru na nastavení daňové třídy u dobírky 'zasilkovna_taxclass_dobirka'

= 2.0.11 = 
* Přidání filtru na úpravu váhy 'zasilkovna_weigh_koef'

= 2.0.10 =
* Úprava filtru pro opakované odeslání

= 2.0.9 =
* Upravy informacnich textu v pluginu

= 2.0.8 =
* Změna získání použité platební metody pro data ticketu
* Funkce zasilkovna_get_cod_value nově přijímá 4 parametry - $s_method, $price, $country, $order
* Nový filter pro hodnotu dobírky v ticketu - apply_filters( 'zasilkovna_dobirka_shipping_value', $cod, $price, $country, $order );

= 2.0.7 =
* Přidání filtru na value při odeslání zásilky $price = apply_filters( 'zasilkovna_ticket_value', $price, $order, $zasilkovna_option, $zasilkovna_id );

= 2.0.6 =
* rozšíření inicializace widgetu o definici vybrané země
* nastavení typeof u kontroly undefined
* úprava v platbě na účet - kompatibilita s WooCommerce 3+
* oprava indefined index form v administraci

= 2.0.5 =
* oprava deaktivace konverze měn
* úprava kontroly virtuálního produktu v košíku

= 2.0.4 =
* úprava kontroly dostupnosti platební metody PNU

= 2.0.3 =
* Přidána PLN měna pro Polsko
* Doplněna možnost deaktivace konverze měn - používejte pouze v případě, že máte v Zásilkovně povolenou konverzi, nebo máte účty v příslušných měnách, viz. - https://www.zasilkovna.cz/eshopy/konverze-men

= 2.0.2 =
* Upraveny ulr tlačítek v administraci, aby napřesměrovávaly na první stranu výpisu objednávek

= 2.0.1 =
* Odstranění chybového hlášení, pokud je log prázdný

= 2.0.0 =
* Nový widget pro výběr poboček
* Doplněny dopravy pro stávající země
* Přidány dopravy a nové země
* Přidána možnost nastavit dopravu zdarma pro většinu doprav
* Rozšířeny filtry pro stávající a nové dopravy, viz. dokumentace https://documentation.toret.cz/zasilkovna/pouziti-filtru/
* Úprava a refaktoring stávajícího kódu
* Odstranění souboru notify_log.txt
* Přidáno interní logování komunikace se Zásilkovnou
* Přidán metabox pro výpis logu pro jednotlivou objednávku
* Přidána stránka s kompletním výpisem logu
* Vytvořen videonávod pro aktuální verzi pluginu https://www.youtube.com/watch?v=3J6NEVblFsg
* Rozšířená stávající dokumentace https://documentation.toret.cz/zasilkovna/
* Soubor pro aktualizaci poboček byl doplněn o parametry, umožňující aktualizovat pobočky Paczkomatů a Nova Postha

= 1.6.4 =
* Nový filtr cesko_nejlevnejsi_doruceni_shipping_cost

= 1.6.3 =
* Doplněna kontrola existence WooCommerce i pro multisite
* Upraveno vkládání souboru s kontrolou licence

= 1.6.2 =
* Doplněna podpora vlastních stavů objednávek v nastavení dobírky a platby na účet
* Upraveny zavržené metody v platbě na účet
* Doplněny odkazy na dokumentaci a nastavení ve výpisu pluginů

= 1.6.1 =
* Odstraněny nepotřebné filtry zasilkovna_cz_cost, zasilkovna_sk_cost a zasilkovna_hu_cost
* Pro zásilkovnu je nyní dostupný filtr zasilkovna_shipping_cost, který je použit pro všechny země
* Filtry z minulé aktualizace byly přemístěny tak, aby je bylo možné použít i pro "vypnutí" konkrétní dopravy. Více v dokumentaci

= 1.6.0 =
* Přidány filtry pro každnou platební metodu, umožňující ovlivnit výslednou cenu
* zasilkovna_cz_cost - Zásilkovna CZ
* zasilkovna_sk_cost - Zásilkovna SK
* zasilkovna_hu_cost - Zásilkovna HU
* zasilkovna_cp_cz_cost - Česká Pošta CZ
* zasilkovna_intime_cz_cost - In Time CZ
* zasilkovna_dpd_cz_cost - DPD CZ
* zasilkovna_express_brno_cz_cost - Expresní doručení Brno
* zasilkovna_express_praha_cz_cost - Expresní doručení Praha
* zasilkovna_express_ostrava_cz_cost - Expresní doručení Ostrava
* zasilkovna_austria_at_cost - Rakouská Pošta
* zasilkovna_germany_de_cost - Německá Pošta
* zasilkovna_hungary_hu_cost - Maďarská Pošta
* zasilkovna_dpd_hu_cost - DPD HU
* zasilkovna_transoflex_hu_cost - Transoflex HU
* zasilkovna_poland_pl_cost - Polská Pošta
* zasilkovna_dpd_pl_cost - DPD PL
* zasilkovna_slovensko_na_adresu_cost - Slovensko Doručení na adresu
* zasilkovna_slovenska_posta_cost - Slovenská Pošta
* zasilkovna_expres_bratislava_cost - Expresní doručení Bratislava
* zasilkovna_slovensko_kuryr_cost - Slovensko Kurýr
* zasilkovna_fan_ro_cost - Rumunsko FAN
* zasilkovna_dpd_ro_cost - DPD RO
* zasilkovna_dpd_bl_cost - DPD BL

= 1.5.9 =
* Přidán reklamační asistent do administrace objedávek

= 1.5.8 =
* Doplněna kompatibilita s WooCommerce 3.4.0

= 1.5.7 =
* Id objednávky předávané do Zásilkovny změněnno na $order->get_order_number(); - comaptibilita s WOSN pluginem
* Přidán filtr zasilkovna_order_number pro možnost ovlivnit id objednávky předané do Zásilkovny

= 1.5.6 =
* Opraveno ukládání hodnot kurzů měn

= 1.5.5 =
* Oprava chyby s nulovou cenou pro CZ Zásilkovnu
* Oprava neukládání prázdných polí v nastavení

= 1.5.4 =
* Barcode v administraci změněn na odkaz vedoucí na sledování zásilky na Zásilkovně
* Přidána nová třída pro načtení poboček Zásilkovna
* Přidány pobočky zásilkovny pro Polsko, Maďarsko a Rumunsko
* Upraven vzhled administrace, pro lepší orientaci v zadávání cen doprav
* Tlačítko pro manuální odeslání do Zásilkovny změněno na ikonu
* Přidáno tlačítko pro stažení štítku zásilky
* Přidáno nemazání vybrané pobočky Zásilkovny, při změně platební metody
* Přidána možnost uložení překladu do složky languages/zasilkovna, ten pak nebude přepisován aktualizací
* Doplněna třída s notice pro kontrolu existence WooCommerce, aktivní licence, cURL a Soap
* Přidána třída pro generování fee pro dobírku
* Přidán email administrátorovi, pokud dojde k selhání vytvoření zásilky
* Pro vyzvedutí zásilky na pobočce Zásilkvny je nyní možné nastavit cenu, od které bude zdarma ( nezávislé na nastavení Dopravy Zdarma )

= 1.5.3 =
* Přidána kontrola existence indexu vybrané dopravní metody v metodě zasilkovna_select_option

= 1.5.2 =
* Odstranění funkce zasilkovna_wc_remote_gateway, která skrývala původní bankovní převod a COD
* Úprava inicializace výchozích hodnot u PNU platební metody

= 1.5.1 =
* V metodě set_fee_by_dobirka_free_shipping byla změněna kontrolovaná hodnota pole na show_cod

= 1.5.0 =
* Doplněna kontrola existence dopravy pro objednávku

= 1.4.9 =
* Úprava html kódu a řetězce pro překlad v metodě zasilkovna_customer_email_info

= 1.4.8 =
* Úprava kódu tabulky pro zobrazení vybrané pobočky a odkau ke sledování na děkovné stránce

= 1.4.7 =
* Úprava kontroly vyřazeného produktu v helper třídě

= 1.4.6 =
* Změna způsobu načítání xml souboru s pobočkami - nově curl

= 1.4.5 =
* Oprava získání statusu objednávky pro verze 2.6+

= 1.4.4 =
* Zaktualizovány třídy pro kompatibilitu s WooCommerce 3.0.+

= 1.4.3 =
* Opraven undefined index zasilkovna-cr-dobirka

= 1.4.2 =
* Přidán výpis pobočky Zásilkovny v detailu objednávky v administraci

= 1.4.1 =
* Přidána no js kontrola vybrané pobočky na pokladně
* Přidána kompatibilita kontroly doručovací adresy pro WooCommerce 3.0.+

= 1.4.0 =
* Přidána kontrola aktivní WooCommerce
* Dobírka upravena pro kompatibilitu s WooCommerce 3.0.+
* Platba na účet upravena pro kompatibilitu s WooCommerce 3.0.+
* Přidána funkce toret_get_customer_country() pro zjištění zadané země zákazníka, s kompatibilitou pro WooCommerce 3.0.+
* Doplněny třídy pro kompatibilitu s WooCommerce 2.6 až 3.0+
* Doplněna kompatibilita pro WooCommerce 3.0.+


= 1.3.9 =
* Oprava undefined old_rate na pokladně
* Doplněno nastavení výchozích měn, pro různé země, viz. dokumentace https://zasilkovna.toret.cz/nastaveni-men-zasilek/
* Přidána registrace stringů pro WPML, pro názvy doprav. Po každé změně, se musí v překladu stringů ve WPML, položky upravit

= 1.3.8 =
* Opraveno volání metody __get v dobírce
* Odstraněn výpis pole v administraci
* Přidáno nastavení do úpravy chování dopravy zdarma - skryje dopravu zdarma a zanechá ceny beze změny. Důvod - možnost kombinovat s nastavením pluginu Woo Doprava

= 1.3.7 =
* Do dobírky byl doplněn filtr woo_doprava_is_virtual_product_in_cart
* Platba na účet byla sjednocena s pluginem Woo Doprava pro vzájemnou kompatibilitu
* Pro platbu na účet, lze nastavit stav objednávky při vytvoření
* Přidána mezera za dvojtečku v informaci o pobočce v emailu
* Oprava zobrazení ikony Zásilkovny v pokladně
* Nastavení ikony Dopravní metody bylo přesunuto z nastavení dopravy, do nastavení pluginu
* Možnost vypnutí odesílání zásilek do systému Zásilkovny
* Upraven výpis pobočky Zásilkovny na děkovné stránce a v mém účtu
* Ošetřeno zobrazování tlačítek pro odeslání do Zásilkovny ve výpisu objednávek, pokud není doprava Zásilkovna

= 1.3.6 =
* Oprava příplatku za dobírku pro Doručení na adresu SK

= 1.3.5 =
* Možnost nastavení chování dopravy zdarma - výchozí/vše zdarma

= 1.3.4 =
* Aktualizace překladových souborů a načítání jazykové domény

= 1.3.3 =
* Možnost nastavení stavu objednávky, po zaplacení na dobírku

= 1.3.2 =
* Doplněno method_title pro zasilkovna_shipping class

= 1.3.1 =
* Oprava načítání názvu metody dobírce

= 1.3.0 =
* Oprava ukládání názvu metody pro zobrazení na pokladně

= 1.2.9 =
* Změna generování zásilek
* Úprava přepočítávání měn
* Kompatibilita s pluginy WooCommerce Currency Switcher a WooCommerce Multilingual
* Informace o důvodu neodeslání zásilky do systému Zásilkovny v detailu objednávky (administrace)
* Zobrazování čísla zásilky (Barcode) ve výisu objednávek
* Zobrazování odkazu na sledování zásilky Track and Trace
* Možnost manuálního odeslání objednávky do Zásilkovny, po opravě dat objednávky
* Kompletní refactoring kódu a přidání nových tříd
* Odstranění drobných chyb a jejich výpisů
* Přidán odkaz na sledování zásilek do klientského emailu a detailu objednávky v účtu zákazníka

= 1.2.8 =
* Změna odesílání dat z Soap na REST

= 1.2.7 =
* Upravena detekce dopravy zdarma, pro kompatibilitu s WooCommerce 2.6+

= 1.2.6 =
* Upravena dopravní metoda, pro kompatibilitu s WooCommerce 2.6
* Přidána kompatibilita s Shipping Zones

= 1.2.5 =
* Doplněny další možnosti doprav

= 1.2.4 =
* Přidána možnost, vypnout DPH, pro příplatek za dobírku

= 1.2.3 =
* Přidána defaultní položka do výběru poboček
* Vložena kontrola výběru pobočky na pokladně.

= 1.2.2 =
* Přidán notifikační log
* Přidáno SK logo pro slovenskou Zásilkovnu
* Seznam poboček Zásilkovny byl rozdělen na Slovenskou a Českou část
* Přidána funkce zasilkovna_get_cod_value
* Přidána funkce zasilkovna_log
* Zprovozněno přidání adresy pobočky Zásilkovny do emailu.

= 1.2.1 =
* Oprava detekce země a měny, pokud je zásilka potvrzena z administrace eshopu

= 1.2.0 =
* Doplnění nových druhů doprav a zemí, které Zásilkovna nově podporuje
* Úprava vzhledu administrace

= 1.1.2 =
* Oprava drobných syntax chyb

= 1.1.1 =
* Přidána možnost výběru momentu odeslání zásilky do Zásilkovny
* Přidán jazykový po soubor pro překlad


= 1.1.0 =
* Kompletní přepsání struktury pluginu
* Přidání licenčního klíče
* Přidání automatické aktualizace

= 1.0.0 =
* Vydání pluginu
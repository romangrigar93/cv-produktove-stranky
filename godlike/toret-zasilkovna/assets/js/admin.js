jQuery('document').ready(function () {
    /**
     * Collapsible in product, variation and category tab
     */
    jQuery(document).on('change', '.tzas-weight-type-selection', function () {
        let target = jQuery(this).find(":selected").data('target');
        let alltables = jQuery(this).data('alltables');
        let single = jQuery(this).data('single');

        if (jQuery(this).find(":selected").val() === 'single') {
            jQuery('.' + single).show();
        }

        jQuery('.' + alltables).hide()
        if (target !== 'none') {
            jQuery('#' + target).show();
            jQuery('#' + target).next('input').show()
        }
    });

    jQuery(document).on('change', '.tzas-fee-type-selection', function () {
        let target = jQuery(this).find(":selected").data('target');
        let alltables = jQuery(this).data('alltables');
        let single = jQuery(this).data('single');

        if (jQuery(this).find(":selected").val() === 'single') {
            jQuery('.' + single).show();
        } else {
            jQuery('.' + single).hide();
        }

        jQuery('.' + alltables).hide()
        if (target !== 'none') {
            jQuery('.' + target).show();
            jQuery('.' + target).next('input').show()
        }
    });

    jQuery(document).on('change', '#multipackage_source', function () {
        let value = jQuery(this).val();
        if(value.includes('weight')){
            jQuery('#multipackage_treshold_weight_row').show()
        }else{
            jQuery('#multipackage_treshold_weight_row').hide();
        }
        if(value.includes('longest')){
            jQuery('#multipackage_treshold_longest_row').show()
        }else{
            jQuery('#multipackage_treshold_longest_row').hide();
        }
        if(value.includes('sum')){
            jQuery('#multipackage_treshold_sum_row').show()
        }else{
            jQuery('#multipackage_treshold_sum_row').hide();
        }
        let label = zasilkovna_admin.multiple_packages_label[value]
        jQuery('label[for="multipackage_treshold"]').text(label);

    });

    /**
     *
     */
    jQuery(document).on("click", '#zasilkovna-change-shipping', function (e) {

        e.preventDefault()

        let orderid = jQuery(this).data('orderid');
        let country = jQuery(this).data('country');
        let method = jQuery('#tzas-change-method').find(":selected").val();

        if (method !== '-') {

            let data = {
                action: 'toret_zasilkovna_change_shipping',
                orderid: orderid,
                method: method,
                country: country,
            }

            jQuery.post(ajaxurl, data, function (response) {
                location.reload();
            })
        }
    });

    jQuery(document).on("click", '.tzas-hmsmazat,.tzas-dmsmazat,.tzas-prsmazat', function (e) {
        e.preventDefault();
        let co = jQuery(this).data('value');
        jQuery('#' + co).remove();
    });

    jQuery(document).on("click", '.tzas-pridathm,.tzas-pridatdm,.tzas-pridatpr', function (e) {
        e.preventDefault();
        pridatTypeZas(jQuery(this));
    });

    function pridatTypeZas(element) {
        let co = element.data('value');
        let stat = element.data('stat');
        let type = element.data('type');
        let pricetype = element.data('pricetype');
        let slug = element.data('slug');
        let zasvl = element.data('zasvl');
        let klic = Math.floor((Math.random() * 1000) + 1) + 150;
        let html = '<tr id="' + zasvl + type + klic + '"><td><input type="number" min="0" step="0.01" name="' + slug + '-' + type + 'd-' + stat + '[]" value="" /></td><td><input type="number" min="0" step="0.01" name="' + slug + '-cena' + pricetype + '-' + stat + '[]" value="" /></td><td><a href="" class="tzas-' + type + 'smazat toret-delete-limit" data-value="' + zasvl + type + klic + '">' + zasilkovna_admin.delete + '</a></td></tr>';
        jQuery('#' + co).append(html);
    }

    //odstranení příplatku váhy - řádek
    jQuery(document).on("click", '.tzas-feesmazat', function (e) {
        e.preventDefault();
        let co = jQuery(this).data('value');
        jQuery('#' + co).remove();
    });
    //přidání řádku pro vlastní příplatek
    jQuery(document).on("click", '.tzas-pridatfee', function (e) {
        e.preventDefault();
        let co = jQuery(this).data('value');
        let stat = jQuery(this).data('stat');
        let slug = jQuery(this).data('slug');
        let zasvlfee = jQuery(this).data('zasvlfee');
        let klic = Math.floor((Math.random() * 1000) + 1) + 150;

        let html = '<tr id="' + zasvlfee + klic + '"><td><input type="number" min="0" step="0.01" name="' + slug + '-feed-' + stat + '[]" value="" /></td><td><input type="number" min="0" step="0.01" name="' + slug + '-cenafee-' + stat + '[]" value="" /></td><td><a href="" class="tzas-feesmazat toret-delete-limit" data-value="' + zasvlfee + klic + '">' + zasilkovna_admin.delete + '</a></td></tr>';

        jQuery('#' + co).append(html);
    });
});


jQuery(document).ready(function () {
    let $ = jQuery;
    if ($('.set_custom_images').length > 0) {
        if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $(document).on('click', '.set_custom_images', function (e) {
                e.preventDefault();
                let button = $(this);
                let id = button.prev();
                wp.media.editor.send.attachment = function (props, attachment) {
                    id.val(attachment.url);
                };
                wp.media.editor.open(button);
                return false;
            });
        }
    }
});


jQuery(document).ready(function () {
    let $ = jQuery;
    if ($('.choose_storage_file').length > 0) {
        if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $(document).on('click', '.choose_storage_file', function (e) {
                e.preventDefault();
                let button = $(this);
                let id = $('#tzas_invoice_file');
                let hidden = $('#tzas_invoice_file_id');
                wp.media.editor.send.attachment = function (props, attachment) {
                    id.val(attachment.url);
                    hidden.val(attachment.id);
                };
                wp.media.editor.open(button);
                return false;
            });
        }
    }

    jQuery('.zasilkovna-upload-storage').on('click', function (e) {

        e.preventDefault();

        let fileid = jQuery(this).data('fileid');
        let orderid = jQuery(this).data('orderid');
        let type = jQuery(this).data('type');

        let data = {
            action: 'toret_save_storage_file',
            fileid: fileid,
            orderid: orderid,
            type: type,
        }

        jQuery.post(ajaxurl, data, function (response) {
            location.reload();
        })

    });

    let mybutton = document.getElementById("to-top-button");

    window.onscroll = function () {
        scrollFunction()
    };

    function scrollFunction() {

        if (mybutton)
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                mybutton.style.display = "block";
            } else {
                mybutton.style.display = "none";
            }
    }

    jQuery(document).on("click", '#to-top-button', function (e) {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    });

    jQuery(document).on("click", '.copy-link-button', function (e) {
        const link = this.getAttribute('data-link');
        navigator.clipboard.writeText(link);
    });


    /**
     *
     */
    if (jQuery('.tzas-flr-checkbox').length > 0) {
        jQuery('.tzas-flr-checkbox').each(function () {
            if (this.checked) {
                jQuery('.' + jQuery(this).data('target')).show();
            } else {
                jQuery('.' + jQuery(this).data('target')).hide();
            }
        });
    }

    jQuery(document).on("change", '.tzas-flr-checkbox', function (e) {
        if (this.checked) {
            jQuery('.' + jQuery(this).data('target')).show();
        } else {
            jQuery('.' + jQuery(this).data('target')).hide();
        }
    });

    if (jQuery('.tzas-flr-cod-checkbox').length > 0) {
        jQuery('.tzas-flr-cod-checkbox').each(function () {
            if (this.checked) {
                jQuery('.' + jQuery(this).data('target')).show();
            } else {
                jQuery('.' + jQuery(this).data('target')).hide();
            }
        });
    }

    jQuery(document).on("change", '.tzas-flr-cod-checkbox', function (e) {
        if (this.checked) {
            jQuery('.' + jQuery(this).data('target')).show();
        } else {
            jQuery('.' + jQuery(this).data('target')).hide();
        }
    });

    /**
     *
     */
    const buttonClass = '.toret-save-btn';
    const storageKey = 'toretLastClickedButton';

    $(buttonClass).on('click', function () {
        const buttonIndex = $(buttonClass).index(this);
        localStorage.setItem(storageKey, buttonIndex);
    });

    const lastClickedIndex = localStorage.getItem(storageKey);
    if (lastClickedIndex !== null) {
        const targetButton = $(buttonClass).eq(lastClickedIndex);
        if (targetButton.length) {
            // Najdeme nejbližší předcházející <h2> nad cílovým tlačítkem
            //const nearestHeading = targetButton.prevAll('h2').last();
            const nearestHeading = targetButton.parent().parent().children('.tzas-settings-box-header').children('h3');

            if (nearestHeading.length) {
                // Posun na <h2> tak, aby byl na horním okraji obrazovky
                $('html, body').animate({scrollTop: nearestHeading.offset().top-50}, 700);
            } else {
                // Pokud <h2> neexistuje, zůstane původní logika
                if (targetButton.offset().top > window.innerHeight) {
                    $('html, body').animate({scrollTop: targetButton.offset().top - (window.innerHeight)}, 700);
                }
            }
            targetButton.focus();
        }
        localStorage.removeItem(storageKey);
    }

});


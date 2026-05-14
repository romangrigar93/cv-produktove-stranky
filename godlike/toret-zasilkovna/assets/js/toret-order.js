// kontrola rozměrů
function DimensionCheck(e, id, ToretError) {
    var co = jQuery('.toret-input-' + e + id);
    if (co.val() < 10) {
        co.addClass('toret-popup-error');
        ToretError += e;
    } else {
        co.removeClass('toret-popup-error');
    }
    return ToretError;
}

// kontrola Váhy
function WeightCheck(e, id, ToretError) {
    var co = jQuery('.toret-input-' + e + id);
    if (co.val() < 0.001) {
        co.addClass('toret-popup-error');
        ToretError += e;
    } else {
        co.removeClass('toret-popup-error');
    }
    return ToretError;
}


// kontrola Váhy
function TotalCheck(e, id, ToretError) {
    var co = jQuery('.toret-input-' + e + id);
    if (co.val() == 0) {
        co.addClass('toret-popup-error');
        ToretError += e;
    } else {
        co.removeClass('toret-popup-error');
    }
    return ToretError;
}


jQuery('document').ready(function () {

    jQuery(".zasilkovna_id_objednavky").click(function (e) {
        if (jQuery(this).hasClass('zasilkovnadisabled')) {
            e.preventDefault()
        } else {
            jQuery(this).addClass('zasilkovnadisabled');
        }
        jQuery(this).attr("disabled", "disabled");
    });


    jQuery('.toret-customs-info').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation()

        var id = jQuery(this).data('id');
        jQuery('.toret-customs-info-' + id).show();
        jQuery('#post-' + id).addClass('no-link');

    });

    jQuery('.toret-popup-inner').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation()
    });


    jQuery('.toret-popup').on('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation()
    });


    jQuery('.toret-add-info').on('click', function (e) {

        e.preventDefault();
        e.stopImmediatePropagation()

        var id = jQuery(this).data('id');

        jQuery('.toret-info-' + id).show();

        jQuery('#post-' + id).addClass('no-link');

    });

    jQuery('.toret-popup-close').on('click', function (e) {
        e.preventDefault();

        var id = jQuery(this).data('id');

        jQuery('.toret-info-' + id).hide();

    });

    jQuery('.toret-popup-close').on('click', function (e) {
        e.preventDefault();

        var id = jQuery(this).data('id');

        jQuery('.toret-customs-info-' + id).hide();

    });


    jQuery('.toret-popup-save').on('click', function (e) {

        e.preventDefault();
        e.stopImmediatePropagation();

        var id = jQuery(this).data('id');
        var width = jQuery('.toret-input-width' + id).val();
        var height = jQuery('.toret-input-height' + id).val();
        var lenght = jQuery('.toret-input-lenght' + id).val();
        var weight = jQuery('.toret-input-weight' + id).val();
        var total = jQuery('.toret-input-total' + id).val();

        var ToretError = '';


        ToretError = DimensionCheck('width', id, ToretError);
        ToretError = DimensionCheck('height', id, ToretError);
        ToretError = DimensionCheck('lenght', id, ToretError);

        ToretError = WeightCheck('weight', id, ToretError);
        ToretError = TotalCheck('total', id, ToretError);

        if (ToretError == '') {
            var data = {
                action: 'toret_save_info',
                sirka: width,
                vyska: height,
                delka: lenght,
                vaha: weight,
                total: total,
                post_id: id
            }

            jQuery.post(ajaxurl, data, function (response) {
                location.reload();
            })
        }
    });


    jQuery('.toret-customs-popup-save').on('click', function (e) {
        e.preventDefault();

        var id = jQuery(this).data('id');
        var date = jQuery('.toret-input-invoice-date' + id).val();
        var number = jQuery('.toret-input-invoice-number' + id).val();


        var data = {
            action: 'toret_save_customs_info',
            date: date,
            number: number,
            post_id: id
        }

        jQuery.post(ajaxurl, data, function (response) {
            location.reload();
        })

    });


    jQuery('.download-label').click(function (e) {
        e.preventDefault();
        var id = jQuery(this).data('id');

        jQuery('.toret-print-' + id).show();

        jQuery('#post-' + id).addClass('no-link');

    });


    jQuery('.disabled-popup').click(function (e) {
        e.preventDefault();

        var id = jQuery(this).data('id');

        var source = jQuery('.toret-print-target' + id).attr('href');
        var offset = jQuery('.toret-input-offset' + id).val();
        var format = jQuery('.toret-format' + id).val();

        var selected = format.split("-on-");
        if (selected[0] == selected[1]) {
            offset = 0;
        }

        var target = source + '&offset=' + offset + '&format=' + format;

        jQuery('.toret-print-' + id).hide();

        window.location.replace(target);

    });

    jQuery('.toret-format').change(function () {
        var id = jQuery(this).data('id');
        var selected = jQuery('.toret-format' + id + ' option:selected').val().split("-on-");

        if (selected[0] == selected[1]) {
            jQuery('.toret-input-offset' + id).prop("disabled", true);
        } else {
            jQuery('.toret-input-offset' + id).prop("disabled", false);
        }

    });

    jQuery('.toret-popup-print-close').on('click', function (e) {
        e.preventDefault();

        var id = jQuery(this).data('id');

        jQuery('.toret-print-' + id).hide();

    });

    jQuery('.toret-popup-print-save').on('click', function (e) {
        e.preventDefault();

        let id = jQuery(this).data('id');

        let source = jQuery('.toret-print-target' + id).attr('href');
        let offset = jQuery('.toret-input-offset' + id).val();
        let format = jQuery('.toret-format' + id).val();

        let selected = format.split("-on-");
        if (selected[0] === selected[1]) {
            offset = 0;
        }

        let target = source + '&offset=' + offset + '&format=' + format;

        jQuery('.toret-print-' + id).hide();

        window.location.replace(target);
    });

    jQuery('#doaction').click(function (e) {

        let action = jQuery('#bulk-action-selector-top option:selected').val();

        if (action === 'hromadny_tisk' || action === 'dopravci_tisk' || action === 'claim_tisk') {

            if (jQuery('.popupdisable').val() !== 'ano') {

                e.preventDefault();


                if (action === 'dopravci_tisk') {

                    jQuery('.toret-formatbulk option').hide();
                    jQuery('.toret-formatbulk .bulk-packeta-only').show();

                    jQuery('.toret-formatbulk option[selected="selected"]').each(
                        function () {
                            jQuery(this).removeAttr('selected');
                        }
                    );

                    let defaultLabel = jQuery('.toret-default-service').val();

                    if (defaultLabel !== '') {
                        jQuery(".toret-formatbulk option[value=" + defaultLabel + "]").attr('selected', 'selected');
                    } else {
                        jQuery(".toret-formatbulk .bulk-packeta-only:first").attr('selected', 'selected');
                    }

                } else {

                    console.log(action)

                    jQuery('.toret-formatbulk option').show();

                    jQuery('.toret-formatbulk option[selected="selected"]').each(
                        function () {
                            jQuery(this).removeAttr('selected');
                        }
                    );

                    let defaultLabel = jQuery('.toret-default-packeta').val();

                    if (defaultLabel !== '') {
                        jQuery(".toret-formatbulk option[value=" + defaultLabel + "]").attr('selected', 'selected');
                    } else {
                        jQuery(".toret-formatbulk option:first").attr('selected', 'selected');
                    }
                }

                jQuery('.toret-print-bulk').show();

            } else {

                e.preventDefault();

                var url = jQuery('input[name=_wp_http_referer]').val();

                if (url.toLowerCase().indexOf("offset") >= 0) {
                    var remove = url.split("&offset");
                    url = remove[0];
                }

                var offset = jQuery('.toret-input-offsetbulk').val();
                if (action == 'hromadny_tisk' || action == 'claim_tisk') {
                    var format = jQuery('.toret-formatbulk-packeta').val();
                } else {
                    var format = jQuery('.toret-formatbulk-service').val();
                }

                var selected = format.split("-on-");
                if (selected[0] == selected[1]) {
                    offset = 0;
                }
                var target = url + '&offset=' + offset + '&format=' + format;

                jQuery('input[name=_wp_http_referer]').val(target)

                if (jQuery("#wc-orders-filter").length) {
                    jQuery("#wc-orders-filter").submit();
                } else
                    jQuery('#posts-filter').submit();
            }
        }
    });

    jQuery('.toret-popup-bulk-close').on('click', function (e) {
        e.preventDefault();
        jQuery('.toret-print-bulk').hide();

    });

    jQuery('.toret-popup-bulk-save').on('click', function (e) {
        e.preventDefault();

        var url = jQuery('input[name=_wp_http_referer]').val();

        if (url.toLowerCase().indexOf("offset") >= 0) {
            var remove = url.split("&offset");
            url = remove[0];
        }

        var offset = jQuery('.toret-input-offsetbulk').val();
        var format = jQuery('.toret-formatbulk').val();

        var selected = format.split("-on-");
        if (selected[0] == selected[1]) {
            offset = 0;
        }
        var target = url + '&offset=' + offset + '&format=' + format;


        jQuery('input[name=_wp_http_referer]').val(target)


        jQuery('.toret-print-bulk').hide();

        if (jQuery("#wc-orders-filter").length) {
            jQuery("#wc-orders-filter").submit();
        } else
            jQuery('#posts-filter').submit();

    });

    // Add spinner
    let $originalIcon = null;
    let $clickerButton = null;

    function addSpinner($button) {
        $originalIcon = $button.find('.dashicons');
        if ($button.data('loading')) return;
        $button.data('loading', true);
        $originalIcon.hide();
        let $spinner = $('<span class="toret-spinner"></span>');
        $button.prepend($spinner);
    }

    // Remove spinner
    function removeSpinner() {
        if ($clickerButton && $originalIcon) {
            $clickerButton.data('loading', false);
            $originalIcon.show();
            $clickerButton.find('.toret-spinner').remove();
        }
    }


    let excludedparameters = [
        'zasilkovna_id_objednavky',
        'package_count',
        'packetka_cancel_finished',
        'zasilkovna_id_objednavky_assistent',
        'zasilkovna_cancel',
        'packetkacancelfinished',
        'is_claim',
        'zasilkovna_delete',
    ]

    function getClearUrl() {

        let url = window.location.href;

        let url_parts = url.split('?');

        let url_main = url_parts[0];
        let url_rest = url_parts[1];

        let url_parameters = url_rest.split('&');

        let url_target = url_main + '?';

        jQuery.each(url_parameters, function (i, parameter) {


            let parameterName = parameter.split('=')[0];


            if (jQuery.inArray(parameterName, excludedparameters) === -1) {

                url_target = url_target + '&' + parameter;

            }

        });

        return url_target;
    }

    jQuery(document).on('click', '.zasilkovna-cancel-package', function (e) {

        e.preventDefault()

        let orderid = jQuery(this).data('orderid');
        let claim = jQuery(this).data('claim');

        let data = {
            action: 'zasilkovna_cancel_package',
            orderid: orderid,
            claim: claim
        };

        jQuery.post(ajaxurl, data, function (response, b) {

            window.location.href = getClearUrl() + '&packetka_cancel_finished=' + response

        })
    });

    jQuery(document).on('click', '.zasilkovna-delete-package', function (e) {

        e.preventDefault()

        let orderid = jQuery(this).data('orderid');

        let data = {
            action: 'zasilkovna_delete',
            orderid: orderid,
        };

        jQuery.post(ajaxurl, data, function (response, b) {

            window.location.href = getClearUrl() + '&packetka_delete_finished=' + response

        })
    });


});
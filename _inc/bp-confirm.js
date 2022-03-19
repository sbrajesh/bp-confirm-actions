jQuery(document).ready(function ($) {
    $(document).on('click', '.generic-button a.bp-needs-confirmation,a.leave-group,.pending_friend a', function (evt) {
       // return allow_or_not(evt);
    });

    $(document).on('click', 'a.leave-group,.pending_friend a', function (evt) {
        return allow_or_not(evt);
    });

    $('ul.activity-list').on('click', 'a.bp-needs-confirmation', function(evt) {
        return allow_or_not(evt);
    });

    $('ul#members-list').on('click', 'a.bp-needs-confirmation', function(evt) {
        return allow_or_not(evt);
    });

    $('div#item-buttons').on('click', 'a.bp-needs-confirmation', function(evt) {
        return allow_or_not(evt);
    });

    //bad idea but works for friendship
    $('a.pending_friend,.friendship-button .remove').on('click', function (evt) {
        return allow_or_not(evt);
    });

    function allow_or_not(evt) {
        if (confirm(BPConfirmaActions.confirm_message)) {
            return true;

        }
        evt.stopImmediatePropagation();
        return false;
    }
});

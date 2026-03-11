<!-- jQuery (LOAD ONCE) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    /* =========================
       Initialize Select2
    ========================= */
    if ($.fn.select2) {
        $('.searchable').select2({
            width: '100%'
        });
    }

    /* =========================
       Mark All Notifications Read
    ========================= */
    $('#markAllReadBtn').on('click', function() {
        fetch('../actions/notification_action.php', {
            method: 'POST'
        }).then(() => location.reload());
    });

    /* =========================
       Category Switcher
    ========================= */
    $('#settingCategories button').on('click', function() {

        $('#settingCategories button').removeClass('active');
        $(this).addClass('active');

        const target = $(this).data('target');

        $('.setting-content').hide();
        $('#' + target).show();
    });

});
</script>

</body>
</html>
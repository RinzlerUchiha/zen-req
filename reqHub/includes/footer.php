</main>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
$(document).ready(function() {

    // Initialize Select2 for searchable selects
    if ($.fn.select2) {
        $('.searchable').select2({
            width: '100%'
        });
    }

    // Initialize Choices.js if present
    if (typeof Choices !== 'undefined') {
        // Choices.js initialization handled in individual pages
    }

});
</script>

</body>
</html>
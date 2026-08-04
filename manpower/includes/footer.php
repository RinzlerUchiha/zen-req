</main>

<!-- Bootstrap JS already loaded in header.php; Select2 already loaded too -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.searchable').select2({ width: '100%' });
    }
    if (typeof Choices !== 'undefined') {
        // Choices.js initialization handled per-page
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const bellBtn = document.getElementById('mpNotifBellBtn');
    if (bellBtn) {
        const dropdown = new bootstrap.Dropdown(bellBtn);
        bellBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.toggle();
        });
    }

    function attachNotifClickHandler(item) {
        item.addEventListener('click', function() {
            const notifId   = this.dataset.notifId;
            const requestId = this.dataset.requestId;

            fetch('<?= $manpower_root ?>/notification_action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(notifId)
            }).catch(() => {});

            if (requestId) {
                window.location.href = '<?= $manpower_root ?>/view?id=' + encodeURIComponent(requestId);
            }
        });
    }

    function attachMarkAllListener(btn) {
        btn.addEventListener('click', function() {
            fetch('<?= $manpower_root ?>/notification_action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: ''
            })
            .then(res => res.json())
            .then(data => { if (data.success) fetchNotifications(); })
            .catch(() => {});
        });
    }

    function fetchNotifications() {
        fetch('<?= $manpower_root ?>/notification_fetch')
            .then(res => res.json())
            .then(data => {
                if (!data.success || !bellBtn) return;

                const count = data.count;
                let badge = bellBtn.querySelector('.mp-notif-badge');
                if (count > 0) {
                    if (badge) {
                        badge.textContent = count > 9 ? '9+' : count;
                    } else {
                        badge = document.createElement('span');
                        badge.className = 'mp-notif-badge';
                        badge.textContent = count > 9 ? '9+' : count;
                        bellBtn.appendChild(badge);
                    }
                } else if (badge) {
                    badge.remove();
                }

                const menu   = bellBtn.closest('.dropdown').querySelector('.dropdown-menu');
                const header = menu.querySelector('.mp-notif-header');

                menu.querySelectorAll('.mp-notif-item, .mp-notif-empty').forEach(el => el.remove());

                let markAllBtn = document.getElementById('mpMarkAllReadBtn');
                if (count > 0) {
                    if (!markAllBtn && header) {
                        markAllBtn = document.createElement('button');
                        markAllBtn.className = 'btn btn-link btn-sm p-0';
                        markAllBtn.id = 'mpMarkAllReadBtn';
                        markAllBtn.style.fontSize = '11.5px';
                        markAllBtn.style.color = 'var(--mp-blue)';
                        markAllBtn.textContent = 'Mark all as read';
                        header.appendChild(markAllBtn);
                        attachMarkAllListener(markAllBtn);
                    }
                } else if (markAllBtn) {
                    markAllBtn.remove();
                }

                if (data.notifications.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'mp-notif-empty';
                    empty.textContent = 'No new notifications';
                    menu.appendChild(empty);
                } else {
                    data.notifications.forEach(notif => {
                        const div = document.createElement('div');
                        div.className = 'mp-notif-item unread';
                        div.dataset.notifId = notif.id;
                        div.dataset.requestId = notif.request_id || '';
                        div.dataset.type = notif.type;
                        div.innerHTML = `
                            <div class="mp-notif-message">${notif.message}</div>
                            <div class="mp-notif-time">${notif.created_at_formatted}</div>
                        `;
                        attachNotifClickHandler(div);
                        menu.appendChild(div);
                    });
                }
            })
            .catch(() => {});
    }

    document.querySelectorAll('.mp-notif-item').forEach(attachNotifClickHandler);

    const markAllBtn = document.getElementById('mpMarkAllReadBtn');
    if (markAllBtn) attachMarkAllListener(markAllBtn);

    if (bellBtn) setInterval(fetchNotifications, 20000);
});
</script>

</body>
</html>
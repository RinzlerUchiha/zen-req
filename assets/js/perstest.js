document.addEventListener('DOMContentLoaded', function () {
    function fetchContent(id) {
        fetch(id)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.text();
            })
            .then(data => {
                const element = document.getElementById(id);
                if (element) {
                    element.innerHTML = data;
                } else {
                    console.warn(`Element with ID '${id}' not found.`);
                }
            })
            .catch(error => console.error(`Error fetching ${id}:`, error));
    }

    // Fetch content after DOM is loaded
    ['disc', 'color', 'vak'].forEach(fetchContent);

    // Navigation functions
    function goToNextDiv(nextSectionId) {
        $('.modal-content').addClass('hidden');
        $('#' + nextSectionId).removeClass('hidden');
    }

    function goToPreviousDiv(previousSectionId) {
        $('.modal-content').addClass('hidden');
        $('#' + previousSectionId).removeClass('hidden');
    }

    // Enneagram save function
    document.querySelector('#save-enneagram')?.addEventListener('click', function () {
        const selectedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked');
        const selectedValues = [];
        const qCategories = [];

        selectedCheckboxes.forEach(checkbox => {
            const qSet = checkbox.getAttribute('q_set');
            const qCategory = checkbox.getAttribute('q_category');
            selectedValues.push(`${qSet}-${qCategory}`);
            qCategories.push(qCategory);
        });

        const formattedData = selectedValues.join(',');

        fetch('saveEnneagram', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                qCategories: qCategories,
                data: formattedData,
            }),
        })
            .then(response => response.json())
            .then(result => {
                console.log('Response:', result);
                if (result.status === 'success') {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert(result.message || 'Error saving data');
                }
            })
            .catch(error => {
                console.error('Error saving data:', error);
                alert('An unexpected error occurred');
            });
    });
});

fetch('innegram')
.then(response => response.text())
.then(html => {
    document.getElementById("inne").innerHTML = html;

    // ADD BUTTON EVENT LISTENER HERE...
    document.querySelector('#save-enneagram').addEventListener('click', function () {
        const selectedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked');
        const selectedValues = [];
        const qCategories = [];

        selectedCheckboxes.forEach(checkbox => {
            const qSet = checkbox.getAttribute('q_set');
            const qCategory = checkbox.getAttribute('q_category');
            selectedValues.push(`${qSet}-${qCategory}`);
            qCategories.push(qCategory);
        });

        // Combine the values into a single string
        const formattedData = selectedValues.join(',');

        // Send the data as JSON
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
    // ==========================
    // NOW FETCH DATA FOR CHART
    // ==========================
    return fetch('enneagram');
})
.then(response => response.json())
.then(data => {

    const canvas = document.getElementById('polarChart');
    if (!canvas) {
        console.error("polarChart canvas not found");
        return;
    }

    const ctx = canvas.getContext('2d');

    const labels = data.map(item => item.type);
    const scores = data.map(item => item.score);

    const originalColors = [
        '#f77d79','#f7a979','#f7f179','#79f7a1','#79f3f7','#8a79f7','#f779ed','#f77992','#798cf7'
    ];

    const defaultColor = '#d3d3d3';

    const sortedIndices = scores
        .map((score, index) => ({ score, index }))
        .sort((a, b) => b.score - a.score)
        .map(item => item.index);

    const top3 = sortedIndices.slice(0, 3);

    const backgroundColors = scores.map((_, i) =>
        top3.includes(i) ? originalColors[i] : defaultColor
    );

    new Chart(canvas.getContext("2d"), {
        type: 'polarArea',
        data: {
            labels: labels,
            datasets: [{
                label: 'Enneagram Scores',
                data: scores,
                backgroundColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true
        }
    });

})
.catch(err => console.error("Error:", err));



// Functions for modal navigation
function goToNextDiv(nextSectionId) {
    // Hide all sections
    $('.modal-content').addClass('hidden');
    // Show the next section
    $('#' + nextSectionId).removeClass('hidden');
}

function goToPreviousDiv(previousSectionId) {
    // Hide all sections
    $('.modal-content').addClass('hidden');
    // Show the previous section
    $('#' + previousSectionId).removeClass('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    // Fetch and display education data
    fetch('educ')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.text(); // Expecting HTML
        })
        .then(data => {
            const educContainer = document.getElementById("educ");
            if (educContainer) {
                educContainer.innerHTML = data; // Set the inner HTML
            } else {
                console.error('Element with id "educ" not found.');
            }
        })
        .catch(error => console.error('Error fetching education data:', error));

    // Use event delegation for dynamic elements
    document.body.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'save-education') {
            // Create a new FormData object
            const formData = new FormData();
            formData.append('levelSchool', document.getElementById('SchLevel')?.value || '');
            formData.append('Degree', document.getElementById('degreeInput')?.value || '');
            formData.append('Major', document.getElementById('majorInput')?.value || '');
            formData.append('School', document.getElementById('schoolInput')?.value || '');
            formData.append('SchoolAdd', document.getElementById('addressInput')?.value || '');
            formData.append('DateGrad', document.getElementById('dateInput')?.value || '');
            formData.append('Status', document.getElementById('statusInput')?.value || '');

            // Send the form data to PHP using AJAX
            fetch('saveEduc', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const alertMessage = document.getElementById('educ-message');
                    if (alertMessage) {
                        if (data.success) {
                            // Display success message
                            alertMessage.className = 'alert alert-success';
                            alertMessage.textContent = "Data saved successfully!";

                            // Clear input values
                            document.getElementById('SchLevel').value = '';
                            document.getElementById('degreeInput').value = '';
                            document.getElementById('majorInput').value = '';
                            document.getElementById('schoolInput').value = '';
                            document.getElementById('addressInput').value = '';
                            document.getElementById('dateInput').value = '';
                            document.getElementById('statusInput').value = '';
                        } else {
                            // Display error message
                            alertMessage.className = 'alert alert-danger';
                            alertMessage.textContent = "Error saving data: " + (data.error || 'Unknown error.');
                        }

                        // Show the alert message
                        alertMessage.style.display = 'block';

                        // Hide the alert message after 3 seconds
                        setTimeout(() => {
                            alertMessage.style.display = 'none';
                        }, 3000);
                    } else {
                        console.error('Element with id "educ-message" not found.');
                    }
                })
                .catch(error => console.error('Error saving education data:', error));
        }
    });
});

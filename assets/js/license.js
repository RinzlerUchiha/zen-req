// Fetch and display license data
fetch('license')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.text(); // Expecting HTML
    })
    .then(data => {
        const licenseContainer = document.getElementById("license");
        if (licenseContainer) {
            licenseContainer.innerHTML = data; // Set the inner HTML
        } else {
            console.error('Element with id "license" not found.');
        }
    })
    .catch(error => console.error('Error fetching license data:', error));

document.addEventListener('DOMContentLoaded', function () {
    // Use event delegation for dynamically loaded elements
    document.body.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'save-license') {
            // Create a new FormData object
            const formData = new FormData();
            formData.append('licensename', document.getElementById('licenseInput')?.value || '');
            formData.append('startdate', document.getElementById('sdateInput')?.value || '');
            formData.append('enddate', document.getElementById('edateInput')?.value || '');
            formData.append('licenseprof', document.getElementById('profInput')?.value || '');

            // Handle the file input
            const fileInput = document.getElementById('limgInput');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('licenseimg', fileInput.files[0]);
            }

            // Send the form data to PHP using AJAX
            fetch('saveLicense', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const alertMessage = document.getElementById('license-message');
                    if (alertMessage) {
                        if (data.success) {
                            // Display success message
                            alertMessage.className = 'alert alert-success';
                            alertMessage.textContent = "Data saved successfully!";

                            // Clear input values
                            document.getElementById('licenseInput').value = '';
                            document.getElementById('sdateInput').value = '';
                            document.getElementById('edateInput').value = '';
                            document.getElementById('profInput').value = '';
                            document.getElementById('limgInput').value = '';
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
                        console.error('Element with id "license-message" not found.');
                    }
                })
                .catch(error => console.error('Error saving license data:', error));
        }
    });
});

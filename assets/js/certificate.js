document.addEventListener('DOMContentLoaded', function () {
    // Fetch and display certificates
    fetch('certs')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.text(); // Expecting HTML
        })
        .then(data => {
            const certContainer = document.getElementById("cert");
            if (certContainer) {
                certContainer.innerHTML = data; // Set the inner HTML
            } else {
                console.error('Element with id "cert" not found.');
            }
        })
        .catch(error => console.error('Error fetching certificates:', error));

    // Use event delegation to handle clicks on the save button inside the modal
    document.body.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'save-cert') {
            // Create a new FormData object
            const formData = new FormData();
            formData.append('certTitle', document.getElementById('certInput')?.value || '');
            formData.append('certdate', document.getElementById('certdateInput')?.value || '');
            formData.append('certlocation', document.getElementById('certlocInput')?.value || '');
            formData.append('certspeak', document.getElementById('certspeakInput')?.value || '');

            // Append the file if available
            const fileInput = document.getElementById('certimgInput');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('certimage', fileInput.files[0]); // Append the file object
            }

            // Send the form data to PHP using AJAX
            fetch('saveCert', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const alertMessage = document.getElementById('certificate-message');
                    if (alertMessage) {
                        if (data.success) {
                            // Display success message
                            alertMessage.className = 'alert alert-success';
                            alertMessage.textContent = "Data saved successfully!";

                            // Clear input values
                            document.getElementById('certInput').value = '';
                            document.getElementById('certdateInput').value = '';
                            document.getElementById('certlocInput').value = '';
                            document.getElementById('certspeakInput').value = '';
                            document.getElementById('certimgInput').value = '';
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
                        console.error('Element with id "certificate-message" not found.');
                    }
                })
                .catch(error => console.error('Error saving certificate:', error));
        }
    });
});

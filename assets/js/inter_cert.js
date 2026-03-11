fetch('certInter')
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
    }
    return response.text(); // Since we're expecting HTML
})
.then(data => {
    document.getElementById("int-cert").innerHTML = data; // Set the inner HTML
})
//SAVING EDUCATION
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('save-Intcert').addEventListener('click', function() {
        // Create a new FormData object
        let formData = new FormData();
        formData.append('certTitle', document.getElementById('certInput').value);
        formData.append('certdate', document.getElementById('certdateInput').value);
        formData.append('certlocation', document.getElementById('certlocInput').value);
        formData.append('certspeak', document.getElementById('certspeakInput').value);
        
        // Append the file correctly
        let fileInput = document.getElementById('certimgInput');
        if (fileInput.files.length > 0) {
            formData.append('certimage', fileInput.files[0]);  // Append the file object
        }

        // Send the form data to PHP using AJAX
        fetch('saveIntCert', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const alertMessage = document.getElementById('intc-message');

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
                alertMessage.textContent = "Error saving data: " + data.error;
            }

            // Show the alert message
            alertMessage.style.display = 'block';

            // Hide the alert message after 3 seconds
            setTimeout(() => {
                alertMessage.style.display = 'none';
            }, 3000);
        })
        .catch(error => console.error('Error:', error));
    });
});

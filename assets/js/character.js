document.addEventListener('DOMContentLoaded', function () {
    fetch('characters')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.text();
        })
        .then(data => {
            const familyContainer = document.getElementById("char");
            if (familyContainer) {
                familyContainer.innerHTML = data; 
            } else {
                console.error('Element with id "family" not found.');
            }
        })
        .catch(error => console.error('Error fetching family data:', error));

    document.body.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'save-charac') {
            // Validate form inputs
            const fullname = document.getElementById('refnameInpt').value.trim();
            const company = document.getElementById('refcompanyInpt').value.trim();
            const position = document.getElementById('refpositionInpt').value.trim();
            const address = document.getElementById('refaddressInpt').value.trim();
            const contact = document.getElementById('refcontactInpt').value.trim();
            const relationship = document.getElementById('refrelationInpt').value.trim();

            if (!company || !address) {
                const alertMessage = document.getElementById('charac-message');
                alertMessage.className = 'alert alert-danger';
                alertMessage.textContent = 'Please fill out all required fields.';
                alertMessage.style.display = 'block';
                setTimeout(() => alertMessage.style.display = 'none', 3000);
                return;
            }

            // Create FormData object
            const formData = new FormData();
            formData.append('refname', fullname);
            formData.append('refcompany', company);
            formData.append('refposition', position);
            formData.append('refaddress', address);
            formData.append('refcontact', contact);
            formData.append('refrelation', relationship);

            // Send data via fetch to PHP
            fetch('saveRef', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const alertMessage = document.getElementById('charac-message');
                    if (data.success) {
                        alertMessage.className = 'alert alert-success';
                        alertMessage.textContent = 'Family information saved successfully!';
                        
                        // Clear form fields
                        document.querySelectorAll('input').forEach(input => input.value = '');
                        document.querySelectorAll('input[name="Famsex"]').forEach(radio => radio.checked = false);
                    } else {
                        alertMessage.className = 'alert alert-danger';
                        alertMessage.textContent = 'Error saving data: ' + (data.error || 'Unknown error.');
                    }
                    alertMessage.style.display = 'block';
                    setTimeout(() => alertMessage.style.display = 'none', 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertMessage = document.getElementById('charac-message');
                    alertMessage.className = 'alert alert-danger';
                    alertMessage.textContent = 'An error occurred while saving data.';
                    alertMessage.style.display = 'block';
                    setTimeout(() => alertMessage.style.display = 'none', 3000);
                });
        }
    });
});


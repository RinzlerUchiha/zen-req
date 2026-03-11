document.addEventListener('DOMContentLoaded', function () {
    // Fetch and display family data
    fetch('family')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.text(); // Expecting HTML
        })
        .then(data => {
            const familyContainer = document.getElementById("family");
            if (familyContainer) {
                familyContainer.innerHTML = data; // Set the inner HTML
            } else {
                console.error('Element with id "family" not found.');
            }
        })
        .catch(error => console.error('Error fetching family data:', error));

    // Event delegation for saving family data
    document.body.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'save-family') {
            // Validate form inputs
            const lastname = document.getElementById('Famlastname').value.trim();
            const firstname = document.getElementById('Famfirstname').value.trim();
            const relationship = document.getElementById('relationship').value;
            const contactNumber = document.getElementById('Famperson_num').value.trim();
            const sex = document.querySelector('input[name="Famsex"]:checked');

            if (!lastname || !firstname || relationship === 'Select Relation' || !contactNumber || !sex) {
                const alertMessage = document.getElementById('family-message');
                alertMessage.className = 'alert alert-danger';
                alertMessage.textContent = 'Please fill out all required fields.';
                alertMessage.style.display = 'block';
                setTimeout(() => alertMessage.style.display = 'none', 3000);
                return;
            }

            // Create FormData object
            const formData = new FormData();
            formData.append('lastname', lastname);
            formData.append('midname', document.getElementById('Fammidname').value.trim());
            formData.append('firstname', firstname);
            formData.append('suffix', document.getElementById('Famsuffixname').value.trim());
            formData.append('maidenname', document.getElementById('Fammaidenname').value.trim());
            formData.append('relationship', relationship);
            formData.append('contactNumber', contactNumber);
            formData.append('birthdate', document.getElementById('Fambirthdate').value);
            formData.append('occupation', document.getElementById('Famoccupation').value.trim());
            formData.append('workplace', document.getElementById('Famworkplace').value.trim());
            formData.append('workAddress', document.getElementById('Famworkadd').value.trim());
            formData.append('sex', sex.value);

            // Send data via fetch to PHP
            fetch('Bfamily', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const alertMessage = document.getElementById('family-message');
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
                    const alertMessage = document.getElementById('family-message');
                    alertMessage.className = 'alert alert-danger';
                    alertMessage.textContent = 'An error occurred while saving data.';
                    alertMessage.style.display = 'block';
                    setTimeout(() => alertMessage.style.display = 'none', 3000);
                });
        }
    });
});

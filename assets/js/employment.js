document.addEventListener('DOMContentLoaded', function () {
    // fetch('emps')
    //     .then(response => {
    //         if (!response.ok) {
    //             throw new Error('Network response was not ok: ' + response.statusText);
    //         }
    //         return response.text();
    //     })
    //     .then(data => {
    //         const familyContainer = document.getElementById("employ");
    //         if (familyContainer) {
    //             familyContainer.innerHTML = data; 
    //         } else {
    //             console.error('Element with id "family" not found.');
    //         }
    //     })
    //     .catch(error => console.error('Error fetching family data:', error));

    document.body.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'save-empl') {
            // Validate form inputs
            const company = document.getElementById('companyInput').value.trim();
            const address = document.getElementById('addressInput').value.trim();
            const position = document.getElementById('positionInput').value.trim();
            const supervisor = document.getElementById('visorInput').value.trim();
            const contact = document.getElementById('Fammaidenname').value.trim();
            const sdate = document.getElementById('sdateInput').value.trim();
            const edate = document.getElementById('edateInput').value.trim();
            const reason = document.getElementById('reasonInput').value.trim();

            if (!company || !address) {
                const alertMessage = document.getElementById('empl-message');
                alertMessage.className = 'alert alert-danger';
                alertMessage.textContent = 'Please fill out all required fields.';
                alertMessage.style.display = 'block';
                setTimeout(() => alertMessage.style.display = 'none', 3000);
                return;
            }

            // Create FormData object
            const formData = new FormData();
            formData.append('companyName', company);
            formData.append('address', address);
            formData.append('position', position);
            formData.append('supervisor', supervisor);
            formData.append('contact', contact);
            formData.append('sdate', sdate);
            formData.append('edate', edate);
            formData.append('reason', reason);

            // Send data via fetch to PHP
            fetch('saveEmplo', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const alertMessage = document.getElementById('empl-message');
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
                    const alertMessage = document.getElementById('empl-message');
                    alertMessage.className = 'alert alert-danger';
                    alertMessage.textContent = 'An error occurred while saving data.';
                    alertMessage.style.display = 'block';
                    setTimeout(() => alertMessage.style.display = 'none', 3000);
                });
        }
    });
});
// // Fetch and display employment data
// fetch('emps')
//     .then(response => {
//         if (!response.ok) {
//             throw new Error('Network response was not ok: ' + response.statusText);
//         }
//         return response.text(); // Expecting HTML
//     })
//     .then(data => {
//         const employContainer = document.getElementById("employ");
//         if (employContainer) {
//             employContainer.innerHTML = data; // Set the inner HTML
//         } else {
//             console.error('Element with id "employ" not found.');
//         }
//     })
//     .catch(error => console.error('Error fetching employment data:', error));

// document.addEventListener('DOMContentLoaded', function () {
//     // Use event delegation for dynamic elements
//     document.body.addEventListener('click', function (event) {
//         if (event.target && event.target.id === 'save-empl') {
//             // Create a new FormData object
//             const formData = new FormData();
//             formData.append('company', document.getElementById('companyInput')?.value || '');
//             formData.append('address', document.getElementById('addressInput')?.value || '');
//             formData.append('position', document.getElementById('positionInput')?.value || '');
//             formData.append('supervisor', document.getElementById('visorInput')?.value || '');
//             formData.append('contact', document.getElementById('contInput')?.value || '');
//             formData.append('sdate', document.getElementById('sdateInput')?.value || '');
//             formData.append('edate', document.getElementById('edateInput')?.value || '');
//             formData.append('reason', document.getElementById('reasonInput')?.value || '');

//             // Send the form data to PHP using AJAX
//             fetch('saveEmplo', {
//                 method: 'POST',
//                 body: formData
//             })
//                 .then(response => response.json())
//                 .then(data => {
//                     const alertMessage = document.getElementById('empl-message');
//                     if (alertMessage) {
//                         if (data.success) {
//                             // Display success message
//                             alertMessage.className = 'alert alert-success';
//                             alertMessage.textContent = "Data saved successfully!";

//                             // Clear input values
//                             document.getElementById('companyInput').value = '';
//                             document.getElementById('addressInput').value = '';
//                             document.getElementById('positionInput').value = '';
//                             document.getElementById('visorInput').value = '';
//                             document.getElementById('contInput').value = '';
//                             document.getElementById('sdateInput').value = '';
//                             document.getElementById('edateInput').value = '';
//                             document.getElementById('reasonInput').value = '';
//                         } else {
//                             // Display error message
//                             alertMessage.className = 'alert alert-danger';
//                             alertMessage.textContent = "Error saving data: " + (data.error || 'Unknown error.');
//                         }

//                         // Show the alert message
//                         alertMessage.style.display = 'block';

//                         // Hide the alert message after 3 seconds
//                         setTimeout(() => {
//                             alertMessage.style.display = 'none';
//                         }, 3000);
//                     } else {
//                         console.error('Element with id "empl-message" not found.');
//                     }
//                 })
//                 .catch(error => console.error('Error saving employment data:', error));
//         }
//     });
// });

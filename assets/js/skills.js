fetch('skills')
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
    }
    return response.text(); // Since we're expecting HTML
})
.then(data => {
    document.getElementById("skills").innerHTML = data; // Set the inner HTML
})
//POPULATING SKILLS OPTIONS
.catch(error => console.error('Error fetching data:', error));
$(document).ready(function() {
    // Fetch categories on page load
    $.ajax({
        url: 'skillCat',
        method: 'GET',
        success: function(response) {
            $('#skillCategory').append(response); // Populate categories
        }
    });

    // Fetch skill types based on selected category
    $('#skillCategory').on('change', function() {
        const sc_id = $(this).val();
        $.ajax({
            url: 'skillType',
            method: 'GET',
            data: { sc_id: sc_id },
            success: function(response) {
                $('#skillType').html('<option value="">Select Type</option>' + response);
            }
        });
    });

    // Show or hide "Others" input
    $('#skillCategory').on('change', function() {
        if ($(this).val() === "7") {
            $('#othersInput').show();
            $('#skillType').hide();
        } else {
            $('#othersInput').hide();
            $('#skillType').show();
        }
    });
});
//SAVING SKILLS
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('save-skills').addEventListener('click', function() {
        // Create a new FormData object
        let formData = new FormData();
        formData.append('Scategory', document.getElementById('skillCategory').value);
        formData.append('Stype', document.getElementById('skillType').value);
        formData.append('Others', document.getElementById('othersInput').value);

        // Send the form data to PHP using AJAX
        fetch('SSkills', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const alertMessage = document.getElementById('skill-message');

            if (data.success) {
                // Display success message
                alertMessage.className = 'alert alert-success';
                alertMessage.textContent = "Data saved successfully!";
                
                // Clear input values
                document.getElementById('skillCategory').value = '';
                document.getElementById('skillType').value = '';
                document.getElementById('othersInput').value = '';
                // document.getElementById('othersInput').style.display = 'none';
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

function openImage() {
	console.log('Input clicked');
	window.open('https://i.pinimg.com/564x/b0/68/56/b06856d929b6066d2281c9f065a29e31.jpg', '_blank');
}
  document.getElementById('save-event').addEventListener('click', function () {
    // Get input values
    const eventname = document.getElementById('eventInput').value;
    const eventdate = document.getElementById('eventdateInput').value;
    const startdate = document.getElementById('sdateInput').value;
    const enddate = document.getElementById('edateInput').value;
    const eventimg = document.getElementById('eventimgInput').files[0]; // Get file

    if (!eventname || !eventdate || !startdate || !enddate || !eventimg) {
        alert('All fields are required!');
        return;
    }

    // Create FormData object
    const formData = new FormData();
    formData.append('eventname', eventname);
    formData.append('eventdate', eventdate);
    formData.append('startdate', startdate);
    formData.append('enddate', enddate);
    formData.append('eventimg', eventimg);

    // Send AJAX request
    fetch('save_event', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Event saved successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
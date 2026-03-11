function fetchAndDisplay(url, elementId) {
  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.statusText);
      }
      return response.text();
    })
    .then(data => {
      document.getElementById(elementId).innerHTML = data;
    })
    .catch(error => {
      console.error('Error fetching data:', error);
      document.getElementById(elementId).innerHTML = "Error fetching data";
    });
}

fetchAndDisplay('13_b', '13B');
// fetchAndDisplay('postIR', '_13bdraft');
// fetchAndDisplay('13b_pending', '_13bposted');
// fetchAndDisplay('solvedIR', '_13breviewed');
// fetchAndDisplay('explIR', '_13bissued');
// fetchAndDisplay('explIR', '_13breceived');
// fetchAndDisplay('explIR', '_13brefused');
// fetchAndDisplay('explIR', '_13bcancelled');

$(document).ready(function() {
    $.ajax({
        url: "13b_pending",
        method: "GET",
        success: function(response) {
            $("#_13bposted").html(response);
        }
    });

   
});
$(document).ready(function(){
    $(".has-submenu > a").click(function(e){
      e.preventDefault();

      var $submenu = $(this).siblings(".submenu");
      
      $submenu.slideToggle('fast', function(){
      });
    });

    $('#saveType').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'typeSave',
                type: 'POST',
                data: $('#typeForm').serialize(),
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.success) {
                            showNotification('success', result.success);
                            $('#typeForm')[0].reset();  // Reset form inputs
                        } else if (result.error) {
                            showNotification('danger', result.error);
                        }
                    } catch (e) {
                        showNotification('danger', 'Unexpected error occurred');
                    }
                },
                error: function() {
                    showNotification('danger', 'An error occurred while saving the data.');
                }
            });
    });

    $('#saveCategory').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'categSave',
                type: 'POST',
                data: $('#categoryForm').serialize(),
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.success) {
                            showNotification('success', result.success);
                            $('#categoryForm')[0].reset();  // Reset form inputs
                        } else if (result.error) {
                            showNotification('danger', result.error);
                        }
                    } catch (e) {
                        showNotification('danger', 'Unexpected error occurred');
                    }
                },
                error: function() {
                    showNotification('danger', 'An error occurred while saving the data.');
                }
            });
    });

    $('#saveItem').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'itemSave',
                type: 'POST',
                data: $('#itemForm').serialize(),
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.success) {
                            showNotification('success', result.success);
                            $('#itemForm')[0].reset();  // Reset form inputs
                        } else if (result.error) {
                            showNotification('danger', result.error);
                        }
                    } catch (e) {
                        showNotification('danger', 'Unexpected error occurred');
                    }
                },
                error: function() {
                    showNotification('danger', 'An error occurred while saving the data.');
                }
            });
    });

    // Function to show notification with Bootstrap alert style
    function showNotification(type, message) {
        $('#responseMessage, #categoryMessage, #itemMessage').html(
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '' +
                '</button>' 
                +
            '</div>'
        );

        $('#itemMessage').html(
            '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '' +
                '</button>' 
                +
            '</div>'
        );
        
        // Auto-fade notification after 3 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 3000);
    }

});

function loadTable() {
        fetch('Mlist')
            .then(response => response.text())
            .then(html => {
                document.getElementById('listmaint').innerHTML = html;
            })
            .catch(error => console.error('Error loading table:', error));
}

window.onload = loadTable;

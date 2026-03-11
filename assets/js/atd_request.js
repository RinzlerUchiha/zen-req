$(document).ready(function(){
  $(".has-submenu > a").click(function(e){
    e.preventDefault(); // Prevent the default action of the link

    var $submenu = $(this).siblings(".submenu");
    
    // Slide toggle the submenu and ensure that it pushes other elements down
    $submenu.slideToggle('fast', function(){
      // Optional: Adjust the sidebar height dynamically if needed
    });
  });
});

$(document).ready(function() {
    // Fetch categories on page load
    $.ajax({
        url: 'categry',
        type: 'GET',
        success: function(data) {
            $('#category').html(data);
        },
        error: function() {
            console.error('Error fetching categories');
        }
    });

    function resetFields() {
        $('#amount').val('');
        $('#amort').val('');
        $('#payroll').val('');
        $('#first').prop('checked', false);
        $('#firstValue').val('');
        $('#second').prop('checked', false);
        $('#secondValue').val('');
        $('#startDate').val('');
        $('#endDate').val('');
    }

    // Fetch items based on selected category
    $('#category').on('change', function() {
        resetFields();
        var categoryID = $(this).val();

        // Clear previous items if no category is selected
        if (!categoryID) {
            $('#items').html('<option value="">Select Item</option>');
            return;
        }

        $.ajax({
            url: 'item',
            type: 'POST',
            data: { category: categoryID },
            success: function(data) {
                $('#items').html(data);
            },
            error: function() {
                console.error('Error fetching items');
            }
        });
    });
});

$(document).ready(function() {
    var amortization = 0; // Declare a global variable for amortization

    $.ajax({
        url: 'categry',
        type: 'GET',
        success: function(data) {
            $('#category').html(data);
        },
        error: function() {
            console.error('Error fetching categories');
        }
    });

    $('#category').on('change', function() {
        var categoryId = $(this).val();

        if (!categoryId) {
            $('#items').html('<option value="">Select Item</option>');
            $('#payroll').val('');
            return;
        }

        $.ajax({
            url: 'item',
            type: 'POST',
            dataType: 'json',
            data: { category: categoryId },
            success: function(data) {
                $('#items').html(data.options);

                if (data.options === "<option value=\"\">Select Items</option>") {
                    $('#payroll').val('');
                }

                $('#items').on('change', function() {
                    var selectedItemId = $(this).val();
                    var payrollValue = data.payrollValues[selectedItemId] || ''; 
                    $('#payroll').val(payrollValue);
                });
            },
            error: function() {
                console.error('Error fetching items and payroll values');
            }
        });
    });
    //COMPUTE AMOUNT BASED ON AMORTIZATION VALUE
    $('#amort').on('input', function() {
        var amortValue = parseFloat($(this).val()) || 0;
        var payrollValue = parseFloat($('#payroll').val()) || 0;

        if (payrollValue > 0) {
            var amountValue = amortValue * payrollValue; 
            $('#amount').val(amountValue.toFixed(2));
        } else {
            $('#amount').val('0.00');
        }
    });
    //AMORTIZATION COMPUTATION
    $('#amount, #payroll').on('input', function() {
        var loanAmount = parseFloat($('#amount').val()) || 0; 
        var payroll = parseFloat($('#payroll').val()) || 0;

        if (payroll > 0) {
            amortization = loanAmount / payroll;
            $('#amort').val(amortization.toFixed(2));
        } else {
            amortization = 0;
            $('#amort').val('0.00');
        }
    });
    //CUTOFF SELECTION BASED COMPUTATION
    $('#first, #second').on('change', function() {
    var amortization = parseFloat($('#amort').val()) || 0;
        var firstChecked = $('#first').is(':checked');
        var secondChecked = $('#second').is(':checked');
    
        if (amortization === 0) {
            if (firstChecked) {
                $('#firstValue').val('0.00');
            } else {
                $('#firstValue').val('');
            }
            
            if (secondChecked) {
                $('#secondValue').val('0.00');
            } else {
                $('#secondValue').val('');
            }
        } else {
            if (firstChecked && secondChecked) {
                $('#firstValue').val((amortization / 2).toFixed(2));
                $('#secondValue').val((amortization / 2).toFixed(2));
            } else if (firstChecked) {
                $('#firstValue').val(amortization.toFixed(2));
                $('#secondValue').val('');
            } else if (secondChecked) {
                $('#secondValue').val(amortization.toFixed(2));
                $('#firstValue').val('');
            } else {
                $('#firstValue').val('');
                $('#secondValue').val('');
            }
        }
    });

    //FOR EDITING THE 15TH CUTOFF
    $('#firstValue').on('input', function() {
        var firstValue = parseFloat($('#firstValue').val()) || 0;

        if (firstValue > amortization) {
            $('#firstValue').val(amortization.toFixed(2)); 
            $('#secondValue').val('');
        } else {
            var secondValue = amortization - firstValue;
            $('#secondValue').val(secondValue.toFixed(2));
        }
    });
    //FOR THE START DATE AND END DATE
    $('#startDate, #payroll').on('change input', function() {
        var startDate = $('#startDate').val();
        var payroll = parseInt($('#payroll').val()) || 0;

        if (startDate && payroll > 0) {
            var startDateObj = new Date(startDate);
            startDateObj.setMonth(startDateObj.getMonth() + payroll);

            var endDate = startDateObj.toISOString().split('T')[0];
            $('#endDate').val(endDate); 
        } else {
            $('#endDate').val(''); 
        }
    });
});
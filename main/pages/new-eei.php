<?php
// $date_diff = $v['ji_datehired'] && $v['ji_datehired'] != '0000-00-00' ? 
// date_diff(new DateTime($v['ji_datehired']), new DateTime(date('Y-m-d'))) : '';
// $months_in_service = $date_diff ? ($date_diff->y * 12) + $date_diff->m : 0;
// $emp_tenure_m = $servicelen;
// $emp_tenure_y = floor($emp_tenure_m / 12);
// // $emp_tenure_m = $servicelen % 12;
// $emp_tenure_range = "";
// if (empty($emp_tenure_range)) {
//     if ($emp_tenure_m <= 18) {
//         $emp_tenure_range = "18 months or less";
//     } elseif ($emp_tenure_m > 18 && $emp_tenure_y <= 5) {
//         $emp_tenure_range = "More than 18 months to 5 years";
//     } elseif ($emp_tenure_y > 5 && $emp_tenure_y <= 10) {
//         $emp_tenure_range = "More than 5 years to 10 years";
//     } elseif ($emp_tenure_y > 10) {
//         $emp_tenure_range = "More than 10years";
//     }
// }
// $eei_sql = $hr_pdo->query("SELECT COUNT(resp_id) AS cnt FROM db_eei.tbl_response WHERE resp_empno = '" . $user_empno . "' AND DATE_FORMAT(resp_date,'%Y-%m') = '" . date('Y-m') . "'");
// $eei_rec = $eei_sql->fetch(PDO::FETCH_OBJ)->cnt;
?>
<style>
    #new-eei-div table {
        width: 100%;
    }

    .eei-opt {
        cursor: pointer;
    }

    .eei-opt.ischk {
        background-color: lightblue;
    }

    .eei-3-3-2-opt label,
    .eei-3-4-2-opt label,
    .eei-3-5-2-opt label {
        font-weight: normal;
    }

    .eei-3-3-2-opt [type="checkbox"],
    .eei-3-4-2-opt [type="checkbox"],
    .eei-3-5-2-opt [type="checkbox"] {
        height: 20px;
        width: 20px;
    }

    .eei-3-5-ans {
        padding: 1px !important;
    }

    .eei-3-5-ans textarea {
        width: 100%;
        height: 100%;
        min-height: 50px;
    }

    .d-none {
        display: none;
    }

    .err-border {
        border: 2px solid red;
    }
</style>
<div class="container-fluid">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Employee Engagement Index</h5>
            </div>
            <div class="panel-body" id="new-eei-div">
                <?php
                if (date('m') % 3 == 0 && $eei_rec == 0)
                    include_once "eei/eei-1.php";
                if (in_array(date('m'), ['01', '06']) && $eei_rec == 0)
                    include_once "eei/eei-2.php";
                if ($emp_tenure_range == "18 months or less" && in_array(date('m'), ['01', '04', '07', '10']) && $eei_rec == 0)
                    include_once "eei/eei-3-2.php";
                if ($emp_tenure_range == "More than 18 months to 5 years" && in_array(date('m'), ['01', '06']) && $eei_rec == 0)
                    include_once "eei/eei-3-3.php";
                if ($emp_tenure_range == "More than 5 years to 10 years" && in_array(date('m'), ['01', '06']) && $eei_rec == 0)
                    include_once "eei/eei-3-4.php";
                if ($emp_tenure_range == "More than 10years" && in_array(date('m'), ['01', '06']) && $eei_rec == 0)
                    include_once "eei/eei-3-5.php";
                ?>
                <input type="hidden" id="emp-tenure" itemval="Gaano katagal ka na sa company?" value="<?= $emp_tenure_range ?>">
                <center>
                    <button id="btn-submit-eei" class="btn btn-primary">Submit</button>
                </center>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {

        $('#btn-submit-eei').click(function() {
            $('#btn-submit-eei').prop('disabled', true);
            let data = {};

            if ($('#tbl-eei-1').length > 0) {
                if ($('.eei-1-item').not(':has(.ischk)').length > 0) {
                    // alert($('.eei-1-item').not(':has(.ischk)').first().find('.eei-1-q').attr('itemval'));

                    let elementOffset = $('.eei-1-item').not(':has(.ischk)').first().offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('.eei-1-item').not(':has(.ischk)').first().addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }
                data['eei-1'] = {};
                $('.eei-1-item').each(function() {
                    data['eei-1'][$(this).attr('eei-id')] = {
                        optval: $(this).find('.ischk').attr('checkval'),
                        opttxt: $(this).find('.ischk').attr('checkvaltxt')
                    };
                });
            }

            if ($('#tbl-eei-2').length > 0) {
                if ($('.eei-2-item').not(':has(.ischk)').length > 0) {
                    let elementOffset = $('.eei-2-item').not(':has(.ischk)').first().offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('.eei-2-item').not(':has(.ischk)').first().addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }
                data['eei-2'] = {};
                $('.eei-2-item').each(function() {
                    data['eei-2'][$(this).attr('eei-id')] = {
                        optval: $(this).find('.ischk').attr('checkval'),
                        opttxt: $(this).find('.ischk').attr('checkvaltxt')
                    };
                });
            }

            if ($('#tbl-eei-3-2').length > 0 || $('#tbl-eei-3-3').length > 0 || $('#tbl-eei-3-4').length > 0 || $('#tbl-eei-3-5').length > 0) {
                data['eei-3'] = {
                    'eei-3-1': $('#emp-tenure').val()
                };
            }

            if ($('#tbl-eei-3-2').length > 0) {
                if ($('.eei-3-2-item').not(':has(.ischk)').length > 0) {
                    let elementOffset = $('.eei-3-2-item').not(':has(.ischk)').first().offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('.eei-3-2-item').not(':has(.ischk)').first().addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }
                data['eei-3']['eei-3-2'] = {};
                $('.eei-3-2-item').each(function() {
                    data['eei-3']['eei-3-2'][$(this).attr('eei-id')] = {
                        optval: $(this).find('.ischk').attr('checkval'),
                        opttxt: $(this).find('.ischk').attr('checkvaltxt')
                    };
                });
            }

            if ($('#tbl-eei-3-3').length > 0) {

                if ($('[eei-id="eei-3-3-1"]').not(':has(.ischk)').length > 0) {
                    let elementOffset = $('[eei-id="eei-3-3-1"]').offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('[eei-id="eei-3-3-1"]').addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }

                if ($('.eei-3-3-2-opt:visible').length > 0 && $('.eei-3-3-2-opt input:checked').length == 0) {
                    let elementOffset = $('[eei-id="eei-3-3-2"]').offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('[eei-id="eei-3-3-2"]').addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }

                data['eei-3']['eei-3-3'] = {};
                $('.eei-3-3-item').each(function() {
                    if ($(this).hasClass('multi-chk')) {
                        let selected_opt = [];
                        $('#tbl-eei-3-3 .' + $(this).attr('eei-id') + '-opt').has('[name="eei-3-3-2-opt"]:checked').each(function() {
                            selected_opt.push($(this).attr('checkval'));
                        });
                        if (selected_opt.length > 0) {
                            data['eei-3']['eei-3-3'][$(this).attr('eei-id')] = selected_opt;
                        }
                    } else {
                        data['eei-3']['eei-3-3'][$(this).attr('eei-id')] = {
                            optval: $(this).find('.ischk').attr('checkval'),
                            opttxt: $(this).find('.ischk').attr('checkvaltxt')
                        };
                    }
                });
            }

            if ($('#tbl-eei-3-4').length > 0) {
                if ($('[eei-id="eei-3-4-1"]').not(':has(.ischk)').length > 0) {
                    let elementOffset = $('[eei-id="eei-3-4-1"]').offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('[eei-id="eei-3-4-1"]').addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }

                if ($('.eei-3-4-2-opt:visible').length > 0 && $('.eei-3-4-2-opt input:checked').length == 0) {
                    let elementOffset = $('[eei-id="eei-3-4-2"]').offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('[eei-id="eei-3-4-2"]').addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }

                data['eei-3']['eei-3-4'] = {};
                $('.eei-3-4-item').each(function() {
                    if ($(this).hasClass('multi-chk')) {
                        let selected_opt = [];
                        $('#tbl-eei-3-4 .' + $(this).attr('eei-id') + '-opt').has('[name="eei-3-4-2-opt"]:checked').each(function() {
                            selected_opt.push($(this).attr('checkval'));
                        });
                        if (selected_opt.length > 0) {
                            data['eei-3']['eei-3-4'][$(this).attr('eei-id')] = selected_opt;
                        }
                    } else {
                        data['eei-3']['eei-3-4'][$(this).attr('eei-id')] = {
                            optval: $(this).find('.ischk').attr('checkval'),
                            opttxt: $(this).find('.ischk').attr('checkvaltxt')
                        };
                    }
                });
            }

            if ($('#tbl-eei-3-5').length > 0) {
                if ($('[eei-id="eei-3-5-1"]').not(':has(.ischk)').length > 0) {
                    let elementOffset = $('[eei-id="eei-3-5-1"]').offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('[eei-id="eei-3-5-1"]').addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }

                if ($('.eei-3-5-2-opt:visible').length > 0 && $('.eei-3-5-2-opt input:checked').length == 0) {
                    let elementOffset = $('[eei-id="eei-3-5-2"]').offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('[eei-id="eei-3-5-2"]').addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }

                if ($('[eei-id="eei-3-5-3"] textarea').val().trim() == '') {
                    let elementOffset = $('[eei-id="eei-3-5-3"]').offset().top;
                    let windowHeight = $(window).height();
                    let scrollPosition = elementOffset - (windowHeight / 3);
                    $('html, body').animate({
                        scrollTop: scrollPosition
                    }, 500); // Adjust the duration as needed (in milliseconds)
                    $('[eei-id="eei-3-5-3"]').addClass('err-border');
                    setTimeout(() => $('.err-border').removeClass('err-border'), 1500);
                    $('#btn-submit-eei').prop('disabled', false);
                    return;
                }

                data['eei-3']['eei-3-5'] = {};
                $('.eei-3-5-item').each(function() {
                    if ($(this).hasClass('multi-chk')) {
                        let selected_opt = [];
                        $('#tbl-eei-3-5 .' + $(this).attr('eei-id') + '-opt').has('[name="eei-3-5-2-opt"]:checked').each(function() {
                            selected_opt.push($(this).attr('checkval'));
                        });
                        if (selected_opt.length > 0) {
                            data['eei-3']['eei-3-5'][$(this).attr('eei-id')] = selected_opt;
                        }
                    } else if ($(this).find('.eei-3-5-ans').length > 0) {
                        data['eei-3']['eei-3-5'][$(this).attr('eei-id')] = $(this).find('.eei-3-5-ans textarea').val();
                    } else {
                        data['eei-3']['eei-3-5'][$(this).attr('eei-id')] = {
                            optval: $(this).find('.ischk').attr('checkval'),
                            opttxt: $(this).find('.ischk').attr('checkvaltxt')
                        };
                    }
                });
            }

            $.post('new-eei', {
                a: 'submit',
                data: data
            }, function(res) {
                if (res == 1) {
                    alert('Submitted');
                    window.location = '?page=home';
                } else {
                    alert('Failed to submit. Please try again.');
                }
            });
        });
    });
</script>
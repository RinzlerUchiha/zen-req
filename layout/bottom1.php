
                                <!-- <div id="styleSelector"></div> -->
                            </div><!-- main-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Older IE warning message -->
    <!--[if lt IE 9]>
    <div class="ie-warning">
        <h1>Warning!!</h1>
        <p>You are using an outdated version of Internet Explorer, please upgrade <br/>to any of the following web browsers to access this website.</p>
        <div class="iew-container">
            <ul class="iew-download">
                <li>
                    <a href="http://www.google.com/chrome/">
                        <img src="/zen/admin_template/assets/images/browser/chrome.png" alt="Chrome">
                        <div>Chrome</div>
                    </a>
                </li>
                <li>
                    <a href="https://www.mozilla.org/en-US/firefox/new/">
                        <img src="/zen/admin_template/assets/images/browser/firefox.png" alt="Firefox">
                        <div>Firefox</div>
                    </a>
                </li>
                <li>
                    <a href="http://www.opera.com">
                        <img src="/zen/admin_template/assets/images/browser/opera.png" alt="Opera">
                        <div>Opera</div>
                    </a>
                </li>
                <li>
                    <a href="https://www.apple.com/safari/">
                        <img src="/zen/admin_template/assets/images/browser/safari.png" alt="Safari">
                        <div>Safari</div>
                    </a>
                </li>
                <li>
                    <a href="http://windows.microsoft.com/en-us/internet-explorer/download-ie">
                        <img src="/zen/admin_template/assets/images/browser/ie.png" alt="">
                        <div>IE (9 & above)</div>
                    </a>
                </li>
            </ul>
        </div>
        <p>Sorry for the inconvenience!</p>
    </div>
    <![endif]-->
    <!-- Warning Section Ends -->
    <!-- Required Jqurey -->
    <script type="text/javascript" src="/zen/admin_template/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/zen/admin_template/bower_components/popper.js/js/popper.min.js"></script>
    <?php if($pa_root != ($portal_root ?? '')."/dtr"){ ?>
    <script type="text/javascript" src="/zen/admin_template/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <?php } ?>
    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->

    <!-- <script type="text/javascript" src="/zen/admin_template/bower_components/jquery/js/jquery.min.js"></script> -->
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="/zen/admin_template/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
    <!-- modernizr js -->
    <script type="text/javascript" src="/zen/admin_template/bower_components/modernizr/js/modernizr.js"></script>
    <script type="text/javascript" src="/zen/admin_template/bower_components/modernizr/js/css-scrollbars.js"></script>
    <!-- classie js -->
    <script type="text/javascript" src="/zen/admin_template/bower_components/classie/js/classie.js"></script>    
    <!-- i18next.min.js -->
    <script type="text/javascript" src="/zen/admin_template/bower_components/i18next/js/i18next.min.js"></script>
    <script type="text/javascript" src="/zen/admin_template/bower_components/i18next-xhr-backend/js/i18nextXHRBackend.min.js"></script>
    <script type="text/javascript" src="/zen/admin_template/bower_components/i18next-browser-languagedetector/js/i18nextBrowserLanguageDetector.min.js"></script>
    <script type="text/javascript" src="/zen/admin_template/bower_components/jquery-i18next/js/jquery-i18next.min.js"></script>

    <!--Forms - Wizard js-->
    <!-- <script src="/zen/admin_template/bower_components/jquery.cookie/js/jquery.cookie.form-wizard.jsjs"></script> -->
    <script src="/zen/admin_template/bower_components/jquery.steps/js/jquery.steps.js"></script>
    <script src="/zen/admin_template/bower_components/jquery-validation/js/jquery.validate.js"></script>

   
    <!-- jquery file upload js -->
    <!-- <script src="/zen/admin_template/assets/pages/jquery.filer/js/jquery.filer.min.js"></script> -->
    <!-- <script src="/zen/admin_template/assets/pages/filer/custom-filer.js" type="text/javascript"></script> -->
    <!-- <script src="/zen/admin_template/assets/pages/filer/jquery.fileuploads.init.js" type="text/javascript"></script> -->

    <!-- Validation js -->
    <script src="/zen/admin_template/bower_components/underscore/js/underscore-min.js"></script>
    <script src="/zen/admin_template/bower_components/moment/js/moment.min.js"></script>
    <!-- <script type="text/javascript" src="/zen/admin_template/assets/pages/form-validation/validate.js"></script> -->

    <!-- data-table js -->
    <script src="/zen/admin_template/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="/zen/admin_template/bower_components/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="/zen/admin_template/assets/pages/data-table/js/jszip.min.js"></script>
    <script src="/zen/admin_template/assets/pages/data-table/js/pdfmake.min.js"></script>
    <script src="/zen/admin_template/assets/pages/data-table/js/vfs_fonts.js"></script>
    <script src="/zen/admin_template/assets/pages/data-table/extensions/colreorder/js/dataTables.colReorder.min.js"></script>
    <script src="/zen/admin_template/bower_components/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="/zen/admin_template/bower_components/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="/zen/admin_template/bower_components/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/zen/admin_template/bower_components/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/zen/admin_template/bower_components/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>



    <!-- Handson table js -->
    <!-- <script type="text/javascript" src="/zen/admin_template/bower_components/handsontable/js/handsontable.full.js"></script> -->
    <!-- Custom js -->
    <!-- <script type="text/javascript" src="/zen/admin_template/assets/pages/handson-table/cell-features.js"></script> -->
    <script type="text/javascript" src="/zen/admin_template/bower_components/jscolor/js/jscolor.js"></script>
    <script src="/zen/admin_template/assets/pages/data-table/extensions/colreorder/js/colreorder-custom.js"></script>

    <!-- <script src="/zen/admin_template/assets/pages/data-table/extensions/buttons/js/extension-btns-custom.js"></script> -->
    <!-- <script type="text/javascript" src="/zen/admin_template/assets/pages/handson-table/integration.js"></script> -->
    <!-- <script src="/zen/admin_template/assets/pages/data-table/extensions/fixed-colums/js/fixed-column-custom.js"></script> -->
    <script type="text/javascript" src="/zen/admin_template/assets/js/script.js"></script>
    <!-- <script type="text/javascript" src="/zen/admin_template/assets/pages/forms-wizard-validation/form-wizard.js"></script> -->
    <!-- pcmenu js -->
    <script src="/zen/admin_template/assets/js/pcoded.min.js"></script>
    <script src="/zen/admin_template/assets/js/demo-12.js"></script>
    <script src="/zen/admin_template/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="/zen/admin_template/assets/js/jquery.mousewheel.min.js"></script>
    <!-- Handson table js -->
    <!-- <script type="text/javascript" src="/zen/admin_template/bower_components/handsontable/js/handsontable.full.js"></script> -->
    <!-- <script src="/zen/assets/js/zen.js"></script> -->
    <?php if($pa_root != ($portal_root ?? '')."/dtr"){ ?>
    <script src="/zen/assets/js/post.js"></script>
    <?php } ?>
    <!-- <script src="fullcalendar/js/main.min.js"></script> -->
    <!-- <script src="/zen/assets/pages/jquery.filer/js/jquery.filer.min.js"></script> -->
    <script type="text/javascript" src="/zen/assets/js/jquery.emojipicker.js"></script>

    <!-- Emoji Data -->
    <link rel="stylesheet" type="text/css" href="/zen/assets/css/jquery.emojipicker.tw.css">
    <script type="text/javascript" src="/zen/assets/js/jquery.emojis.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('.displayChatbox').on('click', function(e) {
            e.preventDefault(); // Prevent default link behavior if needed
            $(this).find('i').toggleClass('active-color');
        });
    });

    </script>

    <!-- Select 2 js -->
    <script type="text/javascript" src="/zen/admin_template/bower_components/select2/js/select2.full.min.js"></script>
    <!-- Multiselect js -->
    <script type="text/javascript" src="/zen/admin_template/bower_components/bootstrap-multiselect/js/bootstrap-multiselect.js">
    </script>
    <script type="text/javascript" src="/zen/admin_template/bower_components/multiselect/js/jquery.multi-select.js"></script>
    <script type="text/javascript" src="/zen/admin_template/assets/js/jquery.quicksearch.js"></script>
    <script type="text/javascript" src="/zen/admin_template/assets/pages/advance-elements/select2-custom.js"></script>
    
    <link rel="stylesheet" type="text/css" href="/zen/assets/js/postfeed.js">
    <link rel="stylesheet" type="text/css" href="/zen/assets/js/atd_maintenance.js">
    <link rel="stylesheet" type="text/css" href="/zen/assets/js/home.js">
    <link rel="stylesheet" type="text/css" href="/zen/assets/js/event.js">
    <?php if($pa_root != ($portal_root ?? '')."/dtr"){ ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <?php }else{ ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/js/bootstrap-select.min.js"></script>
    <?php } ?>
</body> 

</html>
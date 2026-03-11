$(document).ready(function () {
    function loadProvinces(selectId) {
        $.ajax({
            url: "loc",
            type: "POST",
            data: { type: "province" },
            dataType: "json",
            success: function (data) {
                let options = '<option value="">Select Province</option>';
                $.each(data, function (key, value) {
                    options += `<option value="${value.pr_code}">${value.pr_name}</option>`;
                });
                $(selectId).html(options);
            }
        });
    }

    function loadMunicipalities(provinceSelect, municipalitySelect) {
        $(document).on("change", provinceSelect, function () {
            let pr_code = $(this).val();
            if (pr_code) {
                $.ajax({
                    url: "loc",
                    type: "POST",
                    data: { type: "municipality", pr_code: pr_code },
                    dataType: "json",
                    success: function (data) {
                        let options = '<option value="">Select Municipality</option>';
                        $.each(data, function (key, value) {
                            options += `<option value="${value.ct_id}">${value.ct_name}</option>`;
                        });
                        $(municipalitySelect).html(options);
                    }
                });
            }
        });
    }

    function loadBarangays(municipalitySelect, barangaySelect) {
        $(document).on("change", municipalitySelect, function () {
            let ct_id = $(this).val();
            if (ct_id) {
                $.ajax({
                    url: "loc",
                    type: "POST",
                    data: { type: "barangay", ct_id: ct_id },
                    dataType: "json",
                    success: function (data) {
                        let options = '<option value="">Select Barangay</option>';
                        $.each(data, function (key, value) {
                            options += `<option value="${value.br_id}">${value.br_name}</option>`;
                        });
                        $(barangaySelect).html(options);
                    }
                });
            }
        });
    }

    // Load provinces on page load
    loadProvinces("#province-perm1");
    loadProvinces("#rovince-cur1");
    loadProvinces("#province-birth1");

    loadProvinces("#province-perm3");
    loadProvinces("#province-cur3");
    loadProvinces("#province-birth3");

    loadProvinces("#province");
    loadProvinces("#province-cur2");
    loadProvinces("#province-birth2");

    // Load municipalities and barangays for each address type
    loadMunicipalities("#province-perm1", "#municipal-perm1");
    loadMunicipalities("#rovince-cur1", "#municipal-cur1");
    loadMunicipalities("#province-birth1", "#municipal-birth1");

    loadMunicipalities("#province-perm3", "#municipal-perm3");
    loadMunicipalities("#province-cur3", "#municipal-cur3");
    loadMunicipalities("#province-birth3", "#municipal-birth3");

    loadMunicipalities("#province", "#municipality");
    loadMunicipalities("#province-cur2", "#municipal-cur2");
    loadMunicipalities("#province-birth2", "#municipal-birth2");

    loadBarangays("#municipal-perm1", "#brngy-perm1");
    loadBarangays("#municipal-cur1", "#brngy-cur1");
    loadBarangays("#municipal-birth1", "#brngy-birth1");

    loadBarangays("#municipality", "#barangay");
    loadBarangays("#municipal-cur2", "#brngy-cur2");
    loadBarangays("#municipal-birth2", "#brngy-birth2");
    
    loadBarangays("#municipal-perm3", "#brngy-perm3");
    loadBarangays("#municipal-cur3", "#brngy-cur3");
    loadBarangays("#municipal-birth3", "#brngy-birth3");
});

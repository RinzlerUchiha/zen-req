document.addEventListener('DOMContentLoaded', function () {
    
    fetch('personal')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.text(); 
        })
        .then(data => {
            const educContainer = document.getElementById("profile");
            if (educContainer) {
                educContainer.innerHTML = data; 
            } else {
                console.error('Element with id "personal" not found.');
            }
        })
        .catch(error => console.error('Error fetching personal data:', error));

    
    document.body.addEventListener('click', function (event) {
        if (event.target && event.target.id === 'save-personal') {
           
            const formData = new FormData();

            function getVisibleValue(ids) {
                for (let id of ids) {
                    let element = document.getElementById(id);
                    if (element && element.offsetParent !== null) { 
                        return element.value || ''; 
                    }
                }
                return ''; 
            }

            formData.append('lastname', document.getElementById('lastnameInput')?.value || '');
            formData.append('firstname', document.getElementById('firstnameInput')?.value || '');
            formData.append('midname', document.getElementById('midnameInput')?.value || '');
            formData.append('maidenname', document.getElementById('maidnameInput')?.value || '');
            formData.append('suffix', document.getElementById('suffixInput')?.value || '');
            formData.append('person_num', document.getElementById('persnumInput')?.value || '');
            formData.append('company_num', document.getElementById('compnameInput')?.value || '');
            formData.append('email', document.getElementById('emailInput')?.value || '');
            formData.append('telephone', document.getElementById('telphInput')?.value || '');
            formData.append('sss', document.getElementById('sssInput')?.value || '');
            formData.append('pagibig', document.getElementById('pagibigInput')?.value || '');
            formData.append('philhealth', document.getElementById('philInput')?.value || '');
            formData.append('tin', document.getElementById('tinInput')?.value || '');

            formData.append('Pprovince', getVisibleValue(['province-perm1','province','province-perm3']));
            formData.append('Pmunicipal', getVisibleValue(['municipal-perm1','municipal-perm3','municipality']));
            formData.append('Pbrngy', getVisibleValue(['brngy-perm1','brngy-perm3','barangay']));
            formData.append('Cprovince', getVisibleValue(['province-cur1','province-cur2','province-cur3']));
            formData.append('Cmunicipal', getVisibleValue(['municipal-cur1','municipal-cur2','municipal-cur3']));
            formData.append('Cbrngy', getVisibleValue(['brngy-cur1','brngy-cur2','brngy-cur3']));
            formData.append('Bprovince', getVisibleValue(['province-birth1','province-birth2','province-birth3']));
            formData.append('Bmunicipal', getVisibleValue(['municipal-birth1','municipal-birth2','municipal-birth3']));
            formData.append('Bbrngy', getVisibleValue(['brngy-birth1','brngy-birth2','brngy-birth3']));
            formData.append('permaddlocInput', document.getElementById('permaddlocation')?.value || '');
            formData.append('curaddlocInput', document.getElementById('curaddlocation')?.value || '');
            formData.append('birthaddlocInput', document.getElementById('birthaddlocation')?.value || '');
            
            // formData.append('Pprovince', document.getElementById('province-perm1')?.value || '');
            // formData.append('Pmunicipal', document.getElementById('municipal-perm1')?.value || '');
            // formData.append('Pbrngy', document.getElementById('brngy-perm1')?.value || '');
            // formData.append('Cprovince', document.getElementById('province-cur1')?.value || '');
            // formData.append('Cmunicipal', document.getElementById('municipal-cur1')?.value || '');
            // formData.append('Cbrngy', document.getElementById('brngy-cur1')?.value || '');
            // formData.append('Bprovince', document.getElementById('province-birth1')?.value || '');
            // formData.append('Bmunicipal', document.getElementById('municipal-birth1')?.value || '');
            // formData.append('Bbrngy', document.getElementById('brngy-birth1')?.value || '');

            formData.append('birthdate', document.getElementById('birthdayInput')?.value || '');
            formData.append('civilstat', document.getElementById('civilInput')?.value || '');
            formData.append('religion', document.getElementById('religInput')?.value || '');
            formData.append('height', document.getElementById('heightInput')?.value || '');
            formData.append('weight', document.getElementById('weightInput')?.value || '');
            formData.append('bloodtype', document.getElementById('bloodInput')?.value || '');
            formData.append('dialect', document.getElementById('dialectInput')?.value || '');


            fetch('save_personal', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const alertMessage = document.getElementById('personal-message');
                    if (alertMessage) {
                        if (data.success) {
                            alertMessage.className = 'alert alert-success';
                            alertMessage.textContent = "Data saved successfully!";
                        } else {
                            alertMessage.className = 'alert alert-danger';
                            alertMessage.textContent = "Error saving data: " + (data.error || 'Unknown error.');
                        }

                        alertMessage.style.display = 'block';

                        setTimeout(() => {
                            alertMessage.style.display = 'none';
                        }, 3000);
                    } else {
                        console.error('Element with id "educ-message" not found.');
                    }
                })
                .catch(error => console.error('Error saving education data:', error));
        }
    });
});

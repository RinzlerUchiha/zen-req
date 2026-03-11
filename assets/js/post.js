const text = document 
	.querySelector('#post-desc'); 
const postBtn = document 
	.querySelector('.post-btn'); 
const postAudienceBtn = document 
	.querySelector('.post-audience'); 
const backBtn = document. 
	querySelector('.arrow-left-icon'); 
const createPostSection = document. 
	querySelector('.create-post'); 
const postAudienceSection = document. 
	querySelector('.post-audience-section'); 
// const emojiBtn = document. 
// 	querySelector('.emoji'); 
// const emojiPicker = document. 
// 	querySelector('emoji-picker'); 
const audienceOptions = document. 
	querySelectorAll(".audience-option"); 
const radioBtns = document. 
	querySelectorAll(".audience-option-radio"); 
// const addtoPostBtn = document. 
// 	querySelectorAll(".add-to-your-post"); 
// const postItemSection = document. 
// 	querySelectorAll(".post-item-section");

document.body.style.overflowX = 'none'; 

text.addEventListener("input", () => { 
	if (text.value != '') 
		postBtn.disabled = false; 
	else
		postBtn.disabled = true; 
}) 
// emojiBtn.addEventListener("click", () => { 
// 	if (emojiPicker.style.display == 'none') 
// 		emojiPicker.style.display = 'block'; 
// 	else
// 		emojiPicker.style.display = 'none'; 
// }) 
// emojiPicker.addEventListener('emoji-click', e => { 
// 	textarea.value += e.detail.unicode; 
// }) 
postAudienceBtn.addEventListener('click', () => { 
	document.querySelector('.wrapper') 
		.classList.add('wrapper-active'); 
	postAudienceSection.style.display = 'block'; 
	createPostSection.style.display = 'none'; 
}) 
audienceOptions.forEach(option => { 
	option.addEventListener('click', e => { 
		if (!option.classList.contains('active')) { 
			option.classList.add('active'); 
			e.currentTarget.children[1] 
				.children[0].children[0].checked = true; 
		} 
		for (let i = 0; i < audienceOptions.length; i++) { 
			if (e.currentTarget != audienceOptions[i]) { 
				audienceOptions[i].classList 
					.remove('active'); 
				radioBtns[i].checked = false; 
			} 
		} 
	}) 
}) 
backBtn.addEventListener('click', () => { 
	document.querySelector('.wrapper') 
		.classList.remove('wrapper-active'); 
	postAudienceSection.style.display = 'none'; 
	createPostSection.style.display = 'block'; 
})

function toggleDiv() {
    const div = document.getElementById('add-photo-video');
    const showIcon = document.getElementById('showIcon');
    const hideIcon = document.getElementById('close');

    if (div.style.display === 'none') {
        div.style.display = 'block';
        showIcon.style.display = 'block';
        hideIcon.style.display = 'inline';
    } else {
        div.style.display = 'none';
        showIcon.style.display = 'inline';
        hideIcon.style.display = 'none';
    }

}


function updateFileContent() {
    const fileInput = document.getElementById('file-input');
    const imageVideoDiv = document.getElementById('image-video');

    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            // Set the background image of the image-video div
            imageVideoDiv.style.backgroundImage = `url(${e.target.result})`;
            imageVideoDiv.innerHTML = `<i id="close" onclick="toggleDiv()" style="cursor: pointer;" class="fa fa-times-circle"></i>`;
            
            // Create and append the "Change File" button with Bootstrap classes
            const changeButton = document.createElement('button');
            changeButton.textContent = 'Change File';
            changeButton.classList.add('btn', 'btn-primary', 'btn-mini');
            
            // Prevent the modal from closing when the button is clicked
            changeButton.onclick = function(event) {
                event.preventDefault(); // Prevent default behavior
                fileInput.click(); // Trigger file input to change file
            };
            imageVideoDiv.appendChild(changeButton);
        };

        reader.readAsDataURL(file);
    }
}

document.getElementById("submitBtn").addEventListener("click", function (event) {
  // Prevent default behavior if inside a form
  event.preventDefault();

  // Get the selected radio button
  const selectedOption = document.querySelector('input[name="audience"]:checked');
  
  if (selectedOption) {
    // Get the custom data attributes
    const newText = selectedOption.getAttribute("data-text");
    const newIcon = selectedOption.getAttribute("data-icon");
    
    // Update the span and icon
    document.getElementById("resultText").textContent = newText;
    const resultIcon = document.getElementById("resultIcon");
    resultIcon.className = newIcon; // Update icon class

    // Optional: Hide optionsDiv if needed
    // document.getElementById("optionsDiv").style.display = "none";
  } else {
    alert("Please select an option!");
    return; // Exit function if no selection is made
  }

  // Update wrapper classes and display sections
  document.querySelector('.wrapper').classList.remove('wrapper-active');
  postAudienceSection.style.display = 'none'; 
  createPostSection.style.display = 'block';
});

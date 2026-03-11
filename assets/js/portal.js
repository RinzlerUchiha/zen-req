$(document).ready(function () {
    // Event delegation for reaction triggers
    $(document).on('click', '.reaction-trigger', function () {
        $(this).siblings('.reaction-options').toggle();
    });

    // Event delegation for reactions
    $(document).on('click', '.reaction', function () {
        var reactionType = $(this).data('reaction');
        var postBy = $(this).data('reacted_by');
        var postId = $(this).closest('.reaction-container').find('.reaction-trigger').attr('id').split('-')[2];
        var $reactionTrigger = $(this).closest('.reaction-container').find('.reaction-trigger');

        $.ajax({
            url: 'reaction',
            method: 'POST',
            data: { post_id: postId, reaction: reactionType, reacted_by: postBy },
            success: function (response) {
                $(this).closest('.reaction-container').find('.reaction-options').hide();
                $reactionTrigger.hide();

                var reactionImage;
                switch (reactionType) {
                    case 'like':
                        reactionImage = '<img src="/zen/assets/reactions/likes.WEBP" class="img-fluid rounded-circle" alt="Like">';
                        break;
                    case 'heart':
                        reactionImage = '<img src="/zen/assets/reactions/love.WEBP" class="img-fluid rounded-circle" alt="Heart">';
                        break;
                    case 'love':
                        reactionImage = '<img src="https://i.pinimg.com/564x/1e/b9/ab/1eb9abce88c9859c08e70330ef8495dc.jpg" class="img-fluid rounded-circle" alt="Love">';
                        break;
                    case 'cry':
                        reactionImage = '<img src="/zen/assets/reactions/cry.WEBP" class="img-fluid rounded-circle" alt="Cry">';
                        break;
                    case 'haha':
                        reactionImage = '<img src="/zen/assets/reactions/lough.WEBP" class="img-fluid rounded-circle" alt="Haha">';
                        break;
                    case 'wow':
                        reactionImage = '<img src="/zen/assets/reactions/shock.WEBP" class="img-fluid rounded-circle" alt="Money">';
                        break;
                    case 'angry':
                        reactionImage = '<img src="/zen/assets/reactions/sadness.WEBP" class="img-fluid rounded-circle" alt="Angry">';
                        break;
                    case 'eey':
                        reactionImage = '<img src="https://i.pinimg.com/564x/cc/12/e0/cc12e02e7eed4491de74e05ea8a019a5.jpg" class="img-fluid rounded-circle" alt="Eey">';
                        break;
                    default:
                        reactionImage = '';
                }

                $reactionTrigger.html(reactionImage).show();
            }.bind(this),
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

$(document).ready(function(){
    let mentionList = $("#mention-list");
    let textarea = $("#post-desc");

    textarea.on("input", function() {
        let cursorPos = this.selectionStart;
        let text = $(this).val().substring(0, cursorPos);
        let match = text.match(/@([\w]*)$/);

        if (match) {
            let searchQuery = match[1];

            if (searchQuery.length > 0) {
                $.ajax({
                    url: "persons",
                    method: "POST",
                    data: { query: searchQuery },
                    success: function(response) {
                        mentionList.html(response).show();
                    }
                });
            } else {
                mentionList.hide();
            }
        } else {
            mentionList.hide();
        }
    });

    $(document).on("click", ".mention-item", function() {
        let name = $(this).text();
        let text = textarea.val();
        let cursorPos = textarea[0].selectionStart;
        let textBefore = text.substring(0, cursorPos).replace(/@[\w]*$/, "@" + name + " ");
        let textAfter = text.substring(cursorPos);

        textarea.val(textBefore + textAfter).focus();
        mentionList.hide();
    });

    $(document).click(function(event) {
        if (!$(event.target).closest(".textarea-wrapper").length) {
            mentionList.hide();
        }
    });
});

$(document).ready(function(){
    let mentionList = $("#mention-list");
    let textarea = $("#post-desc2");

    textarea.on("input", function() {
        let cursorPos = this.selectionStart;
        let text = $(this).val().substring(0, cursorPos);
        let match = text.match(/@([\w]*)$/);

        if (match) {
            let searchQuery = match[1];

            if (searchQuery.length > 0) {
                $.ajax({
                    url: "persons",
                    method: "POST",
                    data: { query: searchQuery },
                    success: function(response) {
                        mentionList.html(response).show();
                    }
                });
            } else {
                mentionList.hide();
            }
        } else {
            mentionList.hide();
        }
    });

    $(document).on("click", ".mention-item", function() {
        let name = $(this).text();
        let text = textarea.val();
        let cursorPos = textarea[0].selectionStart;
        let textBefore = text.substring(0, cursorPos).replace(/@[\w]*$/, "@" + name + " ");
        let textAfter = text.substring(cursorPos);

        textarea.val(textBefore + textAfter).focus();
        mentionList.hide();
    });

    $(document).click(function(event) {
        if (!$(event.target).closest(".textarea-wrapper").length) {
            mentionList.hide();
        }
    });
});

$(document).ready(function(){
    $(document).on("input", "input[name^='Mycomm-']", function() {
        let textarea = $(this);
        let annId = textarea.attr("id").replace("Mycomment-", "");
        let mentionList = $("#mention-list-" + annId);

        let cursorPos = this.selectionStart;
        let text = textarea.val().substring(0, cursorPos);
        let match = text.match(/@([\w]*)$/);

        if (match) {
            let searchQuery = match[1];

            if (searchQuery.length > 0) {
                $.ajax({
                    url: "persons",
                    method: "POST",
                    data: { query: searchQuery },
                    success: function(response) {
                        mentionList.html(response).show();
                    }
                });
            } else {
                mentionList.hide();
            }
        } else {
            mentionList.hide();
        }
    });

    $(document).on("click", ".mention-item", function() {
        let name = $(this).text();
        let textarea = $(this).closest(".textarea-wrapper").find("input[name^='Mycomm-']");
        let cursorPos = textarea[0].selectionStart;
        let text = textarea.val();
        let textBefore = text.substring(0, cursorPos).replace(/@[\w]*$/, "@" + name + " ");
        let textAfter = text.substring(cursorPos);

        textarea.val(textBefore + textAfter).focus();
        $(this).parent().hide();
    });

    $(document).click(function(event) {
        if (!$(event.target).closest(".textarea-wrapper").length) {
            $(".mention-list").hide();
        }
    });
});

$('#post-btn').click(function (e) {
    e.preventDefault();

    var postedBy = $('input[name="posted-by"]').val();
    var postDesc = $('#post-desc').val();
    var audience = $('input[name="audience"]:checked').val();
    var postContent = new FormData();

    postContent.append('postedBy', postedBy);
    postContent.append('postDesc', postDesc);
    postContent.append('audience', audience);

    var files = $('#imageInput')[0].files;
    for (var i = 0; i < files.length; i++) {
        postContent.append('postsimg[]', files[i]);
    }

    $.ajax({
        url: 'postnews',
        type: 'POST',
        data: postContent,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            console.log('Server response:', response);
            if (response.success) {
                $('#default-Modal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert('Error: ' + response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});



// $('#post-btn').click(function (e) {
//     e.preventDefault();

//     var postedBy = $('input[name="posted-by"]').val();
//     var postDesc = $('#post-desc').val();
//     var audience = $('input[name="audience"]:checked').val();
//     var postContent = new FormData();

//     postContent.append('postedBy', postedBy);
//     postContent.append('postDesc', postDesc);
//     postContent.append('audience', audience);

//     var fileInput = $('#imageInput')[0].files[0];
//     if (fileInput) {
//         postContent.append('file', fileInput);
//     }

//     $.ajax({
//         url: 'postnews',
//         type: 'POST',
//         data: postContent,
//         processData: false,
//         contentType: false,
//         dataType: 'json',
//         success: function (response) {
//             console.log('Server response:', response); // Debugging
//             if (response.success) {
//                 $('#default-Modal').modal('hide');
//                 setTimeout(() => {
//                     location.reload();
//                 }, 500); // Small delay to ensure modal hides smoothly
//             } else {
//                 alert('Error: ' + response.error);
//             }
//         },
//         error: function (xhr, status, error) {
//             console.error('AJAX error:', error);
//         }
//     });
// });

    
    $('#post-desc').on('input', function () {
        if ($(this).val().trim() !== '') {
            $('.post-btn').prop('disabled', false);
        } else {
            $('.post-btn').prop('disabled', true);
        }
    });
    $('#post-desc2').on('input', function () {
        if ($(this).val().trim() !== '') {
            $('.post-btn').prop('disabled', false);
        } else {
            $('.post-btn').prop('disabled', true);
        }
    });
});

// Save comment function
function saveComment(postId) {
    const commentInput = document.getElementById(`Mycomment-${postId}`);
    const comIdInput = document.querySelector(`input[name="com-id"][value="${postId}"]`);

    if (!commentInput || !comIdInput) {
        alert('Unable to find input fields for this post.');
        return;
    }

    const comment = commentInput.value.trim();
    const comId = comIdInput.value;

    if (comment === '') {
        alert('Comment cannot be empty!');
        return;
    }

    fetch('save_comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `com_id=${encodeURIComponent(comId)}&Mycomment=${encodeURIComponent(comment)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                commentInput.value = '';
                reloadComments(postId);
            } else {
                alert(data.message || 'An error occurred while saving the comment.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
}

// Reload comments
function reloadComments(postId) {
    $.ajax({
        url: 'comment',
        type: 'POST',
        data: { post_id: postId },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                const comment = response.comment;
                const newCommentHTML = `
                    <div class="cardbox-base-comment">
                        <div class="media m-1">
                            <div class="d-flex mr-1" style="margin-left: 20px;">
                                <a href=""><img class="img-fluid rounded-circle" src="https://teamtngc.com/hris2/pages/empimg/${comment.bi_empno}.JPG" alt="User"></a>
                            </div>
                            <div class="media-body">
                                <p class="m-0">${comment.bi_empfname} ${comment.bi_emplname}</p>
                                <small><span><i class="icon ion-md-pin"></i> ${comment.com_content}</span></small>
                                <div class="comment-reply">
                                    <small><a href="#">Just now</a></small>
                                    <small><a style="cursor: pointer;"></a></small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $(`#prof-${postId} #comment-section`).append(newCommentHTML);
            } else {
                console.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching the new comment:', error);
        }
    });
}
let page = 1;

    function loadMorePosts() {
        $('#loading').show();
        $.ajax({
            url: 'post',
            type: 'POST',
            data: { page: page },
            success: function(response) {
                $('#loading').hide();
                if (response.trim() !== '') {
                    $('#myDiv').append(response);
                    page++;
                } else {
                    $('#loading').html('No more posts available.');
                }
            }
        });
    }

    // Initial load
    $(document).ready(function() {
        loadMorePosts();

        // Detect when the user reaches the bottom of the page
        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height()) {
                loadMorePosts();
            }
        });
    });

    // Get elements
    const firstPick = document.getElementById('first-pick');
    const secondPick = document.getElementById('second-pick');
    const firstPicker = document.getElementById('first-picker');
    const secondPicker = document.getElementById('second-picker');
    const textpost = document.getElementById('post-desc');
    const addTextPost = document.getElementById('add-text-post');
    const image = document.getElementById('img-back');
    const back = document.getElementById('back-picker');
    const post = document.getElementById('post-btn');
    const post2 = document.getElementById('post-btn2');
    const textArea = document.getElementById('post-desc2');

    firstPicker.addEventListener('click', () => {
        firstPick.style.display = 'none';
        secondPick.style.display = 'flex';
    });

    secondPicker.addEventListener('click', () => {
        secondPick.style.display = 'none';
        firstPick.style.display = 'flex';
    });

    image.addEventListener('click', () => {
        textpost.style.display = 'none';
        addTextPost.style.display = 'block';
        post.style.display = 'none';
        post2.style.display = 'block';
    });

    back.addEventListener('click', () => {
        textpost.style.display = 'block';
        addTextPost.style.display = 'none';
        post.style.display = 'block';
        post2.style.display = 'none';
    });

    // Set background image selection
    document.querySelectorAll('#second-pick .background-picker img').forEach(image => {
        image.addEventListener('click', () => {
            const selectedBackground = image.getAttribute('data-bg');
            if (selectedBackground) {
                addTextPost.style.backgroundImage = `url('${selectedBackground}')`;
                addTextPost.style.backgroundSize = 'cover';
                addTextPost.style.backgroundPosition = 'center';
            }
        });
    });

document.getElementById('post-btn2').addEventListener('click', (event) => {
    event.preventDefault();

    const selectedAudience = document.querySelector('input[name="audience"]:checked');
    const audienceValue = selectedAudience ? selectedAudience.value : null;

    if (!audienceValue) {
        alert('Please select an audience before posting.');
        return;
    }

    // Ensure fonts are loaded before rendering
    document.fonts.ready.then(() => {
        const dpi = window.devicePixelRatio || 1;
        html2canvas(addTextPost, {
            scale: 10, // Increase scale for better resolution
            useCORS: true, // Handle cross-origin resources
            logging: true, // Enable debug logs
        })
        .then(canvas => {
            const imageData = canvas.toDataURL('image/png');
            // const imageData = canvas.toDataURL('image/png', 1.0);
            if (!imageData) {
                alert('Failed to generate image data.');
                return;
            }

            const content = textArea.value;

            fetch('save_post', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    image: imageData,
                    content: content,
                    audience: audienceValue,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // alert('Post saved successfully!');
                    location.reload();
                } else {
                    alert('Failed to save the post.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        })
        .catch(error => {
            console.error('Error capturing canvas:', error);
        });
    });
});
const showIcon = document.getElementById('showIcon');
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');

    // Open file input when the fixed div is clicked
    showIcon.addEventListener('click', () => {
      imageInput.click();
    });

    // Handle file selection and preview
    imageInput.addEventListener('change', function () {
      const files = Array.from(this.files);

      // Clear previous previews
      previewContainer.innerHTML = '';

      if (files.length > 0) {
        // Show the preview container if images are selected
        previewContainer.style.display = 'flex';

        // Determine if it's single or multiple images
        if (files.length === 1) {
          previewContainer.className = 'add-photos-video'; // Single image
        } else {
          previewContainer.className = 'add-photos-video multiple'; // Grid for multiple images
        }

        // Preview images
        files.forEach((file) => {
          const reader = new FileReader();
          reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'image-preview';
            previewContainer.appendChild(img);
          };
          reader.readAsDataURL(file);
        });
      } else {
      }

    });
function showDiv(uniqueId) {
    const hiddenDiv = document.getElementById('hidden-div-' + uniqueId);
    if (hiddenDiv) {
        hiddenDiv.style.display = hiddenDiv.style.display === 'none' ? 'block' : 'none';
    }
}
function showEmoji(emojiId) {
    const emojiDiv = document.getElementById('emoji-div-' + emojiId);
    if (emojiDiv) {
        emojiDiv.style.display = emojiDiv.style.display === 'none' ? 'block' : 'none';
    }
}
function insertEmoji(inputId, emojiCode) {
    const inputField = document.getElementById(inputId);

    // Decode the emoji code to get the actual emoji
    const tempElement = document.createElement('span');
    tempElement.innerHTML = emojiCode;
    const emoji = tempElement.textContent || tempElement.innerText;

    if (inputField) {
        inputField.value += emoji; // Append the actual emoji to the input field
    } else {
        console.error('Input field not found:', inputId);
    }
}
document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.emoji-tab-btn');
  const panels = document.querySelectorAll('.emoji-tab-panel');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      // Remove active class from all tabs and panels
      tabs.forEach(t => t.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));

      // Add active class to the clicked tab and corresponding panel
      tab.classList.add('active');
      const panelId = tab.getAttribute('data-tab');
      document.getElementById(panelId).classList.add('active');
    });
  });
});

$(document).on('click', '.report-btn', function () {
    const modal = $(this).closest('.modal');
    const postID = $(this).data('postid');
    const reason = modal.find('textarea[name="reason"]').val();

    const formData = {
        postID: postID,
        Reasons: reason
    };

    $.ajax({
        url: 'report_post',
        method: 'POST',
        data: formData,
        success: function (res) {
            // alert("Post reported successfully!");
            $('.modal').modal('hide');
            location.reload();
        },
        error: function () {
            alert("Error saving the form.");
        }
    });
});

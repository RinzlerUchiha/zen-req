<style type="text/css">
.toggle-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: 45%;
}
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #000;
    transition: .4s;
    border-radius: 34px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #4caf50;
}
input:checked + .slider:before {
    transform: translateX(26px);
}
.status {
    font-size: 18px;
    font-weight: bold;
}
</style>
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<div class="page-wrapper">
    <div class="page-body">
            <div class="mood-title" align="center">
                <h4>Hi! Express yourself—how are you feeling today?</h4>
            </div>
            <div class="toggle-container">
                <label class="switch">
                    <input type="checkbox" id="toggle-switch">
                    <span class="slider"></span>
                </label>
                <span class="status" id="status-label">Public</span>
            </div>
        <div class="row">
            <div id="moods" class="moods">
                <div class="mood-content">
                   <div class="mood-list">
                       <a href="#" class="mood-option" data-mood="happy" style="text-align: center!important;"><img src="/zen/assets/reactions/happy.GIF"/><br>
                        <p>Happy</p></a>
                       <a href="#" class="mood-option" data-mood="weird" style="text-align: center!important;"><img src="https://i.pinimg.com/originals/ff/62/60/ff626067889357dad925e54e44a134c9.gif"/><br>
                        <p>Cheesy</p></a>
                       <a href="#" class="mood-option" data-mood="playful" style="text-align: center!important;"><img src="https://i.pinimg.com/originals/c0/a6/fa/c0a6fade93a7299e3e48b6b3d0623092.gif"/><br>
                        <p>Playful</p></a>
                       <a href="#" class="mood-option" data-mood="haha" style="text-align: center!important;"><img src="/zen/assets/reactions/hahaha.GIF"/><br>
                        <p>Laughter</p></a>
                       <a href="#" class="mood-option" data-mood="hug" style="text-align: center!important;"><img src="https://i.pinimg.com/originals/0e/3e/e5/0e3ee551876e1ad2a39f89e4adf9168a.gif"/><br>
                        <p>Excitement</p></a>
                       <a href="#" class="mood-option" data-mood="relieved" style="text-align: center!important;"><img src="https://i.pinimg.com/originals/df/9f/60/df9f60f39ab6034922babb20cdde15e8.gif"/><br>
                        <p>Calm</p></a>
                       <a href="#" class="mood-option" data-mood="inlove" style="text-align: center!important;"><img src="/zen/assets/reactions/inlove.GIF"/><br>
                        <p>Inlove</p></a>
                       <a href="#" class="mood-option" data-mood="hehe" style="text-align: center!important;"><img src="/zen/assets/reactions/hehe.GIF"/><br>
                        <p>Awkward</p></a>
                   </div>
                   <div class="mood-list">
                       <a href="#" class="mood-option" data-mood="unamused" style="text-align: center!important;"><img src="/zen/assets/reactions/unamused.GIF"/><br>
                        <p>Annoyed</p></a>
                       <a href="#" class="mood-option" data-mood="smirk" style="text-align: center!important;"><img src="/zen/assets/reactions/smirk.GIF"/><br>
                        <p>Sly</p></a>
                       <a href="#" class="mood-option" data-mood="vomit" style="text-align: center!important;"><img src="/zen/assets/reactions/vom.GIF"/><br>
                        <p>Disgust</p></a>
                       <a href="#" class="mood-option" data-mood="crying" style="text-align: center!important;"><img src="/zen/assets/reactions/crying.GIF"/><br>
                        <p>Cry</p></a>
                       <a href="#" class="mood-option" data-mood="anger" style="text-align: center!important;"><img src="/zen/assets/reactions/anger.GIF"/><br>
                        <p>Angry</p></a>
                       <a href="#" class="mood-option" data-mood="eyeroll" style="text-align: center!important;"><img src="/zen/assets/reactions/eyeroll.GIF"/><br>
                        <p>Boredom</p></a>
                       <a href="#" class="mood-option" data-mood="sleepy" style="text-align: center!important;"><img src="/zen/assets/reactions/sleepy.GIF"/><br>
                        <p>Sleepy</p></a>
                       <a href="#" class="mood-option" data-mood="tired" style="text-align: center!important;"><img src="/zen/assets/reactions/tired.GIF"/><br>
                        <p>Frustrated</p></a>
                        
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    const toggleSwitch = document.getElementById('toggle-switch');
    const statusLabel = document.getElementById('status-label');

    // Properly set the status label when toggle switch changes
    toggleSwitch.addEventListener('change', function() {
        statusLabel.textContent = this.checked ? 'Private' : 'Public';
    });

    $('.mood-option').on('click', function (e) {
        e.preventDefault();
        
        const mood = $(this).data('mood');
        const status = statusLabel.textContent; // Get the current status text

        $.ajax({
            url: 'save_mood',
            type: 'POST',
            data: { mood: mood, status: status }, // Send status text, not the element
            success: function (response) {
                window.location.href = '/zen/dashboard';
            },
            error: function () {
                alert('Failed to save mood.');
            }
        });
    });
});

</script>
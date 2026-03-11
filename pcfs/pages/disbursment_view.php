<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
<div class="page-wrapper">
    <div class="page-body">
        <div class="row" style="display: flex;">
            <div class="col-md-2 my-div">
                <?php if (!empty($hotside)) include_once($hotside); ?>
                <div style="height: 50px;padding: 10px;">
                    <span>True North Group of Companies | 2025</span>
                </div>
            </div>
            <!-- <div class="col-md-9" id="right-sided"> -->
            <div id="right-sided">
                <div class="card">
                    <div class="card-block" style="height: 87vh;margin-top: 5px;margin-bottom: 5px;overflow: auto;background-color: #fff;padding: 10px;border-radius: 10px;">

                       <div style="float: right;height: 30px;">
                           <button class="btn btn-inverse btn-outline-inverse btn-mini" id="editBtn"><i class="icofont icofont-edit" style="font-size: 16px;"></i></button>
                           <button class="btn btn-danger btn-outline-danger btn-mini" id="cancelBtn" style="display: none;">Cancel</button>
                           <button class="btn btn-primary btn-outline-primary btn-mini" id="saveBtn" style="display: none;">Save</button>
                       </div>
                       <div style="display: flex;margin-bottom: 10px;flex-wrap: wrap;gap: 15px;">
                       		<div style="display: flex;">
                            PCF#: 
                            <input type="text" class="form-control" name="" value="3392" readonly>
                          </div>
                          <div style="display: flex;">
                            <i class='bx bxs-calendar'></i>
                            <input type="text" class="form-control" name="" value="11/04/24" readonly>
                          </div>
                          <div style="display: flex;">
                            <i class='bx bx-money'></i>
                            <input type="text" class="form-control" name="" value="125.00" style="text-align: right;" readonly>
                          </div>
                       </div>
                       <div style="display: flex;margin-bottom: 10px;flex-wrap: wrap;gap: 15px;">
                          <div style="display: flex;">
                            <i class='bx bxs-buildings'></i>
                            <input type="text" class="form-control" name="" value="Sophia Jewellery Inc." readonly>
                          </div>
                          <div style="display: flex;">
                            <i class='bx bxs-user'></i>
                            <input type="text" class="form-control" name="" value="LBC" readonly>
                          </div>
                          <div style="display: flex;">
                            <i class='bx bx-category'></i>
                            <select class="form-control" disabled>
                              <option>Office and Store Supply</option>
                              <option>Transportation</option>
                              <option>Repairs and Maintenance</option>
                              <option>Communication</option>
                              <option selected>Miscellaneous</option>
                            </select>
                            <!-- <input type="text" class="form-control" name="" value="Office and Store Supply"> -->
                          </div>
                          <div style="display: flex;">
                            OR#: 
                            <input type="text" class="form-control" name="" value="" readonly>
                          </div>
                          <!-- <div style="display: flex;">
                            <a href="#"><i class='bx bx-edit'></i></a>
                          </div> -->
                       </div>
                       <div class="fourth">
                         <div class="attachment-card">
                           <div class="image-container">
                             <img src="assets/pcvlbc.png" id="thumbnail" alt="Attachment">
                             <img src="assets/orlbc.png" id="thumbnail" alt="Attachment">
                           </div>
                         </div>
                         <div class="comment-card">
                            <div class="message-container" id="message-container">
                              <div class="message-card">
                                <img class="sender" src="https://teamtngc.com/hris2/pages/empimg/045-2022-017.jpg" width="40" height="40">
                                <div class="message received">Hello! please change the amount. Thank you</div>
                              </div>
                                <div class="message sent">Updated na sir.</div>
                              <div class="message-card">
                                <img class="sender" src="https://teamtngc.com/hris2/pages/empimg/045-2022-017.jpg" width="40" height="40">
                                <div class="message received">Okay.</div>
                              </div>
                            </div>

                            <div class="message-input">
                              <input type="text" id="message-input" placeholder="Type a message...">
                              <a onclick="sendMessage()"><i class='bx bxs-send'></i></a>
                            </div>
                         </div>
                       </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="overlay">
            <span id="close-btn">&times;</span>
            <img id="overlay-img">
        </div>
    </div>
</div>
<script>
  document.getElementById("editBtn").addEventListener("click", function() {
        document.querySelectorAll(".form-control").forEach(input => input.readOnly = false);
        document.querySelectorAll(".form-control").forEach(select => select.disabled = false);
        document.getElementById("editBtn").style.display = "none";
        document.getElementById("saveBtn").style.display = "inline-block";
        document.getElementById("cancelBtn").style.display = "inline-block";
    });
    
    document.getElementById("cancelBtn").addEventListener("click", function() {
        document.querySelectorAll(".form-control").forEach(input => input.readOnly = true);
        document.querySelectorAll(".form-control").forEach(select => select.disabled = true);
        document.getElementById("editBtn").style.display = "inline-block";
        document.getElementById("saveBtn").style.display = "none";
        document.getElementById("cancelBtn").style.display = "none";
  });
  function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const messageContainer = document.getElementById('message-container');

    if (messageInput.value.trim() !== '') {
      const newMessage = document.createElement('div');
      newMessage.className = 'message sent';
      newMessage.textContent = messageInput.value;
      messageContainer.appendChild(newMessage);
      messageInput.value = '';

      // Auto-scroll to the bottom
      messageContainer.scrollTop = messageContainer.scrollHeight;
    }
  }
  document.addEventListener("DOMContentLoaded", function () {
      const thumbnail = document.getElementById("thumbnail");
      const overlay = document.getElementById("overlay");
      const overlayImg = document.getElementById("overlay-img");
      const closeBtn = document.getElementById("close-btn");
  
      // Show overlay on image click
      thumbnail.addEventListener("click", function () {
          overlay.style.display = "flex";
          overlayImg.src = this.src;
      });
  
      // Hide overlay when clicking the close button
      closeBtn.addEventListener("click", function () {
          overlay.style.display = "none";
      });
  
      // Hide overlay when clicking outside the image
      overlay.addEventListener("click", function (e) {
          if (e.target === overlay) {
              overlay.style.display = "none";
          }
      });
  });

</script>
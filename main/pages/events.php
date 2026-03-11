<div class="events">
    <ul id="events-list">
        <?php
             if (!empty($events)) {
                  foreach ($events as $k) {
        ?>
            <li>
                <div class="time">
                    <h2>
                        <?=$k['days']?> <br><span><?=$k['months']?></span>
                    </h2>
                </div>
                <div class="details" style="background-image: url('https://e-classtngcacademy.s3.ap-southeast-1.amazonaws.com/zenhub/events/<?=$k['event_file']?>');"></div>
                <div style="clear: both;"></div>
            </li>
        <?php }} ?>
    </ul>
</div>

<script>
const eventsList = document.querySelector('.events ul');
let scrollSpeed = 0.5; 
let position = 0;

eventsList.innerHTML += eventsList.innerHTML; 

function autoScroll() {
  position -= scrollSpeed;
  eventsList.style.transform = `translateY(${position}px)`;

  if (-position >= eventsList.scrollHeight / 2) {
    position = 0;
  }

  requestAnimationFrame(autoScroll);
}

autoScroll();

</script>

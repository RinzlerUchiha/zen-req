<style type="text/css">
    .sponsored-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 16px;
        }
        .ad-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
            /*padding: 10px;*/
            border-radius: 8px;
        }
        .ad-item:hover {
            background-color: #f0f2f5;
        }
        .ad-image {
            width: 80px;
            height: 100px;
            border-radius: 8px;
            margin-right: 12px;
        }
        .ad-title {
            font-size: 14px;
            color: #333;
        }
</style>
<div id="memo"> 
    <!-- <span><a href="#"><h6>Government</h6></a></span> -->
    <span style="display: flex;justify-content: space-between;height: 25px;">
        <a href="#" data-toggle="modal" data-target="#"><h6>Government</h6></a>
        <!-- <a href="#" class="btn btn-outline-primary btn-mini" data-toggle="modal" data-target="#gov-Modal"><h6><i class="fa fa-plus-circle"></i></h6></a> -->
    </span>
    <div class="m-portlet__body">
        <div id="ads-list"></div>
    </div>
</div>
<script type="text/javascript">
function openImage() {
    console.log('Input clicked');
    window.open('https://i.pinimg.com/564x/b0/68/56/b06856d929b6066d2281c9f065a29e31.jpg', '_blank');
}
fetch('govern')
 .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error fetching ads:', data.error);
                    return;
                }

                const ads = data;
                const adsList = document.getElementById('ads-list');
                let currentIndex = 0;

                function displayAds() {
                    adsList.innerHTML = '';
                    const itemsToShow = ads.slice(currentIndex, currentIndex + 2);
                    itemsToShow.forEach(ad => {
                        const adItem = document.createElement('div');
                        adItem.className = 'ad-item';
                        if (ad.image.includes('<figure')) {
                            const imagePattern = /<img\s+[^>]*src=["']([^"']+)["']/i;
                            const match = ad.image.match(imagePattern);
                            if (match) ad.image = match[1];
                        }
                        ad.image = ad.image.replace('../announcement', 'https://teamtngc.com/hris2/pages/announcement');
                        adItem.innerHTML = `
                            <img src="${ad.image}" alt="${ad.title}" class="ad-image" onclick="openImageOverlay('${ad.image}')">
                            <a href="" class="ad-title">${ad.title}</a>
                        `;
                        adsList.appendChild(adItem);
                    });
                    currentIndex = (currentIndex + 2) % ads.length;
                }

                displayAds();
                setInterval(displayAds, 6000);
            })
            .catch(error => console.error('Error fetching ads:', error));

        function openImageOverlay(imageUrl) {
            window.open(imageUrl, '_blank');
        }

document.addEventListener("DOMContentLoaded", function() {
    window.openImageOverlay = function(src) {
        document.getElementById('overlayImage').src = src;
        document.getElementById('imageOverlay').style.display = 'flex';
    }

    window.closeImageOverlay = function() {
        document.getElementById('imageOverlay').style.display = 'none';
    }
});
</script>

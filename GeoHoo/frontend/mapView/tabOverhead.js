let map = null; //OL map
let vectorSource = null;//contains features
//user coords
let lat = -1;
let long = -1;
let isMapVisible = true;
let closestPost = 999;

const CIRCLE_SIZE = 30; //meters



function updateSelectedTab(selectedTabItem, tabId) {
    // Remove the class from all elements with the class "selected"
    document.querySelectorAll('.selectedTabItem').forEach(element => {
      element.classList.remove('selectedTabItem');
    });
    selectedTabItem.classList.add('selectedTabItem');



    document.querySelectorAll('.tabSelected').forEach(element => {
        element.classList.remove('tabSelected');
    });
    document.getElementById(tabId).classList.add('tabSelected');

    if(tabId == "mapSection"){
        isMapVisible = true;
    }else{
        isMapVisible = false;
    }

    if(tabId != "cameraSection"){ stopCamera(); }

}




//////cam
let camStream = null;
async function startCamera() {
    const videoElement = document.getElementById('camera');
  
    if (camStream) {
        videoElement.srcObject = camStream;
        return;
    }

    try {
      // Request access to the camera
      camStream = await navigator.mediaDevices.getUserMedia({ video: true });
      videoElement.srcObject = camStream;
    } catch (err) {
      console.error("Camera access denied: ", err);
    }
  }

function stopCamera() {
    if (camStream) {
        camStream.getTracks().forEach(track => track.stop());
        camStream = null;
        const videoElement = document.getElementById('camera');
        videoElement.srcObject = null;
    }
}




function takePicture(){

    console.log("pic");

    const videoElement = document.getElementById('camera');
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    // Set canvas size to video size
    canvas.width = videoElement.videoWidth;
    canvas.height = videoElement.videoHeight;

    // Draw the current frame from the video onto the canvas
    context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

    // Get the image as a data URL (base64 encoded)
    const imageDataUrl = canvas.toDataURL('image/png');

    // Display the captured image
    const imgElement = document.getElementById('previewImage');
    imgElement.src = imageDataUrl;

    document.getElementById("imgModal").style.display = "block";
    document.getElementById("accpetButton").onclick = ()=>{
        postImage(canvas);
    }
    
}


async function postImage(canvas){
    if(long == -1 || lat == -1){
        return;
    }

    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg'));
    const formData = new FormData();
    formData.append('files[]', blob, 'photo.jpg');
    formData.append('about', document.getElementById("postText").value.trim());
    formData.append('lon', long);
    formData.append('lat', lat);


    fetch("https://orellion.com/GeoHoo/assets/createPost.php", {
        method: "POST",
        body: formData,
        }).then((response) => {
            return response.text().then((text)=>{
                console.log(text);


                if(response.status == 200){
                    console.log("[post: good]");
                    document.getElementById("imgModal").style.display = "none";
                }else{
                    console.log("[post: bad]");
                }
            })
        }).then(()=>{
            getNearPosts();
        })

}



/////
let nearPosts = [];

function renderNearbyPostFeatures(){
    if(nearPosts.length == 0){
        return;
    }

    nearPosts.sort((a, b) => {
        let dateA = new Date(a.timestamp);
        let dateB = new Date(b.timestamp);
        return dateB - dateA;//latest first
    });



    //add new features
    for(let i = 0; i < nearPosts.length; i++){
        let postObj = nearPosts[i];

        let circle = new ol.geom.Circle(ol.proj.fromLonLat([postObj.location[0], postObj.location[1]]), CIRCLE_SIZE);
        let circleFeature = new ol.Feature(circle);
        circleFeature.setId('post-radius-' + i);
        vectorSource.addFeature(circleFeature);

        /////
        const coord1 = [long, lat]; // [lon, lat]
        const coord2 = [nearPosts[i].location[0], nearPosts[i].location[1]]; // [lon, lat]
        closestPostTemp = ol.sphere.getDistance(coord1, coord2);
        console.log(closestPostTemp);
        if(closestPostTemp < closestPost){
            closestPost = closestPostTemp;
            document.getElementById("postDist").innerHTML = Math.round(closestPostTemp * 1000) / 1000 + "m";
        }
    }
    
    renderNearbyPosts();
}
function renderNearbyPosts(){
    let searchArea = document.getElementById("nearestFeatsSearch");
    let postArea = document.getElementById("postArea");

    if(nearPosts.length == 0 || closestPost > CIRCLE_SIZE){
        searchArea.style.display = "block";
        postArea.style.display = "none";
        return;
    }

    searchArea.style.display = "none";
    postArea.style.display = "block";
    postArea.innerHTML = "";

    for(let i = 0; i < nearPosts.length; i++){
        let postObj = nearPosts[i];
        const coord1 = [long, lat]; // [lon, lat]
        const coord2 = [nearPosts[i].location[0], nearPosts[i].location[1]]; // [lon, lat]
        closestPostTemp = ol.sphere.getDistance(coord1, coord2);
        if(closestPostTemp > CIRCLE_SIZE){
            continue;
        }

        let d = document.createElement("div");
        d.style = "";
        d.className = "";
        d.innerHTML = ` <div class="postDisplay">
                            <div>
                                <img src="https://orellion.com/GeoHoo/assets/${postObj.image_URL}" style="width: 100%;">
                            </div>
                            <div>
                                ${postObj.text}
                            </div>
                        </div>`;
        postArea.appendChild(d);
    }

}

async function getNearPosts(){
    if(long == -1 || lat == -1){
        return;
    }

    const formData = new FormData();
    formData.append('lon', long);
    formData.append('lat', lat);
    console.log(long, lat);


    fetch("https://orellion.com/GeoHoo/assets/getNearPosts.php", {
        method: "POST",
        body: formData,
        }).then((response) => {
            return response.text().then((text)=>{
                //console.log(text);
                if(response.status == 200){
                    console.log("[Get posts: good]");
                    nearPosts = JSON.parse(text);
                }else{
                    console.log("[Get posts: bad]");
                }
            })
        }).then(()=>{
           renderNearbyPostFeatures();
        })

}







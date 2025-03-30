
//https://orellion.com/GeoHoo/assets/test.php


function auth(type, callback){
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();

    if(type == "signup"){
        email = document.getElementById("emailSignUp").value.trim();
        password = document.getElementById("passwordSignUp").value.trim();
    }


    console.log(email, password);
    //SANITY CHECK
    if(email.length < 5 || password.length <= 5){
        console.log("Failed sanity check");
        return;
    }

    console.log(type, email, password);
    const formData = new FormData();
    formData.append("action", type);
    formData.append("username", email);
    formData.append("password", password);
    formData.append("image_url", "");

    fetch("https://orellion.com/GeoHoo/assets/auth.php", {
        method: "POST",
        body: formData,
        }).then((response) => {
            return response.text().then((text)=>{
                console.log(text);


                if(response.status == 200){
                    console.log("[auth: good]");
                    if(text == "Login successful"){
                        window.location.replace("../mapView/index.html");

                    }
                }else{
                    console.log("[auth: bad]");
                }
            })
        }).then(()=>{
            callback();
        })
}


function slide(direction){
    document.getElementById("boxWrapper").style.transform = `translateX(${(-50 * direction)}%)`;
}
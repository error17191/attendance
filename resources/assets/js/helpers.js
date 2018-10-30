window.makeRequest = (params) => {
    if (params.method == 'get' && !params.cache) {
        // params.url = params.url + '?t=' + new Date().getTime();
    }
    return new Promise((resolve) => {
        let promise = axios.request(params)
            .then(resolve)
            .catch((error) => {
                //error.response.status
                // window.location.reload();
            })
    });
};


window.partitionSeconds = function (seconds) {
    let hours = Math.floor(seconds / 60 / 60);
    seconds -= hours * 60 * 60;
    let minutes = Math.floor(seconds / 60);
    seconds -= minutes * 60;
    return {hours,minutes,seconds};
};

window.capitalize = function(inputString){
    if (!inputString) {
        return '';
    }
    inputString = inputString.toString();
    let wordsArray = inputString.split('_');
    let outputWords = [];
    for (let i in wordsArray) {
        let words = wordsArray[i].split(' ');
        for (let word in words){
            words[word] = words[word].charAt(0).toUpperCase() + words[word].slice(1);
            outputWords.push(words[word]);
        }
    }
    return outputWords.join(' ');
};

window.loadScript = function(url, callback){
    let script = document.createElement("script");
    script.type = "text/javascript";

    if (script.readyState){  //IE
        script.onreadystatechange = function(){
            if (script.readyState == "loaded" ||
                script.readyState == "complete"){
                script.onreadystatechange = null;
                callback();
            }
        };
    } else {  //Others
        script.onload = function(){
            callback();
        };
    }

    script.src = url;
    document.getElementsByTagName("head")[0].appendChild(script);
}

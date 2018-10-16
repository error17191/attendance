<template>
    <div>
        <button @click="showScanner" class="btn btn-primary">Scan QR Code</button>
        <div v-if="showOverlay" id="qr-overlay">
            <button @click="hideScanner" class="close">&times;</button>
            <video id="preview"></video>
            <div class="square" :class="{'has-error': feedback == 'error' , 'has-success': feedback == 'success'}">
                <div v-if="feedback" :class="feedback">
                    <i class="fas"
                       :class="{'fa-times' : feedback == 'error' , 'fa-check-circle' : feedback == 'success'}"></i>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        name: "QRCode",
        data() {
            return {
                messages: [],
                showOverlay: false,
                feedback: null
            }
        },
        methods: {
            showScanner(){
              this.showOverlay = true;
              setTimeout(() => {
                  this.initScanner();
              },0)
            },
            hideScanner(){
              this.showOverlay = false;
              this.feedback = null;

            },
            showError(){
                this.feedback = 'error';
            },
            showSuccess(){
                this.feedback = 'success';
            },
            initScanner() {
                let scanner = new Instascan.Scanner({video: document.getElementById('preview')});
                scanner.addListener('scan', content => {
                    let data = content.split('__');
                    let user_id = data[0];
                    let machine_id = data[1];
                    axios.post('machine/add', {'user_id': user_id, 'machine_id': machine_id}).then(response => {
                        this.showSuccess();
                        scanner.stop();
                        setTimeout(() => {
                            this.hideScanner();

                        },2000)

                    }).catch(error => {
                        this.showError();
                        setTimeout(() => {
                            this.feedback = null;

                        },2000)
                    })
                });
                Instascan.Camera.getCameras().then(function (cameras) {
                    if (cameras.length > 0) {
                        // alert(cameras.length);
                        scanner.start(cameras[1]);
                    } else {
                        // alert('No cameras found.');
                    }
                }).catch(function (e) {
                    // alert(e);
                });
            }
        }

    }
</script>

<style scoped>
    #qr-overlay {
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        background-color: rgba(0,0,0,0.85);
        padding: 0;
    }
    #qr-overlay #preview{
        width: 100%;
        height: 100%;
        top:0;
        left:0;
        position: absolute;
        z-index: 100;
        margin: 0;
    }
    #qr-overlay .close{
        position: absolute;
        z-index: 101;
        color: white;
        font-size: 45px;
        left: 20px;
        top: 20px;
    }
    #qr-overlay .square{
        position: absolute;
        z-index: 101;
        background-color: transparent;
        border: 2px dashed white;
        -webkit-border-radius: 20px;
        -moz-border-radius: 20px;
        border-radius: 20px;
        width: 300px;
        height: 300px;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -70%);
        -moz-transform: translate(-50%, -70%);
        -ms-transform: translate(-50%, -70%);
        -o-transform: translate(-50%, -70%);
        transform: translate(-50%, -70%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #qr-overlay .square.has-error{
        border-color: red;
    }
    #qr-overlay .square.has-success{
        border-color: green;
    }
    @media screen and (max-width: 450px) {
        #qr-overlay .square{
            width: 250px;
            height: 250px;
        }
    }
    #qr-overlay .square .error{
        color: red;
        font-size: 200px;
    }
    #qr-overlay .square .success{
        color: green;
        font-size: 200px;
    }
</style>

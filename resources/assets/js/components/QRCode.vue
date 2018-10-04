<template>
    <div>
        <video id="preview"></video>
    </div>
</template>

<script>

    export default {
        name: "QRCode",
        data() {
            return {
                messages: []
            }
        },
        mounted() {
            let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
            scanner.addListener('scan', function (content) {
                alert(content)
            });
            Instascan.Camera.getCameras().then(function (cameras) {
                if (cameras.length > 0) {
                    scanner.start(cameras[0]);
                } else {
                    alert('No cameras found.');
                }
            }).catch(function (e) {
                alert(e);
            });

        }

    }
</script>

<style scoped>

</style>

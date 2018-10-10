<template>
    <div>
        <div>
            <video id="preview"></video>
        </div>
        <div v-for="message in messages">
            <p>{{message}}</p>
        </div>
    </div>
</template>

<script>

    export default {
        name: "QRCode",
        data() {
            return {
                messages: [],

            }
        },
        mounted() {
            let scanner = new Instascan.Scanner({video: document.getElementById('preview')});
            scanner.addListener('scan', function (content) {
                let data = content.split('__');
                let user_id = data[0];
                let machine_id = data[1];
                axios.post('machine/add', {'user_id': user_id, 'machine_id': machine_id}).then(response => {
                    this.messages.push(response.data.messages);
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
</script>

<style scoped>

</style>

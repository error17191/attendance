<template>
    <div>
        <b-list-group>
            <div v-for="message in messages">
                <b-list-group-item>{{message}}</b-list-group-item>
            </div>
        </b-list-group>
    </div>
</template>

<script>
    export default {
        name: "Notifications",
        data() {
            return {
                messages: []
            }
        },
        mounted() {
            window.Echo.private('App.User.' + localStorage.getItem('user_id'))
                .notification((notification) => {
                    this.messages.push(notification.message);
                });

            axios.get('/notifications').then(res=>{

                let message_data=res.data.messages;
                for (let x=0;x<message_data.length;x++){

                    let messageObj=JSON.parse((message_data[x].data));
                    this.messages.push(messageObj.message)
                }

            });
        }

    }
</script>

<style scoped>

</style>
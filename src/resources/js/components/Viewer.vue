<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <button class="btn btn-success" @click="joinBroadcast">
                    Join Stream</button
                ><br />

                <video autoplay ref="viewer"></video>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Viewer",
    props: [
        "auth_user_id",
        "stream_id",
        "turn_url",
        "turn_username",
        "turn_credential",
    ],
    data() {
        return {
            broadcasterId: null,
        };
    },
    methods: {
        joinBroadcast() {
            console.log("Want to join")
        },
    },
};
</script>

<style scoped>
</style>

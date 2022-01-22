<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <button class="btn btn-success" @click="startStream">
                    Start Stream</button>
                <br />
                <p v-if="isVisibleLink" class="my-5">
                    Share the following streaming link:
                </p>
                <video autoplay ref="broadcaster"></video>
            </div>
        </div>
    </div>
</template>

<script>
import { getPermissions } from "../helpers";
import Echo from "laravel-echo";
import Peer from "simple-peer";
export default {
    name: "Broadcaster",
    props: [
        "auth_user_id",
    ],
    data() {
        return {
            streamingPresenceChannel: null,
            isVisibleLink: false,
            streamingUsers: []
        };
    },
    computed: {
        streamId() {
            //TODO: improve streamId generation code.
            return `${this.auth_user_id}randomstring`;
        }
    },
    methods: {
        async startStream() {
            const stream = await getPermissions();
            this.$refs.broadcaster.srcObject = stream;
            this.initializeStreamingChannel();
            this.isVisibleLink = true;
        },
        initializeStreamingChannel() {
            this.streamingPresenceChannel = window.Echo.join(
                `streaming-channel.${this.streamId}`
            );

            this.streamingPresenceChannel.here((users) => {
                this.streamingUsers = users;
            });

            this.streamingPresenceChannel.joining((user) => {
                console.log('New User',user);
            })
        }
    },
};
</script>

<style scoped>
</style>

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
import Peer from "simple-peer";
import Echo from "laravel-echo";
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
            streamingPresenceChannel: null,
            broadcasterPeer: null,
            broadcasterId: null,
        };
    },
    methods: {
        joinBroadcast() {
            console.log("Want to join");
            this.initializeStreamingChannel();
            this.initializeSignalOfferChannel();
        },

        initializeStreamingChannel() {
            console.log("Want to join init");

            this.streamingPresenceChannel = window.Echo.join(
                `streaming-channel.${this.stream_id}`
            );
        },

        initializeSignalOfferChannel() {
            console.log("Want to offer init");

            window.Echo.private(`stream-signal-channel.${this.auth_user_id}`)
                .listen(
                    "StreamOffer",
                    ({data}) => {
                console.log("Signal offer from private channel");
                this.broadcasterId = data.broadcaster;
                this.createViewerPeer(data.offer,data.broadcaster);
            })
        },

        createViewerPeer(offer, broadcaster) {
            const peer = new Peer({
                initiator: false,
                trickle: false,
                config: {
                    iceServers: [
                        {
                            urls: "stun.l.google.com:19302",
                        }
                    ],
                },
            });

            //Add a RTCRtpTransceiver to the connection. Can be used to add transceivers before adding tracks. Automatically called as neccesary by addTrack.
            peer.addTransceiver("video",{ direction: "recvonly" });
            peer.addTransceiver("audio",{ direction: "recvonly" });

            this.handlePeerEvents(
                peer,
                offer,
                broadcaster,
                this.removeBroadcastVideo
            );

            this.broadcasterPeer = peer;
        },

        handlePeerEvents(peer, offer, broadcaster, removeBroadcastVideo) {
            peer.on("signal", (data) => {
                axios
                    .post("/stream-answer",{
                        broadcaster,
                        answer: data,
                    })
                    .then((res) => {
                        console.log(res);
                    })
                    .catch((err) => {
                        console.log(err);
                    })
            });//Signal

            peer.on("stream", (stream) => {
                this.$refs.viewer.srcObject = stream;
            });

            peer.on("track", (track, stream) => {
                console.log("onTrack");
            });

            peer.on("connect", () => {
                console.log("Viewer Peer connected");
            });

            peer.on("close",() => {
                console.log("Viewer Peer closed");
                peer.destroy();
                removeBroadcastVideo();
            });

            peer.on("error", (err) => {
                console.log("handle error:"+err);
            });

            const updateOffer = {
                ...offer,
                sdp: `${offer.sdp}\n`
            };

            peer.signal(updateOffer);
        },

        removeBroadcastVideo() {
            console.log("Remove broadcast clear html5 object");
            alert("Livestream ended by broadcaster");
            const tracks = this.$refs.viewer.srcObject.getTracks();
            tracks.forEach((track) => {
                track.stop();
            })
            this.$refs.viewer.srcObject = null;
        }
    },
};
</script>

<style scoped>
</style>

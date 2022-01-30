<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <button class="btn btn-success" @click="startStream" v-if="isStartStream">
                    Start Stream</button>
                <br />
                <p v-if="isVisibleLink" class="my-5">
                    Share the following streaming link: {{ streamLink }}
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
        "turn_url",
        "turn_username",
        "turn_credential",
    ],
    data() {
        return {
            streamingPresenceChannel: null,
            isVisibleLink: false,
            isStartStream: true,
            currentlyContactedUser: null,
            streamingUsers: [],
            allPeers: {}
        };
    },
    computed: {
        streamId() {
            //TODO: improve streamId generation code.
            return `${this.auth_user_id}randomstring`;
        },
        streamLink() {
            //TODO: For now temp localhost url.
            return `http://127.0.0.1:8080/streaming/${this.streamId}`;
        }
    },
    methods: {
        async startStream() {
            const stream = await getPermissions();
            this.$refs.broadcaster.srcObject = stream;
            this.initializeStreamingChannel();
            this.initializeSignalAnswerChannel();
            this.isVisibleLink = true;
            this.isStartStream = false;
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
                // if this new user is not already on the call, send your stream offer
                const joiningUserIndex = this.streamingUsers.findIndex(
                    (data) => data.id === user.id
                );

                console.log(joiningUserIndex);
                if (joiningUserIndex < 0) {
                    this.streamingUsers.push(user);

                    this.currentlyContactedUser = user.id;

                    this.$set(
                        this.allPeers,
                        `${user.id}`,
                        this.peerCreator(
                            this.$refs.broadcaster.srcObject,
                            user,
                            this.signalCallback
                        )
                    );

                    this.allPeers[user.id].create();

                    this.allPeers[user.id].initEvents();
                }
            })//joining

            this.streamingPresenceChannel.leaving((user) => {
                console.log(user.name , "Left");

                this.allPeers[user.id].getPeer().destroy();

                delete this.allPeers[user.id];

                if (user.id === this.auth_user_id) {
                    this.streamingUsers = [];
                } else {
                    const leavingUserIndex = this.streamingUsers.findIndex(
                        (data) => data.id === user.id
                    );

                    this.streamingUsers.splice(leavingUserIndex,1);
                }
            });
        },
        initializeSignalAnswerChannel() {
            window.Echo.private(`stream-signal-channel.${this.auth_user_id}`).listen(
                "StreamAnswer",
                ({data}) => {
                    console.log("Signal answer from private channel");

                    if (data.answer.renegotiate) {
                        console.log("renegotiate");
                    }

                    //Session description protocol
                    if(data.answer.sdp) {
                        const updateSignal = {
                            ...data.answer,
                            sdp: `${data.answer.sdp}\n`,
                        };

                        this.allPeers[this.currentlyContactedUser]
                            .getPeer()
                            .signal(updateSignal);
                    }
                }
            );
        },//initializeSignalAnswerChannel
        peerCreator(stream, user, signalCallback) {
            let peer;
            return {
                create: () => {
                    peer = new Peer({
                        initiator: true,
                        trickle: false,
                        stream: stream,
                        config: {
                            iceServers: [
                                {
                                    urls: "stun.l.google.com:19302"
                                }
                            ]
                        }
                    });
                },
                getPeer: () => peer,
                initEvents: () => {
                    peer.on("signal",(data) => {
                        //send offer
                        signalCallback(data, user);
                    });

                    peer.on("stream",(stream) => {
                        console.log("onStream");
                    });

                    peer.on("track", (track, stream) => {
                        console.log("onTrack");
                    });

                    peer.on("connect", () => {
                        console.log("Broadcaster Peer connected");
                    });

                    peer.on("close", () => {
                        console.log("Broadcaster Peer closed");
                    });

                    peer.on("error", (err) => {
                        console.log("handle error gracefully");
                    });
                },
            };
        },
        signalCallback(offer, user) {
            axios
                .post("/stream-offer",{
                    broadcaster: this.auth_user_id,
                    receiver: user,
                    offer,
                })
                .then((res) => {
                    console.log(res);
                })
                .catch((err) => {
                    console.log(err);
                })
        }
    },
};
</script>

<style scoped>
</style>

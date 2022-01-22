<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <button class="btn btn-success" @click="startStream">
                    Start Stream</button
                ><br />
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
export default {
    name: "Broadcaster",
    props: [
        "auth_user_id",
    ],
    data() {
        return {
            isVisibleLink: false,
        };
    },
    computed: {
    },
    methods: {
        async startStream() {
            const stream = await getPermissions();
            this.$refs.broadcaster.srcObject = stream;
            this.isVisibleLink = true;
        }
    },
};
</script>

<style scoped>
</style>

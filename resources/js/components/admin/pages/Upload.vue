<template>
    <div class="upload-container">
        <v-card>
            <file-pond @pushTrack="pushTrack($event)"></file-pond>
        </v-card>
        <v-card outlined>
            <track-list
                :tracks="tracks"
                @deleteTracks="deleteTracks($event)"
                :hideCreate="true"
            ></track-list>
        </v-card>
    </div>
</template>

<script>
import FilePond from "../elements/upload/FilePond.vue";
import TrackList from "../List/Track.vue";
export default {
    components: { FilePond, TrackList },
    data() {
        return {
            tracks: []
        };
    },
    methods: {
        pushTrack(track) {
            this.tracks.unshift(JSON.parse(track));
        },
        deleteTracks(tracks) {
            axios
                .post("/api/admin/delete-tracks", {
                    tracks
                })
                .then(() => {
                    this.$notify({
                        group: "foo",
                        type: "success",
                        title: this.$t("Deleted"),
                        text: this.$t("Songs deleted successfully")
                    });
                    this.tracks = this.tracks.filter(
                        track => !tracks.includes(track.id)
                    );
                });
        }
    }
};
</script>

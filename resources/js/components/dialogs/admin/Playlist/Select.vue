<template>
    <v-card class="p-3">
        <v-container>
            <v-row>
                <v-col cols="12">
                    <v-card-title>
                        {{ $t("Select Playlist") }}
                        <v-spacer></v-spacer>
                        <v-spacer></v-spacer>
                        <v-card-actions>
                            <v-btn
                                small
                                dense
                                class="primary"
                                @click="createPlaylistDialog = true"
                            >
                                <v-icon left>$vuetify.icons.plus</v-icon>
                                {{ $t("New Playlist") }}
                            </v-btn>
                        </v-card-actions>
                        <v-btn
                            text
                            small
                            @click="$emit('cancel')"
                            :disabled="loading"
                            >{{ $t("Cancel") }}</v-btn
                        >
                    </v-card-title>
                    <v-select
                        class="py-3"
                        :items="playlists"
                        :label="$t('Select Playlist')"
                        item-value="id"
                        item-text="title"
                        v-model="selectedPlaylists"
                        outlined
                        chips
                        deletable-chips
                        :loading="loading"
                        multiple
                    ></v-select>
                    <v-layout justify-center>
                        <v-btn
                            class="success"
                            @click="
                                addTracksToPlaylist(tracks, selectedPlaylists)
                            "
                            :disabled="!selectedPlaylists.length || loading"
                            :loading="loading"
                            width="150"
                            >{{ $t("Add") }}</v-btn
                        >
                    </v-layout>
                </v-col>
            </v-row>
        </v-container>
        <v-dialog v-model="createPlaylistDialog" max-width="500px" persistent>
            <create-playlist
                @created="fetchPlaylists"
                v-if="createPlaylistDialog"
                @cancel="createPlaylistDialog = false"
            ></create-playlist>
        </v-dialog>
    </v-card>
</template>

<script>
import CreatePlaylist from "./Create.vue";
export default {
    components: { CreatePlaylist },
    props: ["tracks"],
    created() {
        this.fetchPlaylists();
    },
    data() {
        return {
            loading: false,
            createPlaylistDialog: false,
            playlists: [],
            selectedPlaylists: []
        };
    },
    methods: {
        fetchPlaylists() {
            this.createPlaylistDialog = false;
            this.loading = true;
            axios
                .get("/api/admin/playlists")
                .then(res => {
                    this.playlists = res.data;
                })
                .finally(() => (this.loading = false));
        },
        addTracksToPlaylist(tracks, playlists) {
            this.loading = true;
            axios
                .post("/api/admin/add-tracks-to-playlists", {
                    playlists,
                    tracks
                })
                .then(() => {
                    this.$notify({
                        group: "foo",
                        type: "success",
                        title: this.$t("Added"),
                        text: this.$t("Track added to playlists successfully")
                    });
                })
                .finally(() => (this.loading = false, this.$emit('cancel')));
        }
    }
};
</script>

<style></style>

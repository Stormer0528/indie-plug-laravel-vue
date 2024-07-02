<template>
    <div class="songs-wrapper">
        <v-card outlined>
            <v-card-title>
                <v-icon color="primary" x-large>$vuetify.icons.music-note</v-icon>
                <v-btn
                    class="mx-2"
                    dark
                    small
                    color="primary"
                    @click="editVideo('new')"
                >
                    <v-icon>$vuetify.icons.plus</v-icon>
                    {{ $t("New") }}
                </v-btn>
                <v-spacer></v-spacer>
                <v-spacer></v-spacer>
                <div class="admin-search-bar">
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        :label="$t('Search')"
                        single-line
                        hide-details
                    ></v-text-field>
                </div>
            </v-card-title>
            <v-data-table
                :no-data-text="$t('No data available')"
                :loading-text="$t('Fetching data') + '...'"
                :headers="headers"
                :items="videos || []"
                :loading="!videos"
                :items-per-page="25"
                :search="search"
                class="elevation-1"
            >
                <template v-slot:item.cover="{ item }">
                    <div class="img-container py-2">
                        <v-img
                            :src="
                                (item.cover && item.cover.image) || item.cover
                            "
                            :alt="item.title"
                            class="user-avatar song-cover"
                            width="50"
                            height="50"
                        >
                            <div
                                class="upload-percentage"
                                v-if="
                                    item.progress != null && item.progress < 100
                                "
                            >
                                <div class="content-text">
                                    <template v-if="item.progress < 99">
                                        {{ item.progress }}%
                                    </template>
                                    <template v-else>
                                        <v-progress-circular
                                            :size="15"
                                            :width="3"
                                            color="grey"
                                            indeterminate
                                        ></v-progress-circular>
                                    </template>
                                </div>
                            </div>
                        </v-img>
                    </div>
                </template>
                <template v-slot:item.title="{ item }">
                    <router-link
                        class="router-link"
                        :to="{ name: 'video', params: { id: item.id } }"
                        target="_blank"
                    >
                        {{ item.title }}
                    </router-link>
                </template>
                <template v-slot:item.operations="{ item }">
                    <v-btn
                        class="mx-2"
                        @click="editVideo(item)"
                        x-small
                        fab
                        dark
                        color="info"
                    >
                        <v-icon
                            :dark="
                                $store.state.darkTheme ||
                                    $store.getters.getSettings.defaultTheme ==
                                        'dark'
                            "
                            >$vuetify.icons.pencil</v-icon
                        >
                    </v-btn>
                    <v-btn
                        class="mx-2"
                        @click="deleteVideo(item.id)"
                        x-small
                        fab
                        dark
                        color="error"
                    >
                        <v-icon>$vuetify.icons.delete</v-icon>
                    </v-btn>
                </template>
                <template v-slot:item.artists="{ item }">
                    <artists :artists="item.artists"></artists>
                </template>
                <template v-slot:item.created_at="{ item }">
                    {{ moment(item.created_at).format("ll") }}
                </template>
            </v-data-table>
        </v-card>
        <v-dialog
            persistent
            v-model="editDialog"
            max-width="950"
            @click:outside="hideAllvideos"
        >
            <template v-for="video in videos">
                <edit-video-dialog
                    :key="video.id"
                    uploader="artist"
                    v-if="video.isActive"
                    v-show="isShowing(video.id)"
                    @updated="videoEdited(video.id)"
                    @progress="updateProgress($event, video.id)"
                    @created="videoCreated"
                    @close="closeVideo(video.id)"
                    @sleep="sleepVideoDialog(video.id)"
                    @wake="wakeVideoDialog(video.id)"
                    :video="video"
                />
            </template>
        </v-dialog>
    </div>
</template>
<script>
import editVideoDialog from "../../dialogs/edit/Video";
export default {
    components: {
        editVideoDialog
    },
    computed: {
        isShowing() {
            return video_id =>
                this.videos[this.videos.findIndex(video => video.id === video_id)]
                    .isShowing;
        }
    },
    data() {
        return {
            videos: null,
            search: "",
            headers: [
                {
                    text: this.$t("Cover"),
                    align: "start",
                    sortable: false,
                    value: "cover"
                },
                { text: this.$t("Title"), value: "title" },
                { text: this.$t("Artists"), value: "artists" },
                { text: this.$t("Plays"), value: "nb_plays" },
                        {
                    text: this.$t("Downloads"),
                    value: "nb_downloads",
                    align: "center"
                },
                { text: this.$t("Likes"), value: "nb_likes" },
                { text: this.$t("Created At"), value: "created_at" },
                {
                    text: this.$t("Operations"),
                    value: "operations",
                    align: "center"
                }
            ],
            editDialog: false,
            editingVideo: null
        };
    },
    created() {
        this.fetchVieos();
    },
    methods: {
        fetchVieos() {
            axios.get("/api/artist/videos").then(res => {
                this.videos = res.data;
            });
        },
        wakeVideoDialog(video_id) {
            this.editDialog = true;
            let index = this.videos.findIndex(video => video.id === video_id);
            this.$set(this.videos[index], "isShowing", true);
        },
        sleepVideoDialog(video_id) {
            this.editDialog = false;
            let index = this.videos.findIndex(video => video.id === video_id);
            this.$set(this.videos[index], "isShowing", false);
            this.$forceUpdate();
        },
        closeVideo(video_id) {
            let index = this.videos.findIndex(video => video.id === video_id);
            if (this.videos[index].isShowing) {
                this.editDialog = false;
            }
            if (this.videos[index].new) {
                this.videos.splice(index, 1);
            }
            this.$set(this.videos[index], "isActive", false);
            this.$set(this.videos[index], "isShowing", false);
            this.$forceUpdate();
        },
        hideAllvideos() {
            for (let i = 0; i < this.videos.length; i++) {
                this.$set(this.videos[i], "isShowing", false);
            }
            this.editDialog = false;
        },
        updateProgress(progress, video_id) {
            let index = this.videos.findIndex(video => video.id === video_id);
            this.$set(this.videos[index], "progress", progress);
        },
        deleteVideo(video_id) {
            this.$confirm({
                message: `${this.$t("Are you sure you wanna delete this") +
                    " " +
                    this.$t("Video") +
                    "?"}`,
                button: {
                    no: this.$t("No"),
                    yes: this.$t("Yes")
                },
                /**
                 * Callback Function
                 * @param {Boolean} confirm
                 */
                callback: confirm => {
                    if (confirm) {
                        let index = this.videos.findIndex(
                            video => video.id === video_id
                        );
                        if (this.videos[index].requestSource) {
                            this.videos[index].requestSource.cancel();
                        }
                        axios
                            .delete("/api/videos/" + video_id, {
                                video_id
                            })
                            .then(() => {
                                this.videos.splice(index, 1)
                                this.$notify({
                                    group: "foo",
                                    type: "success",
                                    title: this.$t("Deleted"),
                                    text:
                                        this.$t("Video") +
                                        " " +
                                        this.$t("Deleted") +
                                        "."
                                });
                            })
                            .catch(() => {})
                    }
                }
            });
        },
        editVideo(video) {
            if (video === "new") {
                this.videos.unshift({
                    id: Math.floor(Math.random() * (100000 - 5000) + 100000),
                    new: true,
                    artists: [this.$store.getters.getArtist],
                    cover: "/storage/defaults/images/video_cover.jpg",
                    genres: [],
                    isActive: true,
                    isShowing: true,
                    nb_plays: 0,
                    nb_likes: 0,
                    public: true,
                    source: "",
                    source_format: "file"
                });
                this.editDialog = true;
            } else {
                this.videos[
                    this.videos.findIndex(t => t.id === video.id)
                ].isActive = true;
                this.videos[
                    this.videos.findIndex(t => t.id === video.id)
                ].isShowing = true;
                this.editDialog = true;
            }
        },
        videoEdited(video_id) {
            let index = this.videos.findIndex(video => video.id === video_id);
            this.$set(this.videos[index], "progress", 0);
            this.$set(this.videos[index], "isActive", false);
            this.$notify({
                group: "foo",
                type: "success",
                title: this.$t("Saved"),
                text: this.$t("Video") + " " + this.$t("Updated") + "."
            });
        },
        videoCreated() {
            this.$notify({
                group: "foo",
                type: "success",
                title: this.$t("Created"),
                text: this.$t("Video") + " " + this.$t("Created") + "."
            });
        }
    }
};
</script>

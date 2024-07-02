<template>
    <div class="playlists-wrapper">
        <v-card outlined>
            <v-card-title>
                <v-icon color="primary" x-large
                    >$vuetify.icons.playlist-music</v-icon
                >
                <v-btn
                    class="mx-2"
                    dark
                    small
                    color="primary"
                    @click="editPlaylist('new')"
                >
                    <v-icon>$vuetify.icons.plus</v-icon> {{ $t("New") }}
                </v-btn>
                <v-text-field
                    type='date'
                    v-model="start_date"
                    @input="fetchPlaylists()"
                    placeholder="Start date">
                </v-text-field>
                <v-text-field
                    v-model="end_date"
                    @input="fetchPlaylists()"
                    type='date'
                    placeholder="End date">
                </v-text-field>
                <v-btn
                    class="mx-2"
                    dark
                    small
                    color="primary"
                    @click="exportCSV()"
                >{{ $t("Export CSV") }}
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
                :items="playlists || []"
                :items-per-page="25"
                :loading="!playlists"
                :search="search"
                class="elevation-1"
            >
                <template v-slot:item.public="{ item }">
                    {{ item.public ? "Yes" : "No" }}
                </template>
                <template v-slot:item.cover="{ item }">
                    <div class="img-container py-2">
                        <v-img
                            :src="item.cover"
                            :alt="item.title"
                            class="user-avatar playlist-cover"
                            width="50"
                            height="50"
                        ></v-img>
                    </div>
                </template>
                <template v-slot:item.title="{ item }">
                    <router-link
                        class="router-link"
                        :to="{ name: 'playlist', params: { id: item.id } }"
                        target="_blank"
                    >
                        {{ item.title }}
                        <v-icon x-small class="mb-1 ml-1"
                            >$vuetify.icons.open-in-new</v-icon
                        >
                    </router-link>
                </template>
                <template v-slot:item.operations="{ item }">
                    <v-btn
                        @click="editPlaylist(item)"
                        x-small
                        fab
                        dark
                        color="info"
                    >
                        <v-icon>$vuetify.icons.pencil</v-icon>
                    </v-btn>
                    <v-btn
                        @click="deletePlaylist(item.id)"
                        x-small
                        fab
                        dark
                        color="error"
                    >
                        <v-icon>$vuetify.icons.delete</v-icon>
                    </v-btn>
                </template>
                <template v-slot:item.user="{ item }">
                    {{ item.user ? item.user.email : "-" }}
                </template>
                <template v-slot:item.updated_at="{ item }">
                    <div>
                        {{ moment(item.updated_at).fromNow() }}
                    </div>
                </template>
            </v-data-table>
        </v-card>
        <v-dialog v-model="editDialog" max-width="950">
            <edit-playlist-dialog
                v-if="editDialog"
                @updated="playlistEdited"
                @created="playlistCreated"
                @close="editPlaylist(null)"
                :playlist="editingPlaylist"
            />
        </v-dialog>
    </div>
</template>

<script>
import editPlaylistDialog from "../../dialogs/admin/edit/Playlist";
export default {
    components: {
        editPlaylistDialog,
    },
    data() {
        return {
            start_date:null,
            end_date:null,
            playlists: null,
            search: "",
            headers: [
                {
                    text: this.$t("Cover"),
                    align: "start",
                    sortable: false,
                    value: "cover",
                },
                { text: this.$t("Title"), value: "title" },
                { text: this.$t("User"), value: "user" },
                { text: this.$t("Public"), value: "public" },
                { text: this.$t("Followers"), value: "nb_followers" },
                { text: this.$t("Last Update"), value: "updated_at" },
                {
                    text: this.$t("Operations"),
                    value: "operations",
                    align: "center",
                },
            ],
            editDialog: false,
            editingPlaylist: null,
        };
    },
    created() {
        this.fetchPlaylists();
    },
    methods: {
        getDateFilter(){
            var query_string = "?";
            if(this.start_date){
                query_string += '&start_date='+this.start_date;
            }
            if(this.end_date){
                query_string += '&end_date='+this.end_date;
            }
            if(this.search){
                query_string += '&search='+this.search;
            }
            return query_string;
        },
        fetchPlaylists() {
            var query_string = this.getDateFilter();
            return axios.get("/api/admin/playlists"+query_string).then((res) => {
                this.playlists = res.data;
            });
        },

        deletePlaylist(playlist_id) {
            this.$confirm({
                message: `${
                    this.$t("Are you sure you wanna delete this") +
                    " " +
                    this.$t("Playlist") +
                    "?"
                }`,
                button: {
                    no: this.$t("No"),
                    yes: this.$t("Yes"),
                },
                /**
                 * Callback Function
                 * @param {Boolean} confirm
                 */
                callback: (confirm) => {
                    if (confirm) {
                        axios
                            .delete("/api/admin/playlists/" + playlist_id)
                            .then(() => {
                                this.$notify({
                                    group: "foo",
                                    type: "success",
                                    title: this.$t("Deleted"),
                                    text:
                                        this.$t("Playlist") +
                                        " " +
                                        this.$t("Deleted") +
                                        ".",
                                });
                                this.fetchPlaylists();
                            })
                            .catch();
                    }
                },
            });
        },
        editPlaylist(playlist) {
            if (!playlist) {
                this.editDialog = false;
                this.editingPlaylist = null;
            } else if (playlist === "new") {
                this.editingPlaylist = {
                    new: true,
                    public: true,
                };
                this.editDialog = true;
            } else {
                axios.get("/api/playlist/" + playlist.id).then((res) => {
                    this.editingPlaylist = res.data;
                    this.editDialog = true;
                });
            }
        },
        playlistEdited() {
            this.editDialog = false;
            this.$notify({
                group: "foo",
                type: "success",
                title: this.$t("Saved"),
                text: this.$t("Playlist") + " " + this.$t("Updated") + ".",
            });
            this.fetchPlaylists();
        },
        playlistCreated() {
            this.editDialog = false;
            this.$notify({
                group: "foo",
                type: "success",
                title: this.$t("Created"),
                text: this.$t("Playlist") + " " + this.$t("Created") + ".",
            });
            this.fetchPlaylists().then(() => {
                this.editingPlaylist = this.playlists[0];
                this.editDialog = true;
            });
        },
        exportCSV() {

            var query_string = this.getDateFilter();
            var url = '/api/admin/playlist/export'+query_string;

            axios({
                url: url,
                method: 'GET',
            }).then((response) => {
                var fileURL = URL.createObjectURL(new Blob([response.data], {
                    type: 'text/csv'
                }));
                var fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'Playlist.csv');
                document.body.appendChild(fileLink);
                fileLink.click();

                this.$notify({
                    group: "foo",
                    type: "success",
                    title: this.$t("Export"),
                    text: this.$t("Playlist data") + " " + this.$t("Exported") + "."
                });
            }).catch(() => {});
        },
    },
};
</script>

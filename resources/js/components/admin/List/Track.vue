<template>
    <div class="tracks-wrapper">
        <v-card outlined>
            <v-card-title>
                <v-icon color="primary" x-large
                    >$vuetify.icons.folder-music</v-icon
                >
                <v-btn
                    v-if="!hideCreate"
                    class="mx-2"
                    dark
                    small
                    color="primary"
                >
                    <v-icon>$vuetify.icons.plus</v-icon> {{ $t("New") }}
                </v-btn>
                <v-btn
                    class="mx-2"
                    
                    :disabled="!selected.length"
                    color="primary"
                    @click="playlistDialog = true"
                >
                    <v-icon>$vuetify.icons.plus</v-icon>
                    {{ $t("Playlist") }}
                </v-btn>
                <v-btn
                    class="mx-2"
                    
                    :disabled="!selected.length"
                    color="error"
                    @click="deleteTracks(selected.map(s => s.id))"
                >
                    <v-icon>$vuetify.icons.delete</v-icon>
                    {{ $t("Delete") }}
                </v-btn>
                <v-spacer></v-spacer>
                <v-spacer></v-spacer>
                <div class="admin-search-bar">
                    <v-text-field
                        v-model="search"
                        append-icon="$vuetify.icons.magnify"
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
                :items="tracks || []"
                :items-per-page="1000"
                :search="search"
                :loading="!tracks"
                class="elevation-1"
                v-model="selected"
                show-select
            >
                <template v-slot:item.title="{ item }">
                    <router-link
                        class="router-link"
                        :to="{ name: 'song', params: { id: item.id } }"
                        target="_blank"
                    >
                        {{ item.title }}<v-icon x-small class="mb-1 ml-1">$vuetify.icons.open-in-new</v-icon>
                    </router-link>
                </template>
                <template v-slot:item.cover="{ item }">
                    <div class="img-container py-2">
                        <v-img
                            :src="item.cover"
                            :alt="item.name"
                            class="track-cover user-avatar"
                            width="50"
                            height="50"
                        ></v-img>
                    </div>
                </template>
                <template v-slot:item.artist="{ item }">
                    <template v-if="item.artist">
                        <div class="artist-name">
                            {{ item.artist.displayname }}
                        </div>
                    </template>
                    <template v-else> - </template>
                </template>
                <template v-slot:item.album="{ item }">
                    <template v-if="item.album">
                        <div class="album-name">
                            {{ item.album.title }}
                        </div>
                    </template>
                    <template v-else> - </template>
                </template>
                <template v-slot:item.genres="{ item }">
                    <template v-if="item.genres && item.genres.length">
                        <v-chip-group column>
                            <v-chip
                                v-for="genre in item.genres"
                                :key="genre.id"
                                small
                            >
                                {{ genre.name }}
                            </v-chip>
                        </v-chip-group>
                    </template>
                    <template v-else> - </template>
                </template>
                <!-- <template v-slot:item.file_size="{ item }">
                    {{ bytesToMB(item.file_size) + $t("MB") }}
                </template> -->
                <template v-slot:item.duration="{ item }">
                    {{ mmss(item.duration) }}
                </template>
                <template v-slot:item.operations="{ item }">
                    <!-- <v-btn
            class="mx-2"
            @click="$inertia.visit(route('tracks.edit', item.id))"
            x-small
            fab
            dark
            color="info"
          >
            <v-icon>$vuetify.icons.pencil</v-icon>
          </v-btn> -->
                    <v-btn
                        class="mx-2"
                        @click="deleteTracks([item.id])"
                        x-small
                        fab
                        dark
                        color="error"
                    >
                        <v-icon>$vuetify.icons.delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>
        <v-dialog v-model="playlistDialog" max-width="500" persistent>
            <select-playlist
                v-if="playlistDialog"
                @cancel="playlistDialog = false, selected = []"
                :tracks="selected"
            ></select-playlist>
        </v-dialog>
    </div>
</template>
<script>
import SelectPlaylist from "../../dialogs/admin/Playlist/Select.vue";
export default {
    props: ["tracks", "hideCreate"],
    components: { SelectPlaylist },
    data() {
        return {
            search: "",
            selected: [],
            playlistDialog: false,
            headers: [
                {
                    text: this.$t("Cover"),
                    align: "start",
                    sortable: false,
                    value: "cover"
                },
                { text: this.$t("Title"), value: "title" },
                { text: this.$t("Artist"), value: "artist" },
                { text: this.$t("Album"), value: "album" },
                { text: this.$t("Genres"), value: "genres" },
                { text: this.$t("Duration"), value: "duration" },
                {
                    text: this.$t("Operations"),
                    value: "operations",
                    align: "center"
                }
            ]
        };
    },
    methods: {
        deleteTracks(ids) {
            this.$confirm({
                message: `${this.$t("Are you sure you wanna delete this") +
                    " " +
                    this.$t("Track") +
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
                        this.$emit("deleteTracks", ids);
                    }
                }
            });
        }
    }
};
</script>

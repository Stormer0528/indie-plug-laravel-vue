<template>
    <v-card class="p-3">
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-spacer></v-spacer>
            <v-btn @click="validateAndCreatePlaylist" class="success" :disabled="loading" :loading="loading">{{ $t('Save') }}</v-btn>
            <v-btn text small @click="$emit('cancel')" :disabled="loading">{{ $t("Cancel") }}</v-btn>
        </v-card-actions>
        <v-container>
            <v-row>
                <v-col cols="auto">
                    <upload-image
                        @imageReady="imageUploaded($event)"
                        :source="playlist.cover || defaultCoverPath"
                    />
                </v-col>
                <v-col>
                    <v-row>
                        <v-col cols="12">
                            <v-text-field
                                v-model="playlist.title"
                                :label="$t('Name')"
                            >
                            </v-text-field>
                        </v-col>
                        <v-col cols="12">
                            <v-switch
                                v-model="playlist.isPublic"
                                :label="$t('Public')"
                            ></v-switch>
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>
        </v-container>
    </v-card>
</template>

<script>
export default {
    data() {
        return {
            playlist: {
                isPublic: true
            },
            defaultCoverPath: "/storage/defaults/images/playlist_cover.png",
            loading: false
        };
    },
    methods: {
        validateAndCreatePlaylist() {
            if (this.playlist.title) {
                this.loading = true;
                var formData = new FormData();
                formData.append("title", this.playlist.title);
                formData.append("created_by", "admin");
                if (this.playlist.cover && this.playlist.cover.data) {
                    // if cover was picked, the value is stored as an object from the CropImage component
                    formData.append(
                        "cover",
                        this.playlist.cover.data,
                        this.playlist.cover.title
                    );
                } else {
                    formData.append("cover", this.defaultCoverPath);
                }
                formData.append("public", this.playlist.isPublic ? 1 : 0);
                axios
                    .post("/api/admin/playlists", formData, {
                        headers: {
                            "Content-Type": "multipart/form-data"
                        }
                    })
                    .then(() => {
                        this.$notify({
                            group: "foo",
                            type: "success",
                            title: this.$t("Created"),
                            text: this.$t("Playlist created successfully.")
                        });
                        this.$emit("created");
                    })
                    .finally(() => (this.loading = false));
            } else {
                this.$notify({
                    group: "foo",
                    type: "error",
                    title: this.$t("Oops!"),
                    text: this.$t("Please give your playlist a title")
                });
            }
        },
        imageUploaded(e) {
            this.playlist.cover = e;
        }
    }
};
</script>

<style></style>

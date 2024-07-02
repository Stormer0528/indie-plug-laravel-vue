<template>
    <v-card class="page" v-if="!isLoading">
        <v-card-title>
            <div class="page__title">
                <v-icon color="primary" x-large
                    >$vuetify.icons.motion-play</v-icon
                >
                {{ $t("Player") }}
            </div>
            <div class="page__options">
                <v-btn
                    small
                    class="success"
                    @click="save"
                    :disabled="isLoadingForButton"
                    :loading="isLoadingForButton"
                    >{{ $t("Save") }}
                    <template v-slot:loader>
                        <span class="custom-loader">
                            <v-icon light>$vuetify.icons.cached</v-icon>
                        </span>
                    </template>
                </v-btn>
            </div>
        </v-card-title>
        <v-container>
            <v-row>
                <v-col cols="12">
                    <div class="title">{{ $t("Interface") }}</div>
                    <v-divider></v-divider>
                </v-col>
                <v-col>
                    <v-switch
                        :label="$t('Hide Video')"
                        v-model="settings.disableVideo"
                    ></v-switch>
                </v-col>
                <v-col>
                    <v-switch
                        :label="$t('Hide Download Button')"
                        v-model="settings.hideDownloadButton"
                    ></v-switch>
                </v-col>
                <v-col>
                    <v-slider
                        v-model="settings.playerVolume"
                        :label="$t('Default Volume')"
                        thumb-color="primary"
                        hide-details=""
                        :thumb-label="true"
                    ></v-slider>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <div class="title">{{ $t("Engine") }}</div>
                    <v-divider></v-divider>
                </v-col>
                <v-col cols="12">
                    <v-switch
                        :label="$t('FFMPEG')"
                        @change="ffmpeg_changed"
                        :messages="
                            $t(
                                'FFMPEG is a tool to parse and convert audio files.'
                            )
                        "
                        v-model="settings.enable_ffmpeg"
                    ></v-switch>
                    <template v-if="settings.enable_ffmpeg">
                        <v-col sm="6" cols="12">
                            <v-text-field
                                :label="$t('FFMPEG path')"
                                v-model="settings.ffmpeg_path"
                                outlined
                                hide-details=""
                            />
                        </v-col>
                        <v-col sm="6" cols="12">
                            <v-text-field
                                :label="$t('FFPROBE path')"
                                v-model="settings.ffprobe_path"
                                outlined
                                hide-details=""
                            />
                        </v-col>
                    </template>
                    <v-row>
                        <v-col cols="12"></v-col>
                    </v-row>
                </v-col>
                <v-col cols="12">
                    <v-switch
                        :label="
                            $t('HLS streaming') +
                            (!settings.enable_ffmpeg
                                ? ' (' +
                                  $t('FFMPEG must be enabled first!') +
                                  ')'
                                : '')
                        "
                        :messages="
                            $t(
                                'HLS streaming helps improving the performance of your app by chunking the audio files into smaller files. You can also optionally encrypt these chunks.'
                            )
                        "
                        v-model="settings.enable_hls"
                        :disabled="!settings.enable_ffmpeg"
                    ></v-switch>

                    <template v-if="settings.enable_hls">
                        <v-col cols="12">
                            <v-text-field
                                :label="$t('Chunks duration (s)')"
                                v-model="settings.hls_chunk_duration"
                                :messages="
                                    $t(
                                        'The duration of a sigle chunk in seconds. Recommended value between 15 and 20.'
                                    )
                                "
                                type="number"
                                outlined
                            />
                        </v-col>
                        <v-col cols="12">
                            <v-switch
                                :label="$t('Save audio original file also')"
                                :messages="
                                    $t(
                                        'HLS is not supported by all browser versions. The audio file will play in case the browser does not support, we reccomend enabling this option.'
                                    )
                                "
                                v-model="settings.hls_save_original_file"
                                :disabled="!settings.enable_ffmpeg"
                            ></v-switch>
                        </v-col>
                        <v-col cols="12">
                            <v-switch
                                :label="$t('HLS encryption')"
                                :messages="
                                    $t(
                                        'Encrypt your HLS streams and protect your content.'
                                    )
                                "
                                v-model="settings.enable_hls_encryption"
                            ></v-switch>
                        </v-col>
                    </template>
                </v-col>
                <v-col cols="12">
                    <v-divider></v-divider>
                </v-col>
                <v-col cols="12">
                        <v-alert
                    border="left"
                    colored-border
                    type="warning"
                    elevation="2"
                    >
                   {{ $t('Youtube-dl is a tool to download and convert Youtube videos into mp3 audio files and save them into your server storage with the help of FFMPEG. Please be aware that such operations might be illegal in your country and you are the responsible for any violations.') }}
                    </v-alert>
                    <v-switch
                        :label="
                            $t('Youtube-dl') +
                            (!settings.enable_ffmpeg
                                ? ' (' +
                                  $t('FFMPEG must be enabled first!') +
                                  ')'
                                : '')
                        "
                        :messages="$t('')"
                        v-model="settings.enable_youtubedl"
                        :disabled="!settings.enable_ffmpeg"
                    ></v-switch>
                    <template v-if="settings.enable_youtubedl">
                        <v-row>
                            <v-col sm="6" cols="12">
                                <v-text-field
                                    :label="$t('Youtube-dl path')"
                                    v-model="settings.youtubedl_path"
                                    outlined
                                    hide-details=""
                                />
                            </v-col>
                        </v-row>
                    </template>
                </v-col>
            </v-row>
            <v-row>
                <v-col>
                    <v-divider></v-divider>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <v-switch
                        :label="$t('Auto Play')"
                        :messages="
                            $t(
                                'Shuffle songs automatically based on the settings you provide.'
                            )
                        "
                        v-model="settings.autoPlay"
                    ></v-switch>
                    <v-container v-if="settings.autoPlay">
                        <v-row>
                            <div class="pt-5">
                                {{
                                    $t(
                                        "Select the order in which the songs should be shuffled to the user based on the song he selected."
                                    )
                                }}
                            </div>
                            <v-col cols="12">
                                <draggable v-model="shuffleOptions">
                                    <div
                                        class="shuffle-option"
                                        v-for="option in shuffleOptions"
                                        :key="option.id"
                                    >
                                        <div class="drag-icon pr-2">
                                            <v-icon>$vuetify.icons.menu</v-icon>
                                        </div>
                                        <div class="option-text">
                                            {{ option.text }}
                                        </div>
                                    </div>
                                </draggable>
                            </v-col>
                            <!-- <v-col cols="12">
                                <v-switch
                                    disabled
                                    :label="$t('Crossfade') + '(Coming soon)'"
                                    :messages="
                                        $t(
                                            'Makes a smooth transition between two songs.'
                                        )
                                    "
                                    v-model="settings.crossfade"
                                ></v-switch>
                            </v-col> -->
                        </v-row>
                    </v-container>
                </v-col>
            </v-row>
            <!-- <v-row>
                <v-col cols="12">
                    <v-switch
                        :label="$t('Default Playlist')"
                        :messages="
                            $t(
                                'Playlist to load when the user visits the home page.'
                            )
                        "
                        v-model="settings.defaultPlaylist"
                    ></v-switch>
                    <v-container v-if="settings.defaultPlaylist">
                        <v-row>
                            <v-col>
                                <List
                                    :contentLoading="contentLoading"
                                    :content="editedSection.content"
                                    :isOnSectionEdit="true"
                                    @removeContentItem="removeContentItem($event)"
                                    @attachAsset="attachAsset($event)"
                                />
                            </v-col>
                        </v-row>
                    </v-container>
                </v-col>
            </v-row> -->
        </v-container>
        <!-- <v-dialog v-model="attachAssetDialogBoolean" max-width="500">
            <attach-asset-dialog
                v-if="assetIndex !== null"
                class="p-3"
                @asset="updateContent($event)"
            />
        </v-dialog> -->
    </v-card>

    <page-loading v-else />
</template>
<script>
import draggable from "vuedraggable";
// import AttachAssetDialog from "../../../dialogs/admin/AttachAssetDialog.vue";
export default {
    components: {
        draggable,
        // AttachAssetDialog
    },
    props: ["settings", "isLoading"],
    data() {
        return {
            isLoadingForButton: false,
            // shuffleOptions: [
            //     {
            //         id: 1,
            //         value: "album",
            //         text: "Same Album"
            //     },
            //     {
            //         id: 1,
            //         value: "artists",
            //         text: "Same Artists"
            //     },
            //     {
            //         id: 1,
            //         value: "genres",
            //         text: "Same Genres"
            //     }
            // ]
            shuffleOptions: this.$store.getters.getSettings.shuffleOrder,
        };
    },
    methods: {
        save() {
            if (
                this.settings.provider_spotify &&
                (!this.settings.spotifyClientID ||
                    !this.settings.spotifyClientSecret)
            ) {
                this.$notify({
                    group: "foo",
                    type: "error",
                    title: this.$t("Oops!"),
                    text: this.$t("You need to add your Spotify keys."),
                });
                return;
            }
            this.isLoadingForButton = true;
            const formData = new FormData();

            formData.append("autoPlay", this.settings.autoPlay ? 1 : 0);
            formData.append("crossfade", this.settings.crossfade ? 1 : 0);
            formData.append("disableVideo", this.settings.disableVideo ? 1 : 0);
            formData.append(
                "hideDownloadButton",
                this.settings.hideDownloadButton ? 1 : 0
            );
            formData.append(
                "downloadButton",
                this.settings.downloadButton ? 1 : 0
            );
            formData.append("playerVolume", this.settings.playerVolume);

            formData.append(
                "shuffleOrder",
                JSON.stringify(this.shuffleOptions)
            );

            if (this.settings.enable_ffmpeg) {
                if (!this.settings.ffprobe_path || !this.settings.ffmpeg_path) {
                    this.isLoadingForButton = false;
                    return this.$notify({
                        group: "foo",
                        type: "error",
                        title: this.$t("Oops!"),
                        text: this.$t(
                            "Please enter the path of FFMPEG and FFPROBE inside your server."
                        ),
                    });
                } else {
                    formData.append(
                        "enable_ffmpeg",
                        this.settings.enable_ffmpeg ? 1 : 0
                    );
                    formData.append("ffprobe_path", this.settings.ffprobe_path);
                    formData.append("ffmpeg_path", this.settings.ffmpeg_path);
                }
            } else {
                formData.append("enable_ffmpeg", 0);
            }

            if (this.settings.enable_youtubedl) {
                if (!this.settings.youtubedl_path) {
                    this.isLoadingForButton = false;
                    return this.$notify({
                        group: "foo",
                        type: "error",
                        title: this.$t("Oops!"),
                        text: this.$t(
                            "Please enter the path of Youtube-dl inside your server."
                        ),
                    });
                } else {
                    formData.append(
                        "enable_youtubedl",
                        this.settings.enable_youtubedl ? 1 : 0
                    );
                    formData.append(
                        "youtubedl_path",
                        this.settings.youtubedl_path
                    );
                }
            } else {
                formData.append("enable_youtubedl", 0);
            }

            if (this.settings.enable_hls) {
                if (
                    !this.settings.hls_chunk_duration ||
                    this.settings.hls_chunk_duration > 100 ||
                    this.settings.hls_chunk_duration < 5
                ) {
                     this.isLoadingForButton = false;
                    return this.$notify({
                        group: "foo",
                        type: "error",
                        title: this.$t("Oops!"),
                        text: this.$t(
                            "Please enter a chunk duration between 5 and 100 for HLS streaming."
                        ),
                    });
                } else {
                    formData.append("enable_hls", this.settings.enable_hls ? 1 : 0);
                    formData.append(
                        "hls_save_original_file",
                        this.settings.hls_save_original_file ? 1 : 0
                    );
                    formData.append(
                        "enable_hls_encryption",
                        this.settings.enable_hls_encryption ? 1 : 0
                    );
                    formData.append(
                        "hls_chunk_duration",
                        this.settings.hls_chunk_duration
                    );
                }
            } else {
                formData.append("enable_hls", 0);
                formData.append("hls_save_original_file", 0);
                formData.append("enable_hls_encryption", 0);
            }

            axios
                .post("/api/admin/save-player-settings", formData)
                .then(() => {
                    this.$notify({
                        group: "foo",
                        type: "success",
                        title: this.$t("Updated"),
                        text:
                            this.$t("Settings") +
                            " " +
                            this.$t("updated successfully."),
                    });
                })
                .catch(() => {
                    // this.$notify({
                    //     group: "foo",
                    //     type: "error",
                    //     title: this.$t("Error"),
                    //     text: Object.values(e.response.data.errors).join(
                    //         "<br />"
                    //     )
                    // });
                })
                .finally(() => {
                    this.isLoadingForButton = false;
                });
        },
        updateContent(event) {
            this.$set(this.defaultPlaylist.content, this.assetIndex, event);
            this.attachAssetDialogBoolean = false;
            this.assetIndex = null;
        },
        ffmpeg_changed() {
            this.settings.enable_youtubedl = false;
            this.settings.enable_hls = false;
        }
    },
};
</script>

<style lang="scss" scoped>
.shuffle-option {
    display: flex;
    padding: 0.5em 1em;
    font-size: 1.2em;
}
</style>

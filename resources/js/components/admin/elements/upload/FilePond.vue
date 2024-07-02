<template>
    <div class="relative">
        <file-pond
            ref="pond"
            name="track"
            v-model="tracks"
            v-bind:allow-multiple="true"
            accepted-file-types="audio/wav, audio/mpeg"
            :server="server"
            :maxParallelUploads="1"
            :labelIdle="uploadBTN"
            :labelFileProcessingError="errorMessage"
        >
            <v-btn>{{ $t("Upload") }}</v-btn>
        </file-pond>
        <v-btn class="info reset-btn" small @click="reset">{{
            $t("Reset")
        }}</v-btn>
        <!-- <v-checkbox class="ml-2" small v-model="server.headers['Upload-Album']" :label="$t('Album Upload')"></v-checkbox>
         <v-checkbox class="ml-2" small v-model="server.headers['Notify-Users']" :label="$t('Notify Users')"></v-checkbox> -->
    </div>
</template>

<script>
// Import Vue FilePond
import vueFilePond from "vue-filepond";

// Import FilePond styles
// import "filepond/dist/filepond.min.css";

// Import FilePond plugins

// Import image preview and file type validation plugins
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";

// Create component
const FilePond = vueFilePond(FilePondPluginFileValidateType);

export default {
    data: function () {
        return {
            tracks: [],
            errorMessage: "",
            uploadBTN: `<button type="button" class="primary low-z v-btn v-btn--is-elevated v-btn--has-bg theme--dark v-size--large" style="min-width: 250px;"><span class="v-btn__content"><span aria-hidden="true" class="v-icon notranslate v-icon--left theme--dark"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" class="v-icon__svg"><path d="M14,13V17H10V13H7L12,8L17,13M19.35,10.03C18.67,6.59 15.64,4 12,4C9.11,4 6.6,5.64 5.35,8.03C2.34,8.36 0,10.9 0,14A6,6 0 0,0 6,20H19A5,5 0 0,0 24,15C24,12.36 21.95,10.22 19.35,10.03Z"></path></svg></span> Upload Files</span></button>`,
            server: {
                url: "/api/admin/upload",
                process: {
                    onload: (res) => {
                        this.$emit("pushTrack", res);
                    },
                    onerror: (e) => {
                        var obj = JSON.parse(e);
                        if (obj.type == "demo") {
                            this.errorMessage = this.$t(
                                "You can not upload in demo mode"
                            );
                        } else {
                            this.errorMessage = obj.message;
                        }
                    },
                },
                headers: {
                    Authorization: "Bearer " + this.$store.getters.getToken,
                    "Notify-Users": false,
                    "Upload-Album": false,
                },
            },
        };
    },
    components: {
        FilePond,
    },
    methods: {
        reset() {
            this.$refs.pond.removeFiles();
        },
        handleError(e) {
            // console.log(e)
        },
    },
};
</script>

<style lang="scss">
.filepond--wrapper {
    height: 26em;
    overflow: hidden;
}
.filepond-root {
    height: 25em !important;
    overflow: hidden;
}
.filepond--list-scroller {
    height: 20em !important;
    overflow-y: scroll !important;
}
.reset-btn {
    position: absolute;
    top: 1.9em;
    right: 1em;
}
.low-z {
    z-index: -1;
}
</style>

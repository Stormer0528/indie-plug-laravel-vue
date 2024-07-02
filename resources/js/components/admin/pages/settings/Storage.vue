<template>
    <v-card class="page" v-if="!isLoading">
        <v-card-title>
            <div class="page__title">
                <v-icon color="primary" x-large>$vuetify.icons.database</v-icon>
                {{ $t("Storage") }}
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
        <v-container class="pl-5">
            <v-row>
                <v-col cols="12">
                    <v-select
                        :items="storageMethods"
                        return-object
                        item-text="name"
                        :label="$t('Storage Disk')"
                        hide-details
                        v-model="settings.storageDisk"
                        outlined
                    ></v-select>
                </v-col>
            </v-row>
            <template v-if="settings.storageDisk.disk === 's3'">
                <v-row>
                    <v-col cols="12">
                        <div class="title">S3 {{ $t("Credentials") }}</div>
                        <v-divider></v-divider>
                    </v-col>
                    <v-col cols="12">
                        <v-text-field
                            :label="$t('S3 Compatbile Endpoint')"
                            outlined
                            v-if="
                                settings.storageDisk.name ==
                                'Other S3 Compatible'
                            "
                            hide-details
                            v-model="settings.storageDisk.endpoint"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Access Key ID')"
                            outlined
                            hide-details
                            v-model="settings.S3Key"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            type="password"
                            hide-details
                            :label="$t('Secret Access key')"
                            outlined
                            v-model="settings.S3Secret"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('S3 Region')"
                            hide-details
                            outlined
                            v-model="settings.S3Region"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('S3 Bucket')"
                            hide-details
                            outlined
                            v-model="settings.S3Bucket"
                        />
                    </v-col>
                </v-row>
            </template>
            <template v-if="settings.storageDisk.disk === 'b2'">
                <v-row>
                    <v-col cols="12">
                        <div class="title">
                            Blackblaze {{ $t("Credentials") }}
                        </div>
                        <v-divider></v-divider>
                    </v-col>
                    <v-col cols="12">
                        <v-text-field
                            :label="$t('Endpoint')"
                            outlined
                            hide-details
                            placeholder="s3.us-west-000.backblazeb2.com"
                            v-model="settings.blackblazeEndpoint"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Key ID')"
                            outlined
                            hide-details
                            v-model="settings.blackblazeKeyID"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Application Key')"
                            outlined
                            hide-details
                            v-model="settings.blackblazeApplicationKey"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Bucket ID')"
                            outlined
                            hide-details
                            v-model="settings.blackblazeBucketID"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Bucket Name')"
                            outlined
                            hide-details
                            v-model="settings.blackblazeBucketName"
                        />
                    </v-col>
                </v-row>
            </template>
            <template v-if="settings.storageDisk.disk === 'ftp'">
                <v-row>
                    <v-col cols="12">
                        <div class="title">FTP {{ $t("Credentials") }}</div>
                        <v-divider></v-divider>
                    </v-col>
                    <v-col cols="12">
                        <v-text-field
                            :label="$t('Host')"
                            outlined
                            hide-details
                            v-model="settings.FTP_HOST"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Username')"
                            outlined
                            hide-details
                            v-model="settings.FTP_USERNAME"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Password')"
                            outlined
                            type="password"
                            hide-details
                            v-model="settings.FTP_PASSWORD"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Port')"
                            outlined
                            hide-details
                            v-model="settings.FTP_PORT"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Root')"
                            outlined
                            hide-details
                            v-model="settings.FTP_ROOT"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            :label="$t('Timeout')"
                            outlined
                            type="number"
                            hide-details
                            v-model="settings.FTP_TIMEOUT"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-switch
                            :label="$t('Passive')"
                            outlined
                            hide-details
                            v-model="settings.FTP_PASSIVE"
                        />
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-switch
                            :label="$t('SSL')"
                            outlined
                            hide-details
                            v-model="settings.FTP_SSL"
                        />
                    </v-col>
                </v-row>
            </template>
            <v-row>
                <v-col cols="12">
                    <div class="title">{{ $t("Storage Limits") }}</div>
                    <v-divider></v-divider>
                </v-col>
                <v-col cols="12" md="6">
                    <v-text-field
                        :label="$t('Max File Size') + '(audio)'"
                        class="pb-4"
                        type="number"
                        :messages="
                            $t('Max file size allowed') + '(' + $t('MB') + ')'
                        "
                        outlined
                        hide-details=""
                        v-model="settings.maxFileSize"
                        required
                        :suffix="$t('MB')"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                    <v-text-field
                        :label="$t('Max Image Size')"
                        type="number"
                        hide-details=""
                        :messages="
                            $t('Max image size allowed') + '(' + $t('MB') + ')'
                        "
                        outlined
                        v-model="settings.maxImageSize"
                        :suffix="$t('MB')"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                    <v-text-field
                        :label="$t('Default User Disk Space')"
                        class="pb-4"
                        type="number"
                        :messages="$t('Available disk space for users')"
                        outlined
                        v-model="settings.availableUserDiskSpace"
                        :suffix="$t('MB')"
                    ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                    <v-text-field
                        :label="$t('Default Artist Disk Space')"
                        class="pb-4"
                        type="number"
                        :messages="$t('Available disk space for artists')"
                        outlined
                        v-model="settings.availableArtistDiskSpace"
                        :suffix="$t('MB')"
                    ></v-text-field>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <div class="title">{{ $t("Images") }}</div>
                    <v-divider></v-divider>
                </v-col>
                <v-col cols="12">
                    <v-switch
                        :label="$t('Optimize images')"
                        v-model="settings.optimize_images"
                        :messages="
                            $t(
                                'By default, images will be stores as they are originally. If you enable this option the images will be optimized with the help of 3rd party libraries.'
                            )
                        "
                    >
                    </v-switch>
                </v-col>
                <template v-if="settings.optimize_images">
                    <v-col cols="12" sm="6">
                        <v-text-field
                            :label="$t('Default image dimensions')"
                            type="number"
                            v-model="this.settings.image_dimensions"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                        <v-text-field
                            :label="$t('Default thumbnail dimensions')"
                            type="number"
                            v-model="this.settings.thumb_dimensions"
                        ></v-text-field>
                    </v-col>
                </template>
            </v-row>
        </v-container>
    </v-card>
    <page-loading v-else />
</template>
<script>
export default {
    props: ["settings", "isLoading"],

    data() {
        return {
            isLoadingForButton: false,
            compatibleS3Storage: Boolean(
                this.settings.compatibleS3StorageEndpoint
            ),
            storageMethods: [
                {
                    disk: "public",
                    name: "Local Storage",
                },
                {
                    disk: "b2",
                    name: "Blackblaze B2",
                },
                {
                    disk: "s3",
                    name: "AWS S3",
                    endpoint: "s3.amazonaws.com",
                },
                {
                    disk: "s3",
                    name: "Wasabi S3",
                    endpoint: "s3.wasabisys.com",
                },
                {
                    disk: "s3",
                    name: "Other S3 Compatible",
                    endpoint: "",
                },
                {
                    disk: "ftp",
                    name: "FTP",
                    endpoint: "",
                },
            ],
        };
    },
    computed: {
        // storageSettings() {
        //     return (({
        //         storageDisk,
        //         S3Key,
        //         S3Secret,
        //         S3Bucket,
        //         S3Region,
        //         S3Endpoint,
        //         maxImageSize,
        //         maxFileSize,
        //         availableUserDiskSpace,
        //         availableArtistDiskSpace
        //     }) => ({
        //         storageDisk,
        //         S3Key,
        //         S3Secret,
        //         S3Bucket,
        //         S3Region,
        //         S3Endpoint,
        //         maxImageSize,
        //         maxFileSize,
        //         availableUserDiskSpace,
        //         availableArtistDiskSpace
        //     }))(this.settings || {});
        // }
    },
    methods: {
        save() {
            this.isLoadingForButton = true;
            const formData = new FormData();
            formData.append(
                "storageDisk",
                JSON.stringify(this.settings.storageDisk)
            );
            formData.append("maxImageSize", this.settings.maxImageSize);
            formData.append("maxFileSize", this.settings.maxFileSize);
            formData.append(
                "availableUserDiskSpace",
                this.settings.availableUserDiskSpace
            );
            formData.append(
                "availableArtistDiskSpace",
                this.settings.availableArtistDiskSpace
            );

            // validate size

            formData.append("image_dimensions", this.settings.image_dimensions);
            formData.append("thumb_dimensions", this.settings.thumb_dimensions);
            formData.append(
                "optimize_images",
                this.settings.optimize_images ? 1 : 0
            );

            if (this.settings.storageDisk.disk === "s3") {
                formData.append("S3Key", this.settings.S3Key);
                // if (
                //     this.settings.S3Secret !==
                //     "secretsecretsecretsecretsecret"
                // ) {
                formData.append("S3Secret", this.settings.S3Secret);
                // }
                const endpoint = this.settings.storageDisk.name === "Wasabi S3"
                    ? "s3." + this.settings.S3Region + ".wasabisys.com"
                    : this.settings.storageDisk.endpoint;

                this.settings.storageDisk.endpoint = endpoint;
                formData.append("S3Endpoint", endpoint);

                formData.append("S3Region", this.settings.S3Region);
                formData.append("S3Bucket", this.settings.S3Bucket);
            }
            if (this.settings.storageDisk.disk === "b2") {
                formData.append(
                    "blackblazeKeyID",
                    this.settings.blackblazeKeyID
                );
                // if (
                //     this.storageSettings.blackblazeApplicationKey !==
                //     "secretsecretsecretsecretsecret"
                // ) {
                formData.append(
                    "blackblazeApplicationKey",
                    this.settings.blackblazeApplicationKey
                );
                // }
                // formData.append(
                //     "S3Endpoint",
                //     this.settings.S3Endpoint
                // );
                formData.append(
                    "blackblazeBucketID",
                    this.settings.blackblazeBucketID
                );
                formData.append(
                    "blackblazeBucketName",
                    this.settings.blackblazeBucketName
                );
                formData.append(
                    "blackblazeEndpoint",
                    this.settings.blackblazeEndpoint
                );
            }
            if (this.settings.storageDisk.disk === "ftp") {
                formData.append("FTP_HOST", this.settings.FTP_HOST);
                formData.append("FTP_USERNAME", this.settings.FTP_USERNAME);
                formData.append("FTP_PASSWORD", this.settings.FTP_PASSWORD);
                formData.append("FTP_ROOT", this.settings.FTP_ROOT);
                formData.append("FTP_PASSIVE", this.settings.FTP_PASSIVE);
                formData.append("FTP_SSL", this.settings.FTP_SSL);
                formData.append("FTP_TIMEOUT", this.settings.FTP_TIMEOUT);
                formData.append("FTP_PORT", this.settings.FTP_PORT);
            }
            axios
                .post("/api/admin/save-storage-settings", formData)
                .then(() => {
                    this.$notify({
                        group: "foo",
                        type: "success",
                        title: this.$t("Updated"),
                        text:
                            this.$t("Settings") +
                            this.$t("updated successfully."),
                    });
                    location.reload();
                })
                .catch(() => {
                    this.$notify({
                        group: "foo",
                        type: "error",
                        title: this.$t("Error"),
                        text: Object.values(e.response.data.errors).join(
                            "<br />"
                        ),
                    });
                })
                .finally(() => {
                    this.isLoadingForButton = false;
                });
        },
    },
};
</script>

<template>
    <div class="podcst-radioStations-wrapper">
        <v-card outlined>
            <v-card-title>
                <v-icon color="primary" x-large
                    >$vuetify.icons.radio-tower</v-icon
                >
                <v-btn
                    class="mx-2"
                    dark
                    small
                    color="primary"
                    @click="createRadioStationDialog"
                >
                    <v-icon>$vuetify.icons.plus</v-icon> {{ $t("New") }}
                </v-btn>
                <v-text-field
                    type='date'
                    v-model="start_date"
                    @input="fetchRadioStations()"
                    placeholder="Start date">
                </v-text-field>
                <v-text-field
                    v-model="end_date"
                    @input="fetchRadioStations()"
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
                :items="radioStations"
                :items-per-page="25"
                :search="search"
                class="elevation-1"
            >
                <template v-slot:item.name="{ item }">
                    <router-link
                        class="router-link"
                        :to="{ name: 'radio-station', params: { id: item.id } }"
                        target="_blank"
                    >
                        {{ item.name
                        }}<v-icon x-small class="mb-1 ml-1"
                            >$vuetify.icons.open-in-new</v-icon
                        >
                    </router-link>
                </template>
                <template v-slot:item.cover="{ item }">
                    <div class="img-container py-2">
                        <v-img
                            :src="item.cover"
                            :alt="item.name"
                            class="radioStation-cover user-avatar"
                            width="100"
                            height="100"
                        ></v-img>
                    </div>
                </template>
                <template v-slot:item.operations="{ item }">
                    <v-btn
                        class="mx-2"
                        @click="editRadioStation(item)"
                        x-small
                        fab
                        dark
                        color="info"
                    >
                        <v-icon>$vuetify.icons.pencil</v-icon>
                    </v-btn>
                    <v-btn
                        class="mx-2"
                        @click="deleteRadioStation(item.id)"
                        x-small
                        fab
                        dark
                        color="error"
                    >
                        <v-icon>$vuetify.icons.delete</v-icon>
                    </v-btn>
                </template>
                <template v-slot:item.genre="{ item }">
                    <span v-if="item.stats && item.stats.genre">
                        {{ item.stats.genre }}
                    </span>
                    <span v-else> - </span>
                </template>
                <template v-slot:item.currentListeners="{ item }">
                    <span v-if="item.stats && item.stats.currentListeners">
                        {{ item.stats.currentListeners }}
                    </span>
                    <span v-else> - </span>
                </template>
                <template v-slot:item.peakListeners="{ item }">
                    <span v-if="item.stats && item.stats.peakListeners">
                        {{ item.stats.peakListeners }}
                    </span>
                    <span v-else> - </span>
                </template>
                <template v-slot:item.created_at="{ item }">
                    {{ moment(item.created_at).format("ll") }}
                </template>
            </v-data-table>
        </v-card>
        <v-dialog v-model="editDialog" max-width="600">
            <edit-radio-station-dialog
                v-if="editDialog"
                @updated="radioStationEdited"
                @close="editRadioStation(null)"
                :radioStation="editingRadioStation"
            />
        </v-dialog>
    </div>
</template>
<script>
import editRadioStationDialog from "../../dialogs/admin/edit/RadioStation";
export default {
    components: {
        editRadioStationDialog,
    },
    data() {
        return {
            start_date: null,
            end_date : null,
            radioStations: [],
            search: "",
            headers: [
                {
                    text: this.$t("Cover"),
                    align: "start",
                    sortable: false,
                    value: "cover",
                },
                { text: this.$t("Name"), value: "name" },
                { text: this.$t("Genre"), value: "genre", align: "center" },
                {
                    text: this.$t("Current Listeners"),
                    value: "currentListeners",
                    align: "center",
                },
                {
                    text: this.$t("Peak Listeners"),
                    value: "peakListeners",
                    align: "center",
                },
                {
                    text: this.$t("Operations"),
                    value: "operations",
                    align: "center",
                },
            ],
            editDialog: false,
            editingRadioStation: null,
        };
    },
    created() {
        this.fetchRadioStations();
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
        fetchRadioStations() {
            var query_string = this.getDateFilter();
            axios.get("/api/admin/radio-stations"+query_string).then((res) => {
                this.radioStations = res.data;
            });
        },
        deleteRadioStation(id) {
            this.$confirm({
                message: `${
                    this.$t("Are you sure you wanna delete this") +
                    " " +
                    this.$t("Radio Station") +
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
                            .delete("/api/admin/radio-stations/" + id)
                            .then(() => {
                                this.$notify({
                                    group: "foo",
                                    type: "success",
                                    title: this.$t("Deleted"),
                                    text:
                                        this.$t("RadioStation") +
                                        " " +
                                        this.$t("Deleted") +
                                        ".",
                                });
                                this.fetchRadioStations();
                            })
                            .catch();
                    }
                },
            });
        },
        editRadioStation(radioStation) {
            if (!radioStation) {
                this.editDialog = false;
                this.editingRadioStation = null;
            } else {
                this.editingRadioStation = radioStation;
                this.editDialog = true;
            }
        },
        createRadioStationDialog() {
            this.editingRadioStation = {
                new: true,
                name: "",
                serverType: "icecast",
                statsSource: "endpoint",
                interval: 5000,
            };
            this.editDialog = true;
        },
        radioStationEdited() {
            this.editDialog = false;
            this.$notify({
                group: "foo",
                type: "success",
                title: this.$t("Saved"),
                text: this.$t("Radio Station") + " " + this.$t("Updated") + ".",
            });
            this.fetchRadioStations();
        },
        exportCSV() {
            var query_string = this.getDateFilter();
            var url = '/api/admin/radiostation/export'+query_string;
            axios({
                url: url,
                method: 'GET',
            }).then((response) => {
                var fileURL = URL.createObjectURL(new Blob([response.data], {
                    type: 'text/csv'
                }));
                var fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', 'Radiostation.csv');
                document.body.appendChild(fileLink);
                fileLink.click();

                this.$notify({
                    group: "foo",
                    type: "success",
                    title: this.$t("Export"),
                    text: this.$t("Radiostation data") + " " + this.$t("Exported") + "."
                });
            }).catch(() => {});
        },
    },
};
</script>

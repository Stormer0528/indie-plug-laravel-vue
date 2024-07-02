<template>
  <div class="advertising-wrapper">
    <v-card outlined>
      <v-card-title>
        <v-icon color="primary" x-large>$vuetify.icons.google-ads</v-icon>
        <div class="advertising-wrapper__title">
          {{ $t("Campaigns") }}
        </div>
        <v-spacer></v-spacer>
        <v-spacer></v-spacer>
        <!-- <v-btn
          color="primary"
          @click="save"
          :disabled="isLoading"
          :loading="isLoading"
          >{{ $t("Save") }}</v-btn
        > -->
      </v-card-title>
      <v-container>
        <v-row>
          <v-col cols="12"> </v-col>
          <v-col cols="12">
            <div class="card-bb-header">
              <div class="header">
                <div class="title">
                             <div class="buttons">
                  <v-btn
                    @click="editCampaign('new')"
                    small
                    dark
                    color="success"
                  >
                    <v-icon>$vuetify.icons.plus</v-icon>
                    {{ $t('New Campaign') }}
                  </v-btn>
                </div>
                </div>
                <!-- <div class="buttons">
                  <v-btn
                    @click="editCampaign('new')"
                    x-small
                    fab
                    dark
                    color="success"
                  >
                    <v-icon>$vuetify.icons.plus</v-icon>
                  </v-btn>
                </div> -->
              </div>
              <v-divider class="py-2"></v-divider>
              <div class="">
                <v-container>
                  <v-data-table
                    :no-data-text="$t('No data available')"
                    :loading-text="$t('Fetching data') + '...'"
                    :headers="headers"
                    :items="campaigns || []"
                    :loading="!campaigns"
                    :items-per-page="25"
                    class="elevation-1"
                  >
                    <template v-slot:body="props">
                      <draggable
                        v-model="campaigns"
                        v-if="campaigns.length"
                        @change="$forceUpdate()"
                        tag="tbody"
                      >
                        <tr
                          v-for="(campaign, index) in campaigns"
                          :key="campaign.id"
                        >
                          <td>
                            <v-icon small class="page__grab-icon">
                              $vuetify.icons.arrow-all
                            </v-icon>
                          </td>
                          <td class="text-center">{{ index + 1 }}</td>
                          <td>
                            {{ campaign.name }}
                          </td>
                          <!-- <td>
                            {{ campaign.file_name }}
                          </td> -->
                          <td class="text-center">
                            {{ campaign.playAfterXSongs }}
                          </td>
                          <td>
                            <v-btn
                              x-small
                              dense
                              depressed
                              v-if="campaign.status === 'scheduled'"
                              class="warning"
                              >{{ $t("Scheduled") }}</v-btn
                            >
                            <v-btn
                              x-small
                              dense
                              depressed
                              v-if="campaign.status === 'active'"
                              class="success"
                              >{{ $t("Active") }}</v-btn
                            >
                            <v-btn
                              x-small
                              dense
                              depressed
                              v-else-if="campaign.status === 'expired'"
                              class="error"
                              >{{ $t("Expired") }}</v-btn
                            >
                          </td>
                            <td class="text-center">
                                {{ campaign.total_plays }}
                            </td>

                            <td>
                            {{
                              moment(campaign.start_datetime).format(
                                "MM/DD/YYYY, HH:mm"
                              )
                            }}
                          </td>
                          <td>
                            {{
                              moment(campaign.expiry_datetime).format(
                                "MM/DD/YYYY, HH:mm"
                              )
                            }}
                          </td>
                          <td>
                            <v-btn
                              class="mx-2"
                              @click="editCampaign(campaign)"
                              x-small
                              fab
                              dark
                              color="info"
                            >
                              <v-icon dark>$vuetify.icons.pencil</v-icon>
                            </v-btn>
                            <v-btn
                              class="mx-2"
                              @click="deleteCampaign(campaign.id)"
                              x-small
                              fab
                              dark
                              color="error"
                            >
                              <v-icon>$vuetify.icons.delete</v-icon>
                            </v-btn>
                          </td>
                        </tr>
                      </draggable>
                    </template>
                  </v-data-table>
                </v-container>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-container>
      <v-dialog v-model="campaignDialog" max-width="800">
        <edit-campaign
          :campaign="editedCampaign"
          v-if="campaignDialog"
          :admin="$store.getters.getUser"
          @updated="campaignEdited"
          @close="editCampaign(null)"
        ></edit-campaign>
      </v-dialog>
    </v-card>
  </div>
</template>
<script>
import editCampaign from "../../dialogs/admin/edit/Campaign.vue";
import draggable from "vuedraggable";
export default {
  created() {
    this.fetchCampaigns();
  },
  components: {
    editCampaign,
    draggable,
  },
  data() {
    return {
      isLoading: false,
      campaigns: [],
      campaignDialog: false,
      editedCampaign: null,
      headers: [
        {
        text: ""
        },
        {
          text: this.$t("Priority"),
          value: "index",
        },
        {
          text: this.$t("Name"),
          value: "name",
        },
        // {
        //   text: this.$t("File"),
        //   value: "file_name",
        // },
        {
          text: this.$t("Plays after each X Songs"),
          value: "playAfterXSongs",
          align: "center",
        },
        {
          text: this.$t("Status"),
          value: "status",
        },
        {
          text: this.$t("Plays"),
          value: "total_plays",
            align: "center",
        },
        { text: this.$t("Start date"), value: "start_datetime" },
        { text: this.$t("Expiry date"), value: "expiry_datetime" },
        {
          text: this.$t("Operations"),
          value: "operations",
          align: "center",
        },
      ],
    };
  },
  methods: {
    fetchCampaigns() {
      axios.get("/api/admin/campaigns").then((res) => {
        this.campaigns = res.data;
      });
    },
    save() {
      this.isLoading = true;
      axios
        .post("/api/admin/save-ad-settings", {
          campaigns: this.campaigns,
        })
        .then(() => {
          this.$notify({
            group: "foo",
            type: "success",
            title: this.$t("Updated"),
            text: this.$t("Settings added successfully"),
          });
          this.fetchCampaigns();
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    campaignEdited() {
      this.campaignDialog = false;
      this.editedCampaign = null;
    },
    editCampaign(campaign) {
      if (!campaign) {
        this.campaignDialog = false;
        this.editedCampaign = null;
      } else if (campaign === "new") {
        this.editedCampaign = {
          new: true,
          playAfterXSongs: 2,
          start_datetime: new Date(),
          expiry_datetime: new Date(
            new Date().getFullYear(),
            new Date().getMonth() + 1,
            0
          ),
        };
        this.campaignDialog = true;
      } else {
        this.editedCampaign = campaign;
        this.campaignDialog = true;
      }
    },
    campaignEdited() {
      this.campaignDialog = false;
      this.$notify({
        group: "foo",
        type: "success",
        title: this.$t("Saved"),
        text: this.$t("Campaign") + " " + this.$t("Updated") + ".",
      });
      this.fetchCampaigns();
    },
    deleteCampaign(id) {
      this.isLoading = true;
      axios
        .delete("/api/admin/campaigns/" + id)
        .then(() => {
          this.$notify({
            group: "foo",
            type: "success",
            title: this.$t("Deleted"),
            text: this.$t("Campaigns deleted successfully"),
          });
          this.fetchCampaigns();
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>

<style lang="scss">
.advertising-wrapper {
  &__title {
    font-weight: bold;
    font-size: 1.2em;
    margin-left: 0.5em;
  }
}
.ad-banner {
  width: 100%;
  &__rect {
    min-width: 300px;
  }
  &__square {
    max-width: 300px;
    min-width: 140px;
  }
}
</style>

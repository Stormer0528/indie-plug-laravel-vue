<template>
  <edit-dialog
    @callToAction="saveCampaign"
    @cancel="$emit('close')"
    :loading="isLoading"
    editing="campaign"
  >
    <template v-slot:body>
      <v-container>
        <v-row>
          <v-col cols="12">
            <v-text-field
              :label="$t('Name')"
              v-model="editedCampaign.name"
              outlined
            ></v-text-field>
            <v-file-input
              accept="audio/*"
              outlined
              v-model="editedCampaign.file"
              :label="
                editedCampaign.file_name
                  ? editedCampaign.file_name
                  : $t('Attach Audio File')
              "
              :placeholder="
                editedCampaign.file_name
                  ? editedCampaign.file_name
                  : $t('Attach Audio File')
              "
            ></v-file-input>
            <v-text-field
              :label="$t('Play after X songs')"
              type="number"
              outlined
              v-model="editedCampaign.playAfterXSongs"
            ></v-text-field>
            <v-file-input
              accept="image/*"
              outlined
              v-model="editedCampaign.banner"
              :label="
                editedCampaign.banner
                  ? $t('Change Banner')
                  : $t('Attach Banner Image') + $t('(Optional)')
              "
            ></v-file-input>
            <v-datetime-picker
              :label="$t('Start Date')"
              datetime
              :textFieldProps="{
                outlined: true,
              }"
              v-model="editedCampaign.start_datetime"
            >
              <template v-slot:dateIcon>
                <v-icon>$vuetify.icons.calendar</v-icon>
              </template>
              <template v-slot:timeIcon>
                <v-icon>$vuetify.icons.clock</v-icon>
              </template>
            </v-datetime-picker>
            <v-datetime-picker
              :label="$t('Expiry Date')"
              datetime
              :textFieldProps="{
                outlined: true,
              }"
              v-model="editedCampaign.expiry_datetime"
            >
              <template v-slot:dateIcon>
                <v-icon>$vuetify.icons.calendar</v-icon>
              </template>
              <template v-slot:timeIcon>
                <v-icon>$vuetify.icons.clock</v-icon>
              </template>
            </v-datetime-picker>
          </v-col>
        </v-row>
      </v-container>
    </template>
  </edit-dialog>
</template>
<script>
export default {
  props: ["campaign", "manager"],
  created() {
    this.editedCampaign.expiry_datetime = new Date(
      this.editedCampaign.expiry_datetime
    );
    this.editedCampaign.start_datetime = new Date(
      this.editedCampaign.start_datetime
    );
  },
  data() {
    return {
      editedCampaign: this.campaign,
      isLoading: false,
      dateMenu: false
    };
  },
  watch: {
    dateMenu(val) {
      val && setTimeout(() => (this.$refs.picker.activePicker = "YEAR"));
    },
  },
  methods: {
    saveExpiryDate(date) {
      this.$refs.menu.save(date);
    },
    saveCampaign() {
      this.isLoading = true;
      var formData = new FormData();
      formData.append("file", this.editedCampaign.file);
      formData.append("playAfterXSongs", this.editedCampaign.playAfterXSongs);
      formData.append("name", this.editedCampaign.name);
      formData.append(
        "expiry_datetime",
        new Date(this.editedCampaign.expiry_datetime).toISOString()
      );
      formData.append(
        "start_datetime",
        new Date(this.editedCampaign.start_datetime).toISOString()
      );
      if (this.manager) {
        formData.append("manager_id", this.manager.id);
      } else if (this.editedCampaign.manager) {
        formData.append("manager_id", this.editedCampaign.manager.id);
      }
      if (this.editedCampaign.banner) {
        formData.append("banner", this.editedCampaign.banner);
      }
      if (this.editedCampaign.new) {
        formData.append("new", true);
        axios
          .post(
            "/api/" + (this.manager ? "manager" : "admin") + "/campaigns",
            formData,
            {
              headers: {
                "Content-Type": "multipart/form-data",
              },
            }
          )
          .then(() => {
            this.$notify({
              group: "foo",
              type: "success",
              title: this.$t("Added"),
              text: this.$t("Campaigns added successfully"),
            });
            this.$emit("updated");
          })
          .catch((e) => {
            this.$notify({
              group: "foo",
              type: "error",
              title: this.$t("Error"),
              text: Object.values(e.response.data.errors).join("<br />"),
            });
          })
          .finally(() => {
            this.isLoading = false;
          });
      } else {
        formData.append("id", this.editedCampaign.id);
        formData.append("_method", "PUT");
        axios
          .post(
            "/api/" +
              (this.manager ? "manager" : "admin") +
              "/campaigns/" +
              this.editedCampaign.id,
            formData,
            {
              headers: {
                "Content-Type": "multipart/form-data",
              },
            }
          )
          .then(() => {
            this.$notify({
              group: "foo",
              type: "success",
              title: this.$t("Updated"),
              text: this.$t("Campaigns updated successfully"),
            });
            this.$emit("updated");
          })
          .catch((e) => {
            this.$notify({
              group: "foo",
              type: "error",
              title: this.$t("Error"),
              text: Object.values(e.response.data.errors).join("<br />"),
            });
          })
          .finally(() => {
            this.isLoading = false;
          });
      }
    },
    closeWindow() {
      this.$emit("close");
    },
  },
};
</script>
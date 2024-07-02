<template>
  <div class="settings-container" v-if="roles">
    <v-container fluid>
      <v-card>
        <v-row justify="space-between" class="px-4 pt-2 pb-2">
          <div class="header">
            <v-icon color="primary" x-large
              >$vuetify.icons.account-network</v-icon
            >
            <span class="title">{{ $t("Roles") }}</span>
          </div>
          <div class="options">
            <v-btn
              class="primary"
              v-if="hasPermission('Edit roles')"
              :disabled="loading"
              @click="editRole('new')"
            >
              <template v-slot:loader>
                <span class="custom-loader">
                  <v-icon light>$vuetify.icons.cached</v-icon>
                </span>
              </template>
              <v-icon left>$vuetify.icons.plus</v-icon>
              {{ $t("New Role") }}</v-btn
            >
            <!-- <v-btn
              class="info"
              v-if="hasPermission('Edit roles')"
              :disabled="loading"
              @click="reset"
              >{{ $t("Reset") }}</v-btn
            > -->
            <v-btn
              class="success"
              @click="sync"
              v-if="hasPermission('Edit roles')"
              :disabled="loading"
              :title="$t('Synchronize the changes for the exisiting users')"
            >
              <template v-slot:loader>
                <span class="custom-loader">
                  <v-icon light>$vuetify.icons.cached</v-icon>
                </span>
              </template>
              <v-icon left>$vuetify.icons.sync</v-icon>
              {{ $t("Sync") }}</v-btn
            >
          </div>
        </v-row>
      </v-card>
      <v-row justify="space-between" class="px-4 pt-2 pb-2">
        <v-col cols="12">
          <v-alert
            border="top"
            colored-border
            type="info"
            elevation="2"
            color="primary"
          >
            <!-- Here you can set the default permissions for the different roles, Keep
          in mind that changing permissions here will only take impact on the
          next role attachments or new user accounts. If you want to remove/add
          permissions for specific users you can do it on:
          <code>users > user > edit permissions</code>.
          <br />
          <br /> -->
            <strong>CED</strong>: Create, edit and delete.
          </v-alert>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" sm="6" v-for="role in roles" :key="role.id">
          <v-card>
            <v-card-title>
              <v-icon color="primary">$vuetify.icons.account</v-icon>
              {{ role.name }}
              <v-spacer></v-spacer>
              <v-spacer></v-spacer>
              <div class="operations">
                <div class="buttons">
                  <v-btn
                    class="info white--text"
                    small
                    @click="editRole(role)"
                    v-if="hasPermission('Edit roles')"
                  >
                    <v-icon small>$vuetify.icons.plus</v-icon>
                    {{ $t("Edit") }}
                  </v-btn>
                  <v-btn
                    class="error white--text"
                    small
                    @click="deleteRole(role)"
                    v-if="hasPermission('Edit roles') && role.custom"
                  >
                    <v-icon small>$vuetify.icons.delete</v-icon>
                    {{ $t("Delete") }}
                  </v-btn>
                </div>
              </div>
            </v-card-title>
            <v-container class="pl-2" fluid>
              <div class="card-bb-header">
                <div class="header">
                  <div class="title">{{ $t("Permissions") }}</div>
                </div>
                <v-divider class="py-2"></v-divider>
                <div class="body">
                  <div
                    class="item"
                    v-for="(permission) in role.current_permissions"
                    :key="permission.id"
                  >
                    <v-chip
                      >{{ permission.name }}</v-chip
                    >
                  </div>
                </div>
              </div>
            </v-container>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    <v-dialog v-model="showDialog" max-width="900" :persistent="editingRole">
      <edit-role-dialog
        v-if="showDialog && editingRole"
        :roles="roles"
        @update="roleUpdated"
        @close="showDialog = false"
        :role="editingRole"
      />
    </v-dialog>
  </div>
</template>

<script>
import editRoleDialog from "../../dialogs/admin/edit/Role";
export default {
  components: {
    editRoleDialog,
  },
  created() {
    this.fetchRoles();
  },
  data() {
    return {
      roles: null,
      editingRole: null,
      showDialog: false,
      loading: false,
      newRoleDialog: false,
    };
  },
  methods: {
    fetchRoles() {
      return axios
        .get("/api/admin/roles")
        .then((res) => (this.roles = res.data));
    },
    save() {
      this.$confirm({
        message: `${this.$t(
          "Are you sure you wanna save the current permissions?"
        )}`,
        button: {
          no: this.$t("Cancel"),
          yes: "Save",
        },
        /**
         * Callback Function
         * @param {Boolean} confirm
         */
        callback: (confirm) => {
          if (confirm) {
            this.loading = true;
            axios
              .post("/api/admin/save-roles", { roles: this.roles })
              .then(() => {
                this.$notify({
                  group: "foo",
                  type: "success",
                  title: this.$t("Updated"),
                  text:
                    this.$t("Roles") + " " + this.$t("updated successfully."),
                });
              })
              .finally(() => (this.loading = false));
          }
        },
      });
    },
    reset() {
      this.$confirm({
        message: `${this.$t(
          "Are you sure you wanna reset to the default settings?"
        )}`,
        button: {
          no: this.$t("Cancel"),
          yes: "Reset",
        },
        /**
         * Callback Function
         * @param {Boolean} confirm
         */
        callback: (confirm) => {
          if (confirm) {
            this.loading = true;
            axios
              .post("/api/admin/reset-roles")
              .then(() => {
                this.fetchRoles().then(() => {
                  this.$notify({
                    group: "foo",
                    type: "success",
                    title: this.$t("Success"),
                    text: this.$t("Roles reset successfully."),
                  });
                });
              })
              .finally(() => (this.loading = false));
          }
        },
      });
    },
    editRole(role) {
      if (!role) {
        this.showDialog = false;
        this.editingRole = null;
      } else if (role == "new") {
        this.editingRole = {
          new: true,
          name: "",
          category: "",
          custom: true,
          current_permissions: [],
        };
        this.showDialog = true;
      } else {
        this.editingRole = role;
        this.showDialog = true;
      }
    },
    deleteRole(role) {
      this.$confirm({
        message: `${
          this.$t("Are you sure you wanna delete this") +
          " " +
          this.$t("Role") +
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
              .delete("/api/admin/roles/" + role.id)
              .then(() => {
                this.$notify({
                  group: "foo",
                  type: "success",
                  title: this.$t("Deleted"),
                  text: this.$t("Role") + " " + this.$t("Deleted") + ".",
                });
              })
              .catch(() => {})
              .finally(() => this.fetchRoles());
          }
        },
      });
    },
    sync() {
      this.loading = true;
      axios
        .post("/api/admin/roles/sync")
        .then(() => {
          this.$notify({
            group: "foo",
            type: "success",
            title: this.$t("Synchronized"),
            text: this.$t("Role") + " " + this.$t("updated successfully") + ".",
          });
        })
        .catch(() => {})
        .finally(() => ((this.loading = false), this.fetchRoles()));
    },
    roleUpdated() {
      this.fetchRoles();
      this.editRole(null);
    },
  },
};
</script>
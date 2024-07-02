<template>
  <edit-dialog
    @callToAction="saveRole"
    @cancel="$emit('close')"
    :callToActionText="$t('Save')"
    :editing="$t('Edit Role')"
    :loading="loading"
  >
    <template v-slot:body>
      <div class="edit-dialog__body">
        <v-container>
          <v-text-field
            v-model="editingRole.name"
            :label="$t('Name')"
            v-if="editingRole.custom"
          ></v-text-field>
          <v-select
            v-if="editingRole.custom"
            :label="$t('Category')"
            :items="roles.filter((role) => !role.custom)"
            item-text="name"
            item-value="name"
            @change="updateAvailablePermissions"
            v-model="editingRole.category"
          ></v-select>
          <div class="card-bb-header">
            <div class="header">
              <div class="title">{{ $t("Permissions") }}</div>
            </div>
            <v-container>
              <v-row>
                <v-col
                  cols="12"
                  sm="6"
                  class="permission"
                  v-for="permission in available_permissions"
                  :key="permission.id"
                >
                  <div class="checkbox">
                    <v-checkbox
                      :value="permission"
                      :messages="permission.description"
                      v-model="editingRole.current_permissions"
                    >
                      <template v-slot:label>
                        <div class="permission_label">
                          {{ $t(permission.name) }}
                        </div>
                      </template>
                    </v-checkbox>
                  </div>
                </v-col>
              </v-row>
            </v-container>
          </div>
        </v-container>
      </div>
    </template>
  </edit-dialog>
</template>

<script>
export default {
  props: ["role", "roles"],
  data() {
    return {
      editingRole: JSON.parse(JSON.stringify(this.role)),
      loading: false,
    };
  },
  computed: {
    available_permissions() {
      if (this.editingRole.custom) {
        if (this.editingRole.category) {
          return this.roles.find(
            (role) => role.name === this.editingRole.category
          ).available_permissions;
        } else {
          return [];
        }
      } else {
        return this.editingRole.available_permissions;
      }
    },
  },
  methods: {
    updateAvailablePermissions() {
      //   this.editingRole.available_permissions = this.roles.find(
      //     (role) => role.name === this.editingRole.category
      //   ).available_permissions;
    },
    // customRole(role_) {
    //   const nonCustomRoles = ["admin", "artist", "user"];
    //   return !nonCustomRoles.find((role) => role === role_.name);
    // },
    saveRole() {
      this.loading = true;
      if (this.editingRole.new) {
        axios
          .post("/api/admin/roles", {
            ...this.editingRole,
          })
          .then(() => {
            this.$notify({
              group: "foo",
              type: "success",
              title: this.$t("Created"),
              text:
                this.$t("Role") + " " + this.$t("created successfully") + ".",
            });
            this.$emit("update");
          })
          .catch(() => {
            // this.$notify({
            //   group: "foo",
            //   type: "success",
            //   title: this.$t("Oops!"),
            //   text: this.$t("Something wrong happened") + ".",
            // });
          })
          .finally(() => {
            this.loading = false;
          });
      } else {
        axios
          .post("/api/admin/roles/" + this.editingRole.id, {
            _method: "PUT",
            ...this.editingRole,
          })
          .then(() => {
            this.$notify({
              group: "foo",
              type: "success",
              title: this.$t("Update"),
              text:
                this.$t("Role") + " " + this.$t("updated successfully") + ".",
            });
            this.$emit("update");
          })
          //   .catch(() => {
          //     this.$notify({
          //       group: "foo",
          //       type: "success",
          //       title: this.$t("Oops!"),
          //       text: this.$t("Something wrong happened") + ".",
          //     });
          //   })
          .finally(() => {
            this.loading = false;
          });
      }
    },
  },
};
</script>
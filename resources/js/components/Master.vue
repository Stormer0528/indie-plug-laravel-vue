<template>
  <v-app>
    <router-view></router-view>
    <vue-confirm-dialog></vue-confirm-dialog>
    <dialog-wrapper v-if="$store.getters.getDialog"></dialog-wrapper>
    <notifications group="foo" classes="muzzie-notification" />
  </v-app>
</template>
<script>
export default {
  created() {
    if (localStorage.getItem("dark-theme") == "true") {
      this.$vuetify.theme.dark = true;
    } else {
      if (localStorage.getItem("dark-theme") == "false") {
        this.$vuetify.theme.dark = false;
      } else {
        this.$vuetify.theme.dark =
          this.$store.getters.getSettings.defaultTheme === "Dark";
      }
    }
  },
  mounted() {
    if (
      this.$store.getters.getSettings.enableGoogleLogin &&
      this.$store.getters.getSettings.google_oauth_client_id
    ) {
      let googleSDK = document.createElement("script");
      let googleMeta = document.createElement("meta");
      googleMeta.setAttribute("name", "google-signin-client_id");
      googleMeta.setAttribute(
        "content",
        this.$store.getters.getSettings.google_oauth_client_id
      );
      googleSDK.setAttribute("src", "https://apis.google.com/js/platform.js");
      document.head.appendChild(googleSDK);
      document.head.appendChild(googleMeta);
    }
  },
};
</script>

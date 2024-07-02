<template>
  <v-menu
    :close-on-content-click="closeOnContentClick || false"
    top
    :style="{ zIndex: 50000 }"
  >
    <template v-slot:activator="{ on, attrs }">
      <v-btn
        icon
        :dark="dark"
        :disabled="disabled"
        v-bind="attrs"
        v-on="on"
        class="m-0"
        width="22px"
      >
        <v-icon v-if="!icon">$vuetify.icons.dots-horizontal</v-icon>
        <v-icon v-else>$vuetify.icons.{{ icon }}</v-icon>
      </v-btn>
    </template>
    <v-card
      min-width="160"
      v-if="item"
      :class="{ 'dark-background': $vuetify.theme.dark }"
    >

      <v-divider></v-divider>
      <div class="body">
        <v-list
          class="list"
          :class="{ 'dark-background': $vuetify.theme.dark }"
        >

          <v-list-item @click="showSharingDialog">
            <v-icon small class="list-icon">$vuetify.icons.share</v-icon>
            {{ $t("Share") }}
          </v-list-item>

          <!-- <v-list-item
            >
                <v-list-item-title>
                    <v-icon small class="list-icon">$vuetify.icons.thumb-down</v-icon>
                    {{$t('Dislike')}}
                </v-list-item-title
                >
            </v-list-item> -->
        </v-list>
      </div>
    </v-card>
  </v-menu>
  <!-- <div>
        <ul class="abs-menu-container__list">
            <li
                v-if="$store.getters.getUser"
                @click.stop="
                    $store.commit('setAddSongToPlaylist', item.id);
                    $emit('close');
                "
            >
                <v-icon>$vuetify.icons.playlist-music</v-icon> {{$t('Add To Playlist')}}
            </li>
            <li
                @click.stop="
                    $store.dispatch('playSong', { item, reset: false });
                    $emit('close');
                "
            >
                <v-icon>$vuetify.icons.playlist-music</v-icon> {{$t('Add To Queue')}}
            </li>
            <li v-if="item.artist">
                <div
                    @click.stop="
                        $router.push({
                            name: 'artist',
                            params: { id: item.artist.id }
                        });
                        $emit('close');
                        $emit('goToArtist')
                    "
                >
                    <v-icon>$vuetify.icons.account-music</v-icon>
                    {{$t('Go To Artist')}}
                </div>
            </li>
            <li @click="showSharingDialog">
                <div>
                    <v-icon>$vuetify.icons.share</v-icon>
                    {{$t('Share')}}
                </div>
            </li>
        </ul>
    </div> -->
</template>

<script>
import Billing from "../../../mixins/billing/billing";
import Vue from "vue";
export default {
  props: ["item", "icon", "closeOnContentClick", "dark", "disabled"],
  mixins: [Billing],
  computed: {
    isLikable() {
    //   return function (type) {
    //     const likableAssets = ["album", "song", "radio-station"];
    //     return likableAssets.includes(type);
    //   };
    },
    isFollowable() {
    //   return function (type) {
    //     const likableAssets = ["podcast", "artist", "playlist"];
    //     return likableAssets.includes(type);
    //   };
    },
    isPurchasable() {
    //   return this.item.product && !this.isPurchased(this.item);
    },
    isOwned() {
    //   return (
    //     (this.item.artist &&
    //       this.$store.getters.getUser &&
    //       this.$store.getters.getUser.artist &&
    //       this.item.artist.id === this.$store.getters.getUser.artist.id) ||
    //     (this.item.product && this.isPurchased(this.item))
    //   );
    },
  },
  methods: {
    showSharingDialog() {
      this.$store.commit("shareItem", {
        cover: this.item.cover,
        url: this.getItemURL(this.item),
        title: this.item.title,
        type: this.item.type,
        artist: this.getMainArtist(this.item),
      });
    },
    follow(item) {
    //   this.$store.dispatch("follow", item).then(() => {
    //     this.$notify({
    //       group: "foo",
    //       type: "success",
    //       title: this.$t("Followed"),
    //       text: this.$t(
    //         item.type[0].toUpperCase() +
    //           item.type.slice(1).toLowerCase() +
    //           " added to your follows."
    //       ),
    //     });
    //   });
    },
    unfollow(item) {
    //   this.$store.dispatch("unfollow", item).then(() => {
    //     this.$notify({
    //       group: "foo",
    //       type: "success",
    //       title: this.$t("Unfollowed"),
    //       text: this.$t(
    //         item.type[0].toUpperCase() +
    //           item.type.slice(1).toLowerCase() +
    //           " removed to your follows."
    //       ),
    //     });
    //   });
    },
    like(item) {
    //   this.$store.dispatch("like", item).then(() => {
    //     this.$notify({
    //       group: "foo",
    //       type: "success",
    //       title: this.$t("Liked"),
    //       text: this.$t(
    //         item.type[0].toUpperCase() +
    //           item.type.slice(1).toLowerCase() +
    //           " added to your likes."
    //       ),
    //     });
    //   });
    },
    dislike(item) {
    //   this.$store.dispatch("dislike", item).then(() => {
    //     this.$notify({
    //       group: "foo",
    //       type: "success",
    //       title: this.$t("Disliked"),
    //       text: this.$t(
    //         item.type[0].toUpperCase() +
    //           item.type.slice(1).toLowerCase() +
    //           " removed to your likes."
    //       ),
    //     });
    //   });
    },
    async playAble(video) {
        // if (!this.$store.getters.getUser && !this.$store.getters.isLogged) {
        //     await this.loginOrCancel();
        // } if (this.$store.getters.getUser && this.$store.getters.isLogged && song.is_only_for_subscriber && this.$store.getters.getUser.plan.free && !this.$store.getters.getUser.is_admin) {
        //     return new Promise((res, rej) => {
        //         Vue.$confirm({
        //             message: `You need to subscribe to play this song.`,
        //             button: {
        //                 no: "Cancel",
        //                 yes: "Subscribe"
        //             },
        //             callback: confirm => {
        //                 if (confirm) {
        //                 res(this.$router.push({ name: "subscription" }));
        //                 } else {
        //                 rej();
        //                 }
        //             }
        //         });
        //     });
        // } else {
        //     this.play(song, true);
        // }
    },
  },
};
</script>

<style lang="scss" scoped>
.header {
  padding: 0.5em;
  .title {
    font-size: 0.8em !important;
    font-weight: bold;
    line-height: 1.6;
  }
  .artist {
    font-size: 0.8em;
    line-height: 1.8;
  }
  .separator-2 {
    margin: 0 0.5em;
  }
  .actions {
    margin-top: 1em;
  }
}
.body {
  .list-icon {
    margin-right: 0.5em;
  }
  .list {
    font-size: 0.9em !important;
    .v-list-item__title {
      font-size: 0.9em !important;
    }
  }
  .theme--dark.v-list {
    background-color: var(--dark-theme-panel-bg-color);
  }
}
</style>

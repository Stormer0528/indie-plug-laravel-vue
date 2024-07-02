<template>
  <div class="content-item" v-if="video">
    <div class="content-item__header">
      <slot name="control-layer"></slot>
      <div
        class="control-layer"
        :class="{
          'force-appearence': $store.getters.getSongMenu === compid,
        }"
        v-if="!admin"
      >
        <div class="buttons">
          <!-- <div
                        class="button-svg-container button-heart"
                        v-if="isLiked"

                    >
                        <v-btn icon small>
                            <v-icon color="white">$vuetify.icons.heart</v-icon>
                        </v-btn>
                    </div>
                    <div
                        class="button-svg-container button-heart"
                        v-else

                    >
                        <v-btn icon small>
                            <v-icon color="white">$vuetify.icons.heart-outline</v-icon>
                        </v-btn>
                    </div> -->
          <button
            class="button button-play mx-3"
            v-if="imIPlayingNow"
            @click.stop="pause"
          >
            <v-icon size="55" dark>$vuetify.icons.pause-circle</v-icon>
          </button>
          <button class="button button-play mx-3" @click.stop="playAble(video)">
            <v-icon size="55" dark>$vuetify.icons.play-circle</v-icon>
          </button>
          <div class="button button-dots">
            <video-menu
              :item="video"
              :dark="true"
              :closeOnContentClick="true"
              @close="$store.commit('setVideoMenu', null)"
            ></video-menu>
          </div>
          <!-- vuetify icon + menu -->
        </div>
      </div>
      <div class="cover-layer">
        <v-img :src="video.cover" class="img" aspect-ratio="1">
          <template v-slot:placeholder>
            <div class="song-expo-skeleton fill-height">
              <content-placeholders :rounded="true">
                <content-placeholders-img />
              </content-placeholders>
            </div>
          </template>
        </v-img>
        <div class="badges-layer"></div>
      </div>
    </div>
    <div class="content-item__body">
      <router-link
        class="content-item__body__title__container"
        :to="{ name: 'video', params: { id: video.id } }"
      >
        <div class="content-item__body__type">
          {{ $t("Video") }}
        </div>
        <div class="content-item__body__title max-2-lines">
          {{ video.title }}
        </div>
      </router-link>
      <div class="artist max-1-lines">
        <artists :artists="video.artists ?? []"></artists>
      </div>
    </div>
  </div>
</template>
<script>
// import Song from "../../dialogs/edit/Song.vue";
import Vue from "vue";
export default {
  // components: { Song },
  props: ["video", "admin"],

  data() {
    return {
      showMenu: false,
      imIPlayingNow: false,
      compid: Math.random() * Math.floor(500000),
    };
  },
  computed: {},
  methods: {
    playAble(video) {
      this.$router.push({ name: "video", params: { id: video.id } });
    },
    isLiked() {
      return false;
    },
  },
};
</script>

<style lang="scss" scoped>
#play-btn {
  width: 70px;
  height: 70px;
  @media (max-width: 1500px) {
    width: 50px;
    height: 50px;
  }
}
</style>

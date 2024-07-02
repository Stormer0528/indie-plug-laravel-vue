<template>
  <div
    class="podcast-box-container content-item"
    v-if="podcast"
    :class="{ hovered: imIPlayingNow }"
  >
    <div class="content-item__header">
      <div class="cover-layer">
        <v-img :src="podcast.cover" aspect-ratio="1" class="img">
          <template v-slot:placeholder>
            <div class="song-expo-skeleton fill-height">
              <content-placeholders :rounded="true">
                <content-placeholders-img />
              </content-placeholders>
            </div>
          </template>
        </v-img>
      </div>
      <slot name="control-layer"></slot>
      <div class="control-layer" v-if="!admin">
        <div class="buttons">
          <button
            class="button button-play mx-3"
            v-if="imIPlayingNow"
            @click.stop="pause"
          >
            <v-icon size="55" dark>$vuetify.icons.pause-circle</v-icon>
          </button>
          <button
            class="button button-play mx-3"
            v-else
            @click="play(podcast, true)"
          >
            <v-icon size="55" dark>$vuetify.icons.play-circle</v-icon>
          </button>
        </div>
      </div>
    </div>
    <div class="content-item__body">
      <router-link
        class="content-item__body__title__container"
        :to="{ name: 'podcast', params: { id: podcast.id } }"
      >
        <div class="content-item__body__type">
          {{ $t("Podcast") }}
        </div>
        <div class="content-item__body__title max-2-lines">
          {{ podcast.title }}
        </div>
      </router-link>
      <div
        class="content-item__body__sub artist"
        v-if="podcast.artist && podcast.artist.id"
      >
        <router-link
          :key="podcast.artist.id"
          :to="{ name: 'artist', params: { id: podcast.artist.id } }"
        >
          {{ podcast.artist.displayname }}
        </router-link>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: ["podcast", "admin"],
  computed: {
    imIPlayingNow() {
      return this.isCurrentlyPlaying(this.podcast);
    },
  },
};
</script>

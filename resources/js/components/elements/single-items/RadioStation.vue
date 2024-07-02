<template>
  <div
    class="radio_station-box-container content-item"
    v-if="radioStation"
    :class="{ hovered: imIPlayingNow }"
  >
    <div class="content-item__header">
        <slot name="control-layer"></slot>
      <div class="cover-layer">
        <v-img :src="radioStation.cover" aspect-ratio="1" class="img">
          <template v-slot:placeholder>
            <div class="song-expo-skeleton fill-height">
              <content-placeholders :rounded="true">
                <content-placeholders-img />
              </content-placeholders>
            </div>
          </template>
        </v-img>
      </div>
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
            @click="play(radioStation, true)"
          >
            <v-icon size="55" dark>$vuetify.icons.play-circle</v-icon>
          </button>
        </div>
      </div>
    </div>
    <div class="content-item__body">
      <router-link
        :to="{ name: 'radio-station', params: { id: radioStation.id } }"
        class="content-item__body__type relative router-link"
      >
        {{ $t("Radio Station") }}
        <span class="absolute live">
          <svg height="20" width="17" class="blinking">
            <circle cx="5" cy="10" r="3" fill="red"></circle>
          </svg>
        </span>

        <div class="content-item__body__title">
          <div class="max-1-lines line-heigh-15">
            {{ radioStation.name }}
          </div>
        </div>
      </router-link>
      <!-- <div class="content-item__body__sub artist" v-if="radioStation.artist && radioStation.artist.id">
                <router-link
                    :key="radioStation.artist.id"
                    :to="{ name: 'artist', params: { id: radioStation.artist.id } }"
                >
                    {{ radioStation.artist.displayname }}
                </router-link>
            </div> -->
    </div>
  </div>
</template>
<script>
export default {
  props: ["radioStation", "admin"],
  computed: {
    imIPlayingNow() {
      return this.isCurrentlyPlaying(this.radioStation);
    },
  },
};
</script>

<style lang="scss">
.live {
  top: -2px;
}
.line-heigh-15 {
  line-height: 1.5;
}
</style>
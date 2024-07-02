<template>
  <div class="hlf-page-container elevate-page" v-if="radioStation">
    <div
      class="cover-img"
      :style="{ backgroundImage: 'url(' + radioStation.cover + ')' }"
    >
      <div class="overlay"></div>
      <div class="song-info">
        <v-img :src="radioStation.cover" aspect-ratio="1" class="cover">
          <template v-slot:placeholder>
            <div class="radioStation-expo-skeleton fill-height">
              <content-placeholders :rounded="true">
                <content-placeholders-img />
              </content-placeholders>
            </div>
          </template>
        </v-img>
        <!-- <div
          class="main-genre align-center"
          v-if="radioStation.genres && radioStation.genres.length"
        >
          <router-link
            class="router-link"
            :to="{
              name: 'genre-page',
              params: {
                genre_name: slug(radioStation.genres[0].name),
                id: radioStation.genres[0].id,
              },
            }"
          >
            <div class="genre">{{ radioStation.genres[0].name }} {{ $t("RadioStation") }}</div>
          </router-link>
          <div class="badges align-center">
            <div
              class="exclusive mr-2"
              :title="$t('Exclusive')"
              v-if="radioStation.isExclusive"
            >
              <v-btn x-small dense depressed color="primary">{{
                $t("Exclusive")
              }}</v-btn>
            </div>
            <div
              class="explicit mr-2"
              :title="$t('Explicit')"
              v-if="radioStation.isExplicit"
            >
              <div class="explicit__sign">E</div>
            </div>
          </div>
        </div> -->
        <div class="song-title">
          {{ radioStation.name }}
        </div>

        <!-- <div class="radioStation-infos">
          <h3>
            <template v-if="radioStation.album">
              <small>{{ $t("From") }}</small>
              <router-link
                class="router-link router-link__white"
                :to="{
                  name: 'album',
                  params: { id: radioStation.album.id },
                }"
              >
                {{ radioStation.album.title }}
              </router-link></template
            >
            <template v-if="radioStation.artists && radioStation.artists.length">
              <small>{{ $t("By") }}</small>
              <artists :artists="radioStation.artists" :textColor="'white'"></artists>
            </template>
          </h3>
        </div> -->
      </div>
    </div>
    <div class="main">
      <div
        class="play-button"
      >
        <v-btn
          color="primary"
          x-large
          rounded
          @click="pause"
          v-if="isCurrentlyPlaying(radioStation)"
        >
          <v-icon size="25" left>$vuetify.icons.pause</v-icon>
          {{ $t("Pause") }}
        </v-btn>
        <v-btn
          color="primary"
          x-large
          rounded
          @click="play(radioStation, true)"
          :disabled="loading"
          v-else
        >
          <v-icon size="25" left>$vuetify.icons.play</v-icon>
          {{ $t("Play") }}
        </v-btn>
      </div>
      <div class="other-actions">
        <v-sheet
          rounded="pill"
          class="py-1 px-4 d-flex align-center justify-space-between"
          elevation="1"
          min-width="150px"
        >
          <div class="share-btn">
            <v-btn small icon @click="share" color="secondary">
              <v-icon>$vuetify.icons.share-variant</v-icon>
            </v-btn>
          </div>
          <purchase-button :size="'small'" :item="radioStation"></purchase-button>
          <external-links
            :content="radioStation"
            v-if="
              radioStation.spotify_link ||
              radioStation.itunes_link ||
              radioStation.soundcloud_link ||
              radioStation.youtube_link
            "
          />
          <!-- <div v-if="!$store.getters.getSettings.disableRegistration">
            <div v-if="isLiked" @click="like(radioStation)">
              <v-btn icon small>
                <v-icon color="primary">$vuetify.icons.heart</v-icon>
              </v-btn>
            </div>
            <div v-else @click="like(radioStation)">
              <v-btn icon small>
                <v-icon color="primary">$vuetify.icons.heart-outline</v-icon>
              </v-btn>
            </div>
          </div> -->
          <!-- <song-menu
            :item="radioStation"
            :closeOnContentClick="true"
            @close="$store.commit('setSongMenu', null)"
          ></song-menu> -->
        </v-sheet>
      </div>
      <template v-if="$store.getters.getSettings.enableAds">
        <div class="ad-slot">
          <ad
            :ad_code="parseAd($store.getters.getSettings.rect_ad).code"
            @click="
              $store.getters.getSettings.ga4 &&
              $store.getters.getSettings.analytics_adClick_event
                ? emitAnalyticsEvent({
                    action: 'ad_click',
                    category: 'leaderboard_ad',
                    label: 'RadioStation page',
                  })
                : ''
            "
          ></ad>
        </div>
      </template>
      <div class="sections pt-5">
        <div
          class="section-more-from-album"
          v-if="moreFromAlbum && moreFromAlbum.length"
        >
          <swiper-section
            :content="moreFromAlbum"
            rank="1"
            :title="
              $t('More from') +
              ' ' +
              '&quot;' +
              radioStation.album.title +
              '&quot;' +
              ' ' +
              $t('Album')
            "
          />
        </div>
        <!-- <div
          class="section-more-from-artist"
          v-if="moreFromArtists && moreFromArtists.length"
        >
          <swiper-section
            :content="moreFromArtists"
            rank="1"
            :title="$t('More from the same aritsts')"
          />
        </div> -->
        <!-- <div
          class="section-more-from-genre"
          v-if="moreFromGenre && moreFromGenre.length"
        >
          <swiper-section
            :content="moreFromGenre"
            rank="2"
            :title="$t('More') + ' ' + radioStation.genres[0].name + ' ' + $t('Music')"
          />
        </div> -->
      </div>
    </div>
  </div>
  <empty-page
    v-else-if="!radioStation && errorStatus == 404"
    :headline="$t('Not Available!')"
    :sub="
      $t(
        'RadioStation does not exist or maybe it is not available for public access. Who knows!'
      )
    "
    img="peep-68.png"
  >
    <template v-slot:button>
      <v-btn class="primary" rounded small @click="$router.go(-1)">
        {{ $t("Go back") }}
      </v-btn>
    </template>
  </empty-page>
  <empty-page
    v-else-if="!radioStation && errorStatus"
    :headline="$t('Something wrong happend!')"
    :sub="$t('Some server error has occurred. Try again later.')"
    img="peep-68.png"
  >
    <template v-slot:button>
      <v-btn class="primary" rounded small @click="$router.go(-1)">
        {{ $t("Go back") }}
      </v-btn>
    </template>
  </empty-page>
  <page-loading v-else />
</template>
<script>
import PurchaseButton from "../../elements/other/ProductBtn.vue";
export default {
  components: { PurchaseButton },
  metaInfo() {
    return {
      title: this.radioStation ? (this.radioStation.name + ' | ' + this.$t('Play on') +  ' ' + this.$store.getters.getSettings.appName) : '',
      meta: [
        {
          name: "description",
          content: this.generateMetaInfos(
            this.radioStation,
            this.$store.getters.getSettings.songPageDescription,
            "radioStation"
          ),
        },
      ],
    };
  },
  created() {
    axios
      .get("/api/radio-station/" + this.$route.params.id)
      .then((res) => {
        this.radioStation = res.data;
        // axios
        //   .get(
        //     "/api/more-from-album?id=" +
        //       this.radioStation.id +
        //       "&origin=" +
        //       this.radioStation.origin
        //   )
        //   .then(
        //     (res) =>
        //       (this.moreFromAlbum = res.data.filter(
        //         (radioStation) => radioStation.id !== this.radioStation.id
        //       ))
        //   );
        // axios
        //   .get(
        //     "/api/more-from-artist?id=" +
        //       this.radioStation.id +
        //       "&origin=" +
        //       this.radioStation.origin
        //   )
        //   .then(
        //     (res) =>
        //       (this.moreFromArtists = res.data.filter(
        //         (radioStation) => radioStation.id !== this.radioStation.id
        //       ))
        //   );
        // if (this.radioStation.genres.length) {
        //   axios
        //     .get("/api/more-from-genre/" + this.radioStation.genres[0].id)
        //     .then(
        //       (res) =>
        //         (this.moreFromGenre = res.data.filter(
        //           (radioStation) => radioStation.id !== this.radioStation.id
        //         ))
        //     );
        // }
      })
      .catch((e) => {
        if (e.response.status === 404) {
          this.errorStatus = 404;
        } else {
          this.errorStatus = 500;
        }
      });
  },
  computed: {
    isLiked() {
      if (this.radioStation) {
        return (this.$store.getters.getLikedSongs || []).some(
          (x) => x.id == this.radioStation.id
        );
      }
    },
  },
  data() {
    return {
      radioStation: null,
      moreFromArtists: null,
      moreFromAlbum: null,
      moreFromGenre: null,
      allowedToWrite: null,
      errorStatus: null,
    };
  },
  methods: {
    share() {
      this.$store.commit("shareItem", {
        cover: this.radioStation.cover,
        url: this.getItemURL(this.radioStation),
        title: this.radioStation.title,
        type: this.radioStation.type,
        artist: this.getMainArtist(this.radioStation),
      });
    },
    async like(radioStation) {
      if (this.isLiked) {
        this.$store.dispatch("dislike", radioStation);
      } else {
        this.$store.dispatch("like", radioStation);
      }
    },
  },
};
</script>
<style lang="scss">
.hlf-page-container {
  margin: -12px;
  padding-bottom: 3em;
  .other-actions {
    position: absolute;
    right: 3em;
    top: 0;
    z-index: 3;
    transform: translateY(-50%);
    @media (max-width: 400px) {
      right: 1em;
    }
  }
  .main {
    position: relative;
    padding: 2em 1em;
    padding-top: 3em;
    .stats {
      display: flex;
      font-size: 2em;
      opacity: 0.8;
      justify-content: center;
      .plays {
        margin: 0 2em;
      }
    }
    .links {
      justify-content: center;
      .link {
        margin: 0 1em;
      }
    }
    .play-button {
      width: 8%;
      min-width: 4.5em;
      position: absolute;
      max-width: 8em;
      left: 3em;
      top: 0;
      z-index: 3;
      transform: translateY(-50%);
      @media (max-width: 400px) {
        left: 1em;
      }
      .svg-image {
        width: 100%;
        z-index: 1;
      }
      .back-layer {
        width: 5em;
        height: 5em;
        z-index: -1;
        border-radius: 50%;
        background-color: white;
        @media (max-width: 1400px) {
          width: 3em;
          height: 3em;
        }
      }
    }
  }
  .cover-img {
    height: 50vh;
    overflow: hidden;
    position: relative;
    background-size: cover;
    background-position: center;
    .song-info {
      position: absolute;
      display: flex;
      flex-direction: column;
      justify-content: center;
      bottom: 0;
      top: 0;
      left: 0;
      padding-left: 3em;
      z-index: 2;
      color: white;

      right: 0;
      .genre {
        background: #fff;
        color: #000;
        display: inline-block;
        text-transform: uppercase;
        border-radius: 0.2rem;
        font-weight: 600;
        margin-right: 1em;
        font-size: 0.8em;
        padding: 0 0.6em;
      }
      .song-title {
        font-weight: bold;
        font-size: 4.5em;
        @media screen and (max-width: 1400px) {
          font-size: 3em;
        }
      }
      .song-artist {
        font-weight: 400;
        font-size: 3.5em;
      }
    }
    .actions {
      position: absolute;
      top: 5%;
      z-index: 4;
      right: 4%;
      display: flex;
      align-items: center;
      & > * {
        padding: 0 0.3em;
      }
    }
    .overlay {
      position: absolute;
      bottom: 0;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1;
      background: rgb(0, 0, 0);
      background: linear-gradient(
        0deg,
        rgba(0, 0, 0, 0.9023984593837535) 0%,
        rgba(0, 0, 0, 0.6502976190476191) 33%,
        rgba(0, 0, 0, 0.3253676470588235) 100%
      );
    }
    .cover-background {
      width: 100%;
      transform: translateY(-20%);
      filter: blur(2px);
      @media screen and (max-width: 1000px) {
        transform: translateY(-3%);
      }
    }
  }
  .cover {
    width: 100px;
    border-radius: 15px;
    top: 20px;
    position: absolute;
    @media screen and (max-width: 550px) {
      display: none;
    }
  }
}
.player--dark {
  .hlf-page-container {
    .main {
      .play-button {
        .back-layer {
          background-color: black;
        }
      }
    }
  }
}
</style>

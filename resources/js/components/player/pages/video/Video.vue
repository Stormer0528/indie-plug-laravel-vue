

<template>
  <div class="hlf-page-container elevate-page" v-if="video">
    <div
      class="cover-img"
      :style="{ backgroundImage: 'url(' + video.cover + ')' }"
    >
      <div class="overlay"></div>
      <div class="song-info">
        <v-img :src="video.cover" aspect-ratio="1" class="cover">
          <template v-slot:placeholder>
            <div class="song-expo-skeleton fill-height">
              <content-placeholders :rounded="true">
                <content-placeholders-img />
              </content-placeholders>
            </div>
          </template>
        </v-img>

        <div class="song-title">
          {{ video.title }}
        </div>
        <div class="song-infos">
          <h3>
            <template v-if="video.album">
              <small>{{ $t("From") }}</small>
              <router-link
                class="router-link router-link__white"
                :to="{
                  name: 'album',
                  params: { id: video.album.id },
                }"
              >
                {{ video.album.title }}
              </router-link></template
            >
            <template v-if="video.artists && video.artists.length">
              <small>{{ $t("By") }}</small>
              <artists :artists="video.artists" :textColor="'white'"></artists>
            </template>
          </h3>
        </div>
      </div>
      <div class="actions"></div>
    </div>
    <div class="main">
      <div class="play-button">
        <v-btn
          color="primary"
          x-large
          rounded
          @click="pause"
          v-if="isCurrentlyPlaying(video)"
        >
          <v-icon size="25" left>$vuetify.icons.pause</v-icon>
          {{ $t("Pause") }}
        </v-btn>
        <v-btn color="primary" x-large rounded @click="playVideo(video)" v-else>
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

          <!-- <div v-if="!$store.getters.getSettings.disableRegistration">
              <div v-if="isLiked" @click="like(video)">
                <v-btn icon small>
                  <v-icon color="primary">$vuetify.icons.heart</v-icon>
                </v-btn>
              </div>
              <div v-else @click="like(video)">
                <v-btn icon small>
                  <v-icon color="primary">$vuetify.icons.heart-outline</v-icon>
                </v-btn>
              </div>
            </div> -->
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
                    label: 'Song page',
                  })
                : ''
            "
          ></ad>
        </div>
      </template>
      <div class="sections pt-5">
        <div id="video_player"></div>
      </div>
    </div>
    <section class="section-container swiper-section">
        <div class="section-header">
            <div class="section-header__title">Suggested Videos</div>
            <div class="chevrons d-flex">
            </div>
        </div>
        <div class="section-header-small-screen">
            <div class="section-header__title">Suggested Videos</div>
        </div>

        <swiper
            ref="mySwiper"
            :options="swiperOptions"
            @slideChange="onSlideChange"

        >
            <swiper-slide v-for="(asset, i) in _content" :key="i">

                <template v-if="asset.type === 'video'">
                    <video-expo :video="asset" />
                </template>

            </swiper-slide>
        </swiper>
        <div
            class="swiper-prev-el"
            :id="'swiper-button-prev'"
            slot="button-prev"
            v-show="prevBtn"
        >
            <v-btn fab small height="35" width="35">
                <v-icon>
                    $vuetify.icon.chevron-left
                </v-icon>
            </v-btn>
        </div>
        <div
            class="swiper-next-el"
            :id="'swiper-button-next'"
            slot="button-next"
            v-show="nextBtn"
        >
            <v-btn fab height="35" width="35">
                <v-icon>
                    $vuetify.icon.chevron-right
                </v-icon>
            </v-btn>
        </div>
    </section>

  </div>
  <empty-page
    v-else-if="!video && errorStatus == 404"
    :headline="$t('Not Available!')"
    :sub="
      $t(
        'Video does not exist or maybe it is not available for public access. Who knows!'
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
    v-else-if="!video && errorStatus"
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
import { swiper, swiperSlide } from "vue-awesome-swiper";
import "swiper/css/swiper.css";

import OvenPlayer from "ovenplayer";
import axios from "axios";
import Vue from "vue";

export default {
  components: {
    swiper,
    swiperSlide,
  },

  metaInfo() {
    return {
      title: "Sample Video Title",
      meta: [
        {
          name: "description",
          content: "Video Content",
        },
      ],
    };
  },

  created() {
    axios
      .get("/api/videos/" + this.$route.params.id)
      .then((res) => {
        this.video = res.data;
      })
      .catch((e) => {
        if (e.response.status === 404) {
          this.errorStatus = 404;
        } else {
          this.errorStatus = 500;
        }
      });

    this.getSuggestedVideos();
  },

  props: {
    _breakpoints: {
      type: Object,
      default: function () {
        return {
          4000: {
            slidesPerView: 12,
            spaceBetweenSlides: 10,
          },
          2180: {
            slidesPerView: 9,
            spaceBetweenSlides: 10,
          },
          1920: {
            slidesPerView: 7,
            spaceBetweenSlides: 10,
          },
          1500: {
            slidesPerView: 5,
            slidesPerGroup: 5,
            spaceBetweenSlides: 20,
          },
          1200: {
            slidesPerView: 4,
            slidesPerGroup: 4,
            spaceBetweenSlides: 20,
          },
          750: {
            slidesPerView: 3,
            slidesPerGroup: 3,
            spaceBetweenSlides: 10,
          },
          500: {
            slidesPerView: 2,
            slidesPerGroup: 2,
            spaceBetweenSlides: 10,
          },
        };
      },
    },
  },
  computed: {
    _content() {
      return this.contents;
    },
    isLiked() {
      // if (this.song) {
      //   return (this.$store.getters.getLikedSongs || []).some(
      //     (x) => x.id == this.song.id
      //   );
      // }
    },
  },
  data() {
    return {
      video: null,
      moreFromArtists: null,
      moreFromAlbum: null,
      moreFromGenre: null,
      allowedToWrite: null,
      errorStatus: null,
      _swiper: null,
      prevBtn: false,
      nextBtn: true,
      contents: null,
      contentLength: 0,
      serverError: false,
      isContentLoading: false,
      swiperOptions: {
        slidesPerView: 6,
        spaceBetween: 0,
        slidesPerGroup: 5,
        navigation: {
          nextEl: "#swiper-button-next",
          prevEl: "#swiper-button-prev",
        },
        breakpoints: this._breakpoints,
      },
      hasBeenCounted: false
    };
  },
  methods: {
    onSlideChange() {
      this.prevBtn = !this.$refs.mySwiper.swiper.isBeginning;
      this.nextBtn = !this.$refs.mySwiper.swiper.isEnd;
    },

    async playVideo(video) {
      if (!this.$store.getters.getUser && !this.$store.getters.isLogged) {
        await this.loginOrCancel();
      }
      if (this.$store.getters.getUser && this.$store.getters.isLogged && video.is_only_for_subscriber && !this.$store.getters.getUser.is_admin) {
        return new Promise((res, rej) => {
          Vue.$confirm({
            message: `You need to subscribe to play this song.`,
            button: {
              no: "Cancel",
              yes: "Subscribe"
            },
            callback: confirm => {
              if (confirm) {
                res(this.$router.push({ name: "subscription" }));
              } else {
                rej();
              }
            }
          });
        });
      } else {
        this.videoPlayer = OvenPlayer.create("video_player", {
          title: video.title,
          image: video.cover,
          file: video.source,
          autoStart: true
        });
        this.videoPlayer.on('time', (data) => {
            if (data.position >= 65 && !this.hasBeenCounted) {
                this.createPlayCount(video);
            }
        })
      }
    },
    isCurrentlyPlaying(video) {},
    createPlayCount(video) {
      this.$store.dispatch("registerPlayAndRoyaltyCount", {
        id: video.id,
        type: "video",
        label: video.title,
        duration: 0,
        origin: 'file',
        artist_id: video.artist?.id
      });
      this.hasBeenCounted = true
    },

    share() {
      this.$store.commit("shareItem", {
        cover: this.video.cover,
        url: this.getItemURL(this.video),
        title: this.video.title,
        type: this.video.type,
        artist: this.getMainArtist(this.video),
      });
    },
    async like(video) {
      if (this.isLiked) {
        this.$store.dispatch("dislike", video);
      } else {
        this.$store.dispatch("like", video);
      }
    },

    getSuggestedVideos() {
      axios
        .get("/api/suggested-videos")
        .then((res) => {
          this.contents = res.data;
        })
        .catch((errors) => {
          console.log(errors);
        });
    },
  },
};
</script>

  <style lang="scss">
.op-wrapper.ovenplayer {
  width: 65% !important;
  left: 15% !important;
}

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

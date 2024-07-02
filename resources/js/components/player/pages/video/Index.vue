<template>
      <div class="browse-container content-page">
            <div class="icon-header">
                  <div class="cover">
                        <v-icon dark>$vuetify.icons.compass</v-icon>
                  </div>
                  <div class="title">
                        <div class="headline">Video</div>
                        <div class="sub">Explore and listen to various genres & moods.</div>
                  </div>
            </div>

            <div class="songs">
                  <!-- Player -->
                  <div id="video_player"></div>

                  <div class="grid-layout">
                        <div class="item" v-for="video in videos" :key="video.id">
                              <div class="content-item" v-if="video">
                                    <div class="content-item__header">
                                          <slot name="control-layer"></slot>
                                          <div class="control-layer">
                                                <div class="buttons">
                                                      <!-- Play/Pause Music -->
                                                      <button class="button button-play mx-3" @click="playVideo(video)">
                                                            <v-icon size="55" dark>$vuetify.icons.play-circle</v-icon>
                                                      </button>

                                                      <div class="button button-dots">
                                                            <video-menu :item="video" :dark="true" :closeOnContentClick="true"
                                                                  @close="$store.commit('setVideoMenu', null)"></video-menu>
                                                      </div>
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
                                                <div class="badges-layer">
                                                      <div class="badges">
                                                            <div class="premium" :title="$t('Premium')"
                                                                  v-if="video.isProduct">
                                                                  <v-icon color="#FFA500">$vuetify.icons.crown</v-icon>
                                                            </div>
                                                            <div class="explicit" :title="$t('Explicit')"
                                                                  v-if="video.isExplicit">
                                                                  <div class="explicit__sign">E</div>
                                                            </div>
                                                            <div class="exclusive" :title="$t('Exclusive')"
                                                                  v-if="video.isExclusive">
                                                                  <v-btn x-small dense depressed color="primary">{{
                                                                        $t("Exclusive")
                                                                  }}</v-btn>
                                                            </div>
                                                      </div>
                                                </div>
                                          </div>
                                    </div>
                                    <div class="content-item__body">
                                          <router-link class="content-item__body__title__container"
                                                :to="{ name: 'video', params: { id: video.id } }">
                                                <div class="content-item__body__type">
                                                      {{ $t("Video") }}
                                                </div>
                                                <div class="content-item__body__title max-2-lines">
                                                      {{ video.title }}
                                                </div>
                                          </router-link>
                                          <div class="artist max-1-lines">
                                                <artists :artists="video.artists"></artists>
                                          </div>
                                    </div>
                              </div>
                        </div>
                  </div>

                  <div class="empty" v-if="videos && !videos.length">
                        <empty-page :headline="$t('No Content!')"
                              :sub="$t('Looks like there is no content yet for this genre.')" img="peep-68.png" />
                  </div>
            </div>
      </div>
</template>
<script>
import OvenPlayer from "ovenplayer";
import Vue from "vue";
import VideoPlayer from './Player.vue'

export default {
      data() {
            return {
                  hls: null,
                  videos: null,
                  genre: null,
                  errorStatus: null,
                  selected_video: {
                        custom_source: "",
                  },
                  youtubePlayer: null,
                  videoPlayer: null,
                  currentVideo: {
                        source: null,
                        source_format: null,
                        index: 0,
                        title: "",
                        album: "",
                        artist: "",
                        cover: "",
                        progress: 0,
                        duration: "-:--",
                        currentTime: "0:00",
                        videoCurrentTime: 0,
                        isPlaying: false,
                  },
            };
      },
      components: {
            VideoPlayer
      },
      computed: {},
      async created() {
            this.fetchVideos();
      },
      methods: {
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
                        let options = {
                              title: video.title,
                              image: video.cover,
                              file: video.source,
                              autoStart: true,
                              timecode: true,
                        }

                        let video_player = OvenPlayer.create("video_player", options);
                        video_player.on('time', (data) => {
                          if (data.position >= 65) {
                            this.createPlayCount(video);
                          }
                        });

                  }
            },

            createPlayCount(video) {
                  this.$store.dispatch("registerPlayAndRoyaltyCount", {
                        id: video.id,
                        type: "video",
                        label: video.title,
                        duration: 0,
                        origin: 'file',
                        artist_id: video.artist
                  });
            },
            fetchVideos() {
                  axios.get("/api/videos").then((res) => {
                        this.videos = res.data;
                  });
            },
      },
};
</script>

<style>
.op-wrapper.ovenplayer {
      width: 65% !important;
      left: 15% !important;
}
</style>

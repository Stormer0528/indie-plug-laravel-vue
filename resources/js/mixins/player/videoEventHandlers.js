export default {
    methods: {
        initEventHandlers() {
            // this.videoPlayer.onLoad = this.onLoad;
            // this.videoPlayer.onStateChange = this.onStateChange;
            // this.videoPlayer.onEnded = this.onEnded;

            // this.videoPlayer.onloadstart = this.onloadstart;
            // this.videoPlayer.onerror = this.onerror;
            // this.videoPlayer.onloadedmetadata = this.onloadmetadata;
            // this.videoPlayer.oncanplay = this.oncanplay;
            // this.videoPlayer.onwaiting = this.onwaiting;
            // this.videoPlayer.oncanplaythrough = this.oncanplay;

            this.is_register_play_count = false;
            this.videoPlayer.onloadstart = this.onloadstart;
            this.videoPlayer.ontimeupdate = this.ontimeupdate;
            this.videoPlayer.onended = this.onended;
        },

        onloadstart() {
            this.is_register_play_count = false;
            this.isLoading = true;
            console.log("on load start")
        },
        /*onerror() {
            if (!this.isRadioStation) {
                // this.failedToPlay();
            }
        },
        onwaiting() {
            this.isLoading = true;
        },
        onloadmetadata() {
            // this.currentAudio.currentTime = "0:00";
            this.currentAudio.duration = this.videoPlayer.duration
        },
        oncanplay() {
            this.canPlay = true;
            this.isLoading = false;
        },*/
        ontimeupdate() {
            // this.currentAudio.isPlaying = true
            // this.updateTime(
            //     this.videoPlayer.currentTime,
            //     this.videoPlayer.duration
            // );
            // console.log("on time currentTime", this.videoPlayer.currentTime);
            this.onapiCallRegisterPlayCount();
        },
        onapiCallRegisterPlayCount(){
            if(this.videoPlayer.currentTime >= 59 && this.videoPlayer.currentTime <= 60 && !this.is_register_play_count)
            {
                if (this.selected_video.source_format === "yt_video") {
                    this.$store.dispatch("registerPlayAndRoyaltyCount", {
                        id: this.selected_video.id,
                        type: this.isPodcastEpisode ? "episode" : "video",
                        label: this.selected_video.title,
                        duration: this.selected_video.duration,
                        origin: 'youtube',
                        artist_id: this.selected_video.artist ? this.selected_video.artist.id : ''
                    });
                } else if (this.selected_video.source_format === "file") {
                    this.$store.dispatch("registerPlayAndRoyaltyCount", {
                        id: this.selected_video.id,
                        type: this.isPodcastEpisode ? "episode" : "video",
                        label: this.selected_video.title,
                        duration: this.selected_video.duration,
                        origin: this.selected_video.origin,
                        artist_id: this.selected_video.artist ? this.selected_video.artist.id : ''
                    });
                } else if (this.selected_video.source_format === "video_url") {
                    this.$store.dispatch("registerPlayAndRoyaltyCount", {
                        id: this.selected_video.id,
                        type: this.isPodcastEpisode ? "episode" : "video",
                        label: this.selected_video.title,
                        duration: this.selected_video.duration,
                        origin: this.selected_video.origin,
                        artist_id: this.selected_video.artist ? this.selected_video.artist.id : ''
                    });
                }

                this.is_register_play_count = true;
            }
        },
        onended() {
            /*alert("Ended Data");
            var index;
            this.canPlay = false;
            this.tracks_played++;
            this.$store.commit("setCurrentlyPlayingTypeStatus", "pause");
            this.$store.commit("setPlayerStatus", null);
            this.currentAudio.isPlaying = false;*/
            // if (this.$store.getters.getUser && this.$store.getters.getUser.id) {
            //     alert("End Data");
            //     this.$store.dispatch("endPlay");
            // }
            /*if (!this.buttons.loop) {
                if (this.buttons.shuffle && this.playlist.length > 1) {
                    index = this.getRandomAudio(
                        this.playlist.length,
                        this.currentAudio.index
                    );
                    this.updateCurrentAudio(index);
                } else {
                    if (this.playlist.length !== this.currentAudio.index + 1) {
                        index = this.currentAudio.index + 1;
                    } else {
                        index = 0;
                    }
                    this.updateCurrentAudio(index);
                }
            } else {
                if (this.isCurrentAudioAFileAudio) {
                    index = this.currentAudio.index;
                    this.updateCurrentAudio(index, true);
                } else if (this.currentAudio.source_format === "yt_video") {
                    this.videoPlayer.seekTo(0);
                    this.videoPlayer.playVideo();
                }
            }*/
        }
    }
}

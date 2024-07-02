export default {
    methods: {
        initEventHandlers() {
            this.audioPlayer.onloadstart = this.onloadstart;
            this.audioPlayer.onerror = this.onerror;
            this.audioPlayer.onloadedmetadata = this.onloadmetadata;
            this.audioPlayer.oncanplay = this.oncanplay;
            this.audioPlayer.ontimeupdate = this.ontimeupdate;
            this.audioPlayer.onended = this.onended;
            this.audioPlayer.onwaiting = this.onwaiting;
            this.audioPlayer.oncanplaythrough = this.oncanplay;
            this.is_register_play_count = false;
        },
        onloadstart() {
            this.is_register_play_count = false;
            this.isLoading = true;
        },
        onerror() {
            if (!this.isRadioStation) {
                this.failedToPlay();
            }
        },
        onwaiting() {
            this.isLoading = true;
        },
        onloadmetadata() {
            // this.currentAudio.currentTime = "0:00";
            this.currentAudio.duration = this.audioPlayer.duration
        },
        oncanplay() {
            this.canPlay = true;
            this.isLoading = false;
        },
        ontimeupdate() {
            // this.currentAudio.isPlaying = true
            this.updateTime(
                this.audioPlayer.currentTime,
                this.audioPlayer.duration
            );
            this.onapiCallRegisterPlayCount();
        },
        onapiCallRegisterPlayCount(){
            if(this.audioPlayer.currentTime >= 59 && !this.is_register_play_count)
            {
                if (this.currentAudio.source_format === "yt_video") {
                    this.$store.dispatch("registerPlayAndRoyaltyCount", {
                        id: this.currentAudio.id,
                        type: this.isPodcastEpisode ? "episode" : "song",
                        label: this.currentAudio.title,
                        duration: this.currentAudio.duration,
                        origin: 'youtube',
                        artist_id: this.currentAudio.artist ? this.currentAudio.artist.id : ''
                    });
                } else if (this.currentAudio.source_format === "file") {
                    this.$store.dispatch("registerPlayAndRoyaltyCount", {
                        id: this.currentAudio.id,
                        type: this.isPodcastEpisode ? "episode" : "song",
                        label: this.currentAudio.title,
                        duration: this.currentAudio.duration,
                        origin: this.currentAudio.origin,
                        artist_id: this.currentAudio.artist ? this.currentAudio.artist.id : ''
                    });
                } else {
                    this.$store.dispatch("registerPlayAndRoyaltyCount", {
                        id: this.currentAudio.id,
                        type: "radio-sation",
                        label: this.currentAudio.title
                    });
                }

                this.is_register_play_count = true;
            }
        },
        onended() {
            var index;
            this.canPlay = false;
            this.tracks_played++;
            this.$store.commit("setCurrentlyPlayingTypeStatus", "pause");
            this.$store.commit("setPlayerStatus", null);
            this.currentAudio.isPlaying = false;
            /*if (this.$store.getters.getUser && this.$store.getters.getUser.id) {
                this.$store.dispatch("endPlay");
            }*/
            if (!this.buttons.loop) {
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
            }
        }
    }
}

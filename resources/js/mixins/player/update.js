import axios from "axios";

export default {
    methods: {
        async updateCurrentAudio(index, force) {
            this.isLoading = true;
            this.reset();

            if (
                this.$store.getters.isCampaignAdEnabled &&
                this.campaigns.length &&
                this.tracks_played > 0 &&
                !this.$store.getters.getAdPlayed
            ) {

                const campaignsToPlay = this.campaigns.filter( campaign => (campaign.playAfterXSongs == 1 || (this.tracks_played % campaign.playAfterXSongs) == 0)).splice(0,1);
                if (
                    campaignsToPlay.length
                ) {
                    return this.playCampaign(campaignsToPlay, 0, index, force);
                }
            }

            await this.prepare(index);

            if (
                this.$store.getters.getSettings.autoPlay &&
                this.$store.getters.getQueue.length < 2
            ) {
                this.fetchSimilarities(index);
            }

            if (this.$store.getters.getUser) {
                this.updateUserPlay(index);
            }

            if (!this.isCurrentAudioAStream) {
                // await this.afterPrepareForNonStream(index);
            } else {
                this.afterPrepareForStream(index);
            }
            if (this.isCurrentAudioAYoutubeVideo) {
                this.playYoutubeVideo(index, force);
            } else if (this.isCurrentAudioAFileAudio) {
                this.playAudioFile(index, force);
            } else if (this.isCurrentAudioAStream) {
                this.playStream(index, force);
            }
        },
        playCampaign(campaigns, campaignIndex, trackIndex, force) {
            // if (this.campaigns[this.currentCampaignIndex]) {
                // this.switchToCampaignChannel();
                this.$store.commit(
                    "setCurrentCampaign",
                    campaigns[campaignIndex]
                );

                const isThereANextCampagin = campaignIndex !== campaigns.length - 1;
                const nextCampaignIndex = campaignIndex + 1;

                const currentCampaign = this.$store.getters.getCurrentCampaign;
                this.audioPlayer.src = currentCampaign.file

                if( !isThereANextCampagin ) {
                    this.$store.commit("setAdPlayed", true);
                }
                // else {
                //     var audio = new Audio();
                //     audio.src = campaigns[nextCampaignIndex].file
                //     audio.load()
                // }

                this.$set(
                    this.currentAudio,
                    "title",
                    currentCampaign.name
                );


                if (currentCampaign.banner && currentCampaign.banner.length) {
                    this.$set(
                        this.currentAudio,
                        "cover",
                        this.$store.getters.getCurrentCampaign.banner
                    );
                } else {
                    this.$set(
                        this.currentAudio,
                        "cover",
                        ""
                    );
                }

                this.$set(this.currentAudio, "campaign", true);

                this.audioPlayer.play();

                this.audioPlayer.onended = function() {
                    this.$store.commit("setCurrentCampaign", null);

                    // Mark the campaign as played and increment the play count
                    this.$store.dispatch("registerPlayAndRoyaltyCount", {
                        id: currentCampaign.id,
                        type: "campaign",
                    });

                    // move to the next campaign
                    if( isThereANextCampagin ) {
                        this.prepareCampaign( nextCampaignIndex );
                        this.playCampaign(campaigns, nextCampaignIndex, trackIndex, force)
                    } else {
                        // this.switchToMusicChannel();
                        this.updateCurrentAudio(trackIndex, force);
                    }
                }.bind(this);
            // }
            // else {
            //     this.$store.commit("setAdPlayed", true);
            //     this.updateCampaignIndex(0);
            //     this.updateCurrentAudio(trackIndex, force);
            // }
        },
        updateUserPlay(index) {
            if( this.isCurrentAudioAStream ) {
                var obj = {
                    playlist: null,
                    index: null,
                    genre_id: null,
                    radio: JSON.stringify(this.currentAudio)
                }
            } else {
               var obj = {
                playlist: JSON.stringify(this.playlist.map(track => track.id)),
                index: index,
                genre_id:
                    this.$store.getters.getCurrentlyPlayingType &&
                    this.$store.getters.getCurrentlyPlayingType.type ===
                        "genre" &&
                    this.$store.getters.getCurrentlyPlayingType.id
                        ? this.$store.getters.getCurrentlyPlayingType.id
                        : null
            }
            }
            axios.post("/api/user/playing", obj);
        },
        prepareCampaign(index) {
            // this.channels[2].player.src = this.campaigns[index].file;
            // this.channels[2].player.load();
        },
        updateCampaignIndex(index) {
            this.currentCampaignIndex = index;
        },
        reset() {
            clearInterval(this.timeUpdater);
            clearInterval(this.metadataFetcher);
            this.initCurrentAudioObj();
            this.resetBasicValues()
            this.resetAudioElement();
            this.resetTime();

            this.killStreamIfExists();
            // this.initCurrentAudioObj();

            // stop the video if it is mounted
            if (this.videoPlayer && this.videoPlayer.stopVideo) {
                this.videoPlayer.stopVideo();
            }
        },
        playAudioFile(index, force) {
            this.updateAudioElement(this.currentAudio.hls? true: false, this.currentAudio.source)
            if (
                this.currentAudio.index !== 0 ||
                force ||
                this.buttons.shuffle
            ) {
                this.playPause();
            }
        },
        // async afterPrepareForNonStream(index) {
        //     var audio = this.playlist[index];
        //     if( !audio.source ) {
        //         await this.getYoutubeVideoIfExists(audio.title);
        //         return;
        //     }
        //     return
        // },
        async prepare(index) {
            var audio = this.playlist[index];
            this.currentAudio.index = index

            // updating the currentAudio with the new audio values
            Object.keys(audio).forEach(key => {
                this.$set(this.currentAudio, key, audio[key])
            });

            if (audio.type === "podcast" && audio.origin === "listenNotes") {
                let res = await axios.get("/api/podcast/" + audio.id);
                audio = res.data;
            } else
            if( !this.isCurrentAudioAStream && !this.currentAudio.source ) {
                const title = this.currentAudio.title;
                const artist = this.currentAudio.artists.length  ? this.currentAudio.artists[0].name ? this.currentAudio.artists[0].name : this.currentAudio.artists[0].displayname : '';
                this.currentAudio.source = await this.getYoutubeVideoIfExists(title, artist);
            } else if( this.isCurrentAudioAStream ) {
                this.currentAudio.source = this.currentAudio.streamEndpoint
            }
        },
        initCurrentAudioObj() {
            this.currentAudio = {
                src: null,
                title: "",
                album: "",
                artist: "",
                progress: 0,
                duration: "-:--",
                currentTime: "0:00",
                streamEndpoint: null,
                videoCurrentTime: 0,
                isPlaying: false
            };
        },
        updateAudioElement(hls, source) {
            this.canPlay = false
            this.audioPlayer = document.getElementById("audio-player");


            this.initEventHandlers()
            return new Promise((res, rej) => {
                if( hls && Hls.isSupported()) {
                    this.initHLS(source + '?hls=true');
                    this.hls.on(Hls.Events.MANIFEST_PARSED, function (event, data) {
                        this.canPlay = true
                        res()
                      }.bind(this));
                } else {
                    this.detachHLS().then(()=> {
                        this.audioPlayer.crossOrigin = "anonymous";
                        this.audioPlayer.src = source;
                        this.audioPlayer.load();
                        console.log(this.audioPlayer.crossOrigin);
                        console.log(source);
                        res()
                    }) ;
                }
            })
            // this.audioPlayer.crossOrigin = "anonymous";
            // this.audioPlayer.src = source;

            // this.hls.attachMedia(this.audioPlayer)
        },
        resetAudioElement() {
            this.audioPlayer.pause();
        },
        resetBasicValues() {
            // resetting basic keys
            this.currentAudio.isPlaying = false
            this.currentAudio.duration = "-:--"
            this.currentAudio.source_format = null;
            this.currentAudio.file_name = null;
            this.currentAudio.source = null;
            this.currentAudio.progress = 0;
            this.currentAudio.currentTime = "0:00";
        },
        updateTime(currentTime, duration) {
            // updating the bar progress
            if( !this.isCurrentAudioAStream ) {
                if (this.audioPlayer.duration === Infinity && !this.isCurrentAudioAYoutubeVideo) {
                    this.currentAudio.duration = "live";
                    this.currentAudio.progress = 100;
                } else  if (currentTime < duration || currentTime == 0) {
                    this.currentAudio.progress = (currentTime / duration) * 100;
                } else {
                    this.currentAudio.progress = 0;
                }

                if( !isNaN(currentTime) && !isNaN(duration) && duration - currentTime <= 5) {
                    this.audioStatus = "ending"
                } else
                if( !isNaN(currentTime) && !isNaN(duration) && currentTime >= 0 && currentTime <=5) {
                    this.audioStatus = "starting"
                } else {
                    this.audioStatus = null
                }

                if( this.currentAudio.isProduct &&  this.currentAudio.playSample && !this.isOwned) {

                    // show purchase dialog after sample
                    if (
                        parseInt(this.audioPlayer.currentTime) > this.currentAudio.sampleSeconds
                    ) {
                        this.audioPlayer.pause();
                        this.$store.commit("updateQueue", []);
                        this.$store.commit("setCurrentlyPlayingType", {
                            type: "",
                            id: null
                        });
                        this.$store.commit("purchase/setSellingAsset", this.currentAudio);
                    }
                }

                                    // crossfade
                                    if (
                                        !isNaN(currentTime) &&
                                        !isNaN(duration) &&
                                        duration - currentTime <= this.currentAudio.sampleSeconds
                                    ) {
                                        this.audioStatus = "ending";
                                    } else if (
                                        !isNaN(currentTime) &&
                                        !isNaN(duration) &&
                                        currentTime >= 0 &&
                                        currentTime <= this.currentAudio.sampleSeconds
                                    ) {
                                        this.audioStatus = "starting";
                                    } else {
                                        this.audioStatus = null;
                                    }

            }
            // updating the current time "xx:yy"
            this.$set(this.currentAudio, 'currentTime', this.getTimeFormat(
                currentTime.toFixed(0)
            ))
        },
        easeVolumeRaise() {
            const initVolume = this.volume;
            const dropDownVolume = setInterval(() => {
                this.volume = this.volume + (this.volume / 20)
                if(this.volume >= initVolume ) {
                    clearInterval(dropDownVolume)
                }
            }, 250);
        },
        easeVolumeDrop() {
            const initVolume = this.volume;
            this.volume = 0;
            const dropDownVolume = setInterval(() => {
                this.volume = this.volume - (this.volume / 20)
                if(this.volume <= initVolume ) {
                    clearInterval(dropDownVolume)
                }
            }, 250);
        },
        async getYoutubeVideoIfExists(title, artist)
        {
            try {
                let res = await axios.get('/api/track-source?title=' + title + '&artist=' + artist)
                return res.data
            } catch (error) {
                return null
            }
        },

        async fetchSimilarities(index) {
            var audio = this.playlist[index];

            axios.get('/api/next-songs?id='  + audio.id + "&origin=" + audio.origin )
            .then((res) => {
                if( res.data.length ) {
                    this.$store.dispatch('updateQueue', { content: res.data, reset: false });
                }
            })
        },

        initHLS(source) {
            if( this.hls ) {
                this.hls.destroy();
            }
            this.hls = new Hls();
            if( source ) {
                this.hls.loadSource(source)
            }
            this.hls.attachMedia(this.audioPlayer);
        },
        detachHLS() {
            return new Promise((res, rej) => {
                // if (Hls.isSupported() && this.hls && this.hls.media) {
                //     // this.hls.detachMedia();

                //     // this.hls.on(Hls.Events.MEDIA_DETACHED, function() {
                //     //     return res()

                //     // });
                // } else {
                    res();
                // }
            })

        }

    }
};

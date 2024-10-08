<template>
    <div class="content-item" v-if="song" :class="{ hovered: imIPlayingNow }">
        <div class="content-item__header" @click="playAble(song)">
            <slot name="control-layer"></slot>
            <div
                class="control-layer"
                :class="{
                    'force-appearence': $store.getters.getSongMenu === compid
                }"
                v-if="!admin"
            >
                <div class="buttons">
                    <div
                        class="button-svg-container button-heart"
                        v-if="isLiked"
                        @click.stop="like(song)"
                    >
                        <v-btn icon small>
                            <v-icon color="white">$vuetify.icons.heart</v-icon>
                        </v-btn>
                    </div>
                    <div
                        class="button-svg-container button-heart"
                        v-else
                        @click.stop="like(song)"
                    >
                        <v-btn icon small>
                            <v-icon color="white">$vuetify.icons.heart-outline</v-icon>
                        </v-btn>
                    </div>
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
                        @click.stop="playAble(song)"
                    >
                        <v-icon size="55" dark>$vuetify.icons.play-circle</v-icon>
                    </button>
                    <div
                        class="button button-dots"
                    >
                        <song-menu
                            :item="song"
                            :dark="true"
                            :closeOnContentClick="true"
                            @close="$store.commit('setSongMenu', null)"
                        ></song-menu>
                    </div>
                    <!-- vuetify icon + menu -->
                     <!-- <v-btn
                        icon
                        class="button button-dots"
                        @click.stop="$store.commit('setSongMenu', compid)"
                    >
                        <abs-menu
                            v-if="$store.getters.getSongMenu === compid"
                            :style="{ top: '24px', right: '-3em' }"
                        >
                            <song-menu
                                :song="song"
                                @close="$store.commit('setSongMenu', null)"
                            ></song-menu>
                        </abs-menu>
                        <v-icon color="white">$vuetify.icons.dots-horizontal</v-icon>
                    </v-btn> -->
                </div>
            </div>
            <div class="cover-layer">
                <v-img :src="song.cover" class="img" aspect-ratio="1">
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
                    <div class="premium" :title="$t('Premium')" v-if="song.isProduct">
                        <v-icon color="#FFA500">$vuetify.icons.crown</v-icon>
                    </div>
                    <div class="explicit" :title="$t('Explicit')" v-if="song.isExplicit">
                        <div class="explicit__sign">
                            E
                        </div>
                    </div>
                    <div class="exclusive" :title="$t('Exclusive')" v-if="song.isExclusive">
                        <v-btn x-small dense depressed color="primary">{{$t('Exclusive')}}</v-btn>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="content-item__body">
            <router-link
                class="content-item__body__title__container"
                :to="{ name: 'song', params: { id: song.id } }"
            >
                <div class="content-item__body__type">
                    {{$t('Song')}}
                </div>
                <div class="content-item__body__title max-2-lines">
                    {{ song.title }}
                </div>
            </router-link>
            <div class="artist max-1-lines">
                <artists :artists="song.artists"></artists>
            </div>
        </div>
    </div>
</template>
<script>
import Song from "../../dialogs/edit/Song.vue";
import Vue from "vue";
export default {
    components: { Song },
    props: ["song", "admin"],

    data() {
        return {
            showMenu: false,
            compid: Math.random() * Math.floor(500000)
        };
    },
    computed: {
        isLiked() {
            if (!this.admin) {
            return (this.$store.getters.getLikedSongs || []).some(
                x => x.id == this.song.id
            );
            }
        },
        isArtistFollowed() {
            if (!this.admin) {
                if (
                    this.$store.getters.getUser &&
                    this.$store.getters.getFollowedArtists
                ) {
                    var song = this.song;
                    return this.$store.getters.getFollowedArtists.some(
                        artist => artist.id === song.artist_id
                    );
                } else {
                    return false;
                }
            }
        },
        imIPlayingNow() {
            return this.isCurrentlyPlaying(this.song);
        }
    },
    methods: {
        like(song) {
            if (this.isLiked) {
                this.$store.dispatch("dislike", song);
            } else {
                this.$store.dispatch("like", song);
            }
        },
        addSongToPlaylist(song_id) {
            this.$store.commit("setAddSongToPlaylist", song_id);
        },
        async playAble(song) {
            if (!this.$store.getters.getUser && !this.$store.getters.isLogged) {
                await this.loginOrCancel();
            } if (this.$store.getters.getUser && this.$store.getters.isLogged && song.is_only_for_subscriber && this.$store.getters.getUser.plan.free && !this.$store.getters.getUser.is_admin) {
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
                this.play(song, true);
            }
        }
    }
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

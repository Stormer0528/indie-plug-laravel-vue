<template>
  <div class="podcast-genres-wrapper">
    <v-card outlined>
      <v-card-title>
        <v-icon color="primary" x-large>$vuetify.icons.folder-music</v-icon>
        <v-btn
          class="mx-2"
          dark
          small
          color="primary"
          @click="editGenre('new')"
        >
          <v-icon>$vuetify.icons.plus</v-icon> {{ $t("New") }}
        </v-btn>
        <v-text-field
            type='date'
            v-model="start_date"
            @input="fetchGenres()"
            placeholder="Start date">
        </v-text-field>
        <v-text-field
            v-model="end_date"
            @input="fetchGenres()"
            type='date'
            placeholder="End date">
        </v-text-field>
        <v-btn
            class="mx-2"
            dark
            small
            color="primary"
            @click="exportCSV()"
        >{{ $t("Export CSV") }}
        </v-btn>
        <v-spacer></v-spacer>
        <v-spacer></v-spacer>
        <div class="admin-search-bar">
          <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            :label="$t('Search')"
            single-line
            hide-details
          ></v-text-field>
        </div>
      </v-card-title>
      <v-data-table
        :no-data-text="$t('No data available')"
        :loading-text="$t('Fetching data') + '...'"
        :headers="headers"
        :items="genres"
        :items-per-page="25"
        :search="search"
        class="elevation-1"
      >
        <template v-slot:item.name="{ item }">
          <router-link
            :to="{
              name: 'podcasts.genre',
              params: { genre_slug: item.slug, genre_id: item.id },
            }"
            class="router-link"
            target="_blank"
          >
            {{ item.name }}
            <v-icon x-small class="mb-1 ml-1"
              >$vuetify.icons.open-in-new</v-icon
            >
          </router-link>
        </template>
        <template v-slot:item.cover="{ item }">
          <div class="img-container py-2">
            <v-img
              :src="item.cover"
              :alt="item.name"
              class="genre-cover user-avatar"
              width="150"
            ></v-img>
          </div>
        </template>
        <template v-slot:item.operations="{ item }">
          <v-btn @click="editGenre(item)" x-small fab dark color="info">
            <v-icon>$vuetify.icons.pencil</v-icon>
          </v-btn>
          <v-btn @click="deleteGenre(item.id)" x-small fab dark color="error">
            <v-icon>$vuetify.icons.delete</v-icon>
          </v-btn>
        </template>
        <template v-slot:item.created_at="{ item }">
          {{ moment(item.created_at).format("ll") }}
        </template>
      </v-data-table>
    </v-card>
    <v-dialog v-model="editDialog" max-width="950">
      <edit-podcast-genre-dialog
        v-if="editDialog"
        @updated="genreEdited"
        @close="editGenre(null)"
        :genre="editingGenre"
      />
    </v-dialog>
  </div>
</template>
<script>
import editPodcastGenreDialog from "../../dialogs/admin/edit/PodcastGenre";
export default {
  components: {
    editPodcastGenreDialog,
  },
  data() {
    return {
        start_date: null,
        end_date: null,
      genres: [],
      search: "",
      headers: [
        {
          text: this.$t("Cover"),
          align: "start",
          sortable: false,
          value: "cover",
        },
        { text: this.$t("Name"), value: "name" },
        { text: this.$t("Created At"), value: "created_at" },
        { text: this.$t("Operations"), value: "operations", align: "center" },
      ],
      editDialog: false,
      editingGenre: null,
    };
  },
  created() {
    this.fetchGenres();
  },
  methods: {
       getDateFilter(){
            var query_string = "?";
            if(this.start_date){
                query_string += '&start_date='+this.start_date;
            }
            if(this.end_date){
                query_string += '&end_date='+this.end_date;
            }
            if(this.search){
                query_string += '&search='+this.search;
            }
            return query_string;
        },
    fetchGenres() {
        var query_string = this.getDateFilter();
      axios.get("/api/admin/podcast-genres"+query_string).then((res) => {
        this.genres = res.data;
      });
    },
    deleteGenre(id) {
      this.$confirm({
        message: `${
          this.$t("Are you sure you wanna delete this") +
          " " +
          this.$t("Genre") +
          "?"
        }`,
        button: {
          no: this.$t("No"),
          yes: this.$t("Yes"),
        },
        /**
         * Callback Function
         * @param {Boolean} confirm
         */
        callback: (confirm) => {
          if (confirm) {
            axios
              .delete("/api/admin/podcast-genres/" + id)
              .then(() => {
                this.$notify({
                  group: "foo",
                  type: "success",
                  title: this.$t("Deleted"),
                  text: this.$t("Genre") + " " + this.$t("Deleted") + ".",
                });
                this.fetchGenres();
              })
              .catch();
          }
        },
      });
    },
    editGenre(genre) {
      if (!genre) {
        this.editDialog = false;
        this.editingGenre = null;
      } else if (genre === "new") {
        this.editingGenre = {
          new: true,
          name: "",
        };
        this.editDialog = true;
      } else {
        this.editingGenre = genre;
        this.editDialog = true;
      }
    },
    genreEdited() {
      this.editDialog = false;
      this.$notify({
        group: "foo",
        type: "success",
        title: this.$t("Saved"),
        text: this.$t("Genre") + " " + this.$t("Updated") + ".",
      });
      this.fetchGenres();
    },
    exportCSV() {
        var query_string = this.getDateFilter();
        var url = '/api/admin/podcast-genres-export'+query_string;
        axios({
            url: url,
            method: 'GET',
        }).then((response) => {
            var fileURL = URL.createObjectURL(new Blob([response.data], {
                type: 'text/csv'
            }));
            var fileLink = document.createElement('a');
            fileLink.href = fileURL;
            fileLink.setAttribute('download', 'PodcastGenre.csv');
            document.body.appendChild(fileLink);
            fileLink.click();

            this.$notify({
                group: "foo",
                type: "success",
                title: this.$t("Export"),
                text: this.$t("Podcast Genre data") + " " + this.$t("Exported") + "."
            });
        }).catch(() => {});
    },
  },
};
</script>

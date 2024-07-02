<template>
      <div id="video_player"></div>
</template>

<script>
export default {
      props: {
            source: {
                  type: Object,
                  required: true
            },
            autoStart: {
                  type: Boolean,
                  default: false,
            }
      },
      data() {
            return {
                  options: {}
            };
      },
      created() {

      },
      methods: {},


}
</script>

export default {
    methods: {
        toggleVideoPlayButton() {
            const video = this.$el.querySelector('.video-container video');

            // on mobile devices will detect fullscreen mode and prevent video from closing
            jQuery(window).on('fullscreenchange webkitfullscreenchange mozfullscreenchange', () => {
                const selectedVideo = jQuery(video).closest('.video-container');

                if (document.fullscreenElement) {
                    selectedVideo.css('display', 'block');
                } else {
                    selectedVideo.css('display', '');
                }
            });
        }
    }
};

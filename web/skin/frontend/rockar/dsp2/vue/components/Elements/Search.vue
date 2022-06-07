<template>
    <form @submit.prevent="search" class="search-input" v-if="type === 'mobile'">
        <div class="search-inp">
            <input type="text" name="search" v-model="searchString" :placeholder="'Search the website' | translate">
        </div>

        <div class="submit-inp">
            <input type="submit" :value="'Search' | translate">
        </div>
    </form>

    <div class="search" v-else>
        <div class="search-input" @mouseover="focusInput">
            <form @submit.prevent="search" :style="setWidth">
                <div class="search-wrap">
                    <input :style="setOpacity" type="text" @blur="showInput = false" v-model="searchString" :placeholder="'Search' | translate">
                    <button type="submit">S</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            type: {
                required: false,
                type: String,
                default() {
                    return 'desktop'
                }
            },

            searchUrl: {
                required: false,
                type: String,
                default() {
                    return 'http://www.mitsubishi-cars.co.uk/results.aspx?q=';
                }
            }
        },

        data() {
            return {
                showInput: false,
                searchString: null
            }
        },

        computed: {
            setWidth() {
                return {
                    width: this.showInput ? '200px' : '35px'
                }
            },

            setOpacity() {
                return {
                    opacity: this.showInput ? 1 : 0
                }
            }
        },

        methods: {
            focusInput() {
                if (!this.showInput) {
                    this.showInput = true;
                    this.$nextTick(() => {
                        jQuery('.header-right-bottom .search input').focus();
                    });
                }
            },

            search() {
                if (this.searchString.trim().length) {
                    window.location = `${this.searchUrl}${this.searchString}`;
                }
            }
        }
    });
</script>
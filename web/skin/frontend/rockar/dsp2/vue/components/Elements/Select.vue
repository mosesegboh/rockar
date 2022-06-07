<template>
    <div class="selectbox-wrapper">
        <span class="selectbox-label">{{ label | translate }}</span>
        <div class="selectbox" :data-id="id" :class="{ open: show, disabled: disabled, 'validation-error': !valid }">
            <div :class="{'selectbox-title': selectTitleClass, 'selectbox-select': true}"
                 @focus="isFocused = true"
                 @blur="isFocused = false"
                 tabindex="0"
                 @mousedown="toggle()"
            >
                {{ selectTitle | translate }}
                <div class="dropdown-caret"></div>
            </div>

            <ul class="selectbox-dropdown" v-show="show" :style="{ height: selectHeight }" v-el:select-box>
                <li v-if="title"
                    @click="select(false)"
                    @focus="" class="dropdown-item"
                    :class="{ selected: selectedValue === -1 }"
                    :style="{ height: itemHeight + 'px', lineHeight: itemHeight / 2 + 'px' }"
                    :data-value="title"
                >
                    {{ title | translate }}
                </li>
                <li v-for="option in options"
                    @click="select(option, $index)"
                    class="dropdown-item"
                    :class="{ selected: selectedValue === $index, highlighted: highlightIndex === $index }"
                    :style="{ height: itemHeight + 'px', lineHeight: itemHeight / 2 + 'px' }"
                    :data-value="option.title"
                >
                    {{ option.title | translate }}
                </li>
            </ul>

            <input type="hidden" v-model="selectedValue">
        </div>
    </div>
</template>

<script>
    import ps from 'perfect-scrollbar';
    import coreSelect from 'core/components/Elements/Select';

    export default coreSelect.extend({
        events: {
            'Select::updateSelected'(id) {
                this.selected = id;
            }
        },

        props: {
            label: {
                required: false,
                type: String,
                default: ''
            }
        },

        computed: {
            selectTitleClass() {
                if (this.selectTitle === this.title) {
                    return true;
                }
            },

            selectHeight() {
                return `${this.itemHeight * (
                    this.options.length < this.showItems
                        ? this.options.length + (this.title ? 1 : 0)
                        : this.showItems
                ) + 4}px`;
            }
        }
    });
</script>

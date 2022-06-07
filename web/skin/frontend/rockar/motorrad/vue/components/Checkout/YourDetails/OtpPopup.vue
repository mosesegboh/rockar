<template>
    <div class="modal-dialog">
        <div class="modal-content">
            <p class="modal-header otp-popup-header">
                {{ 'Welcome Back.' | translate }}
            </p>

            <p class="otp-notice">
                {{ 'As an existing client of BMW Financial Services, we can auto-fill part of your new' | translate }}
                {{ 'application for your convenience. We\'ve sent an OTP to ' | translate }}
                {{ formattedPhone }}
                {{ ' to verify that it\'s you.' | translate }}
            </p>

            <div class="otp-password">
                <input v-for="(index, char) in codeChars"
                       v-model="char"
                       :key="index"
                       track-by="$index"
                       type="text"
                       class="otp-password-input"
                       v-on:keyup="jumpToNextChar"
                       maxlength="1">
            </div>

            <div class="request-otp">
                <a href="javascript:void(0)" @click="requestNewOtp()">{{ 'Request a new OTP' | translate }}</a>
            </div>

            <template v-if="otpData.retryAttempts !== 0">
                <p class="error-message" v-if="otpData.failAttempt">
                    {{ 'The OTP you\'ve entered is invalid.' | translate }}
                    <template v-if="otpData.retryAttempts">
                        <span v-if="otpData.retryAttempts === 1">
                            {{ 'You have one remaining attempt.' | translate }}
                        </span>
                        <span v-else>
                            {{ 'You have' | translate }} {{ otpData.retryAttempts }} {{ 'remaining attempts.' | translate }}
                        </span>
                    </template>
                    {{ 'Please check your SMS carefully and try again.' | translate }}
                </p>
                <p class="error-message" v-if="otpData.failResponse">
                    {{ 'Failed to process your request. Please contact support for details.' | translate }}
                </p>
                <p class="error-message" v-if="countdown === 0">
                    {{ 'Your session has timed out. Please request a new OTP to continue or skip this step' | translate }}
                </p>
            </template>
            <template v-if="otpData.retryAttempts === 0 && creditAppData.totalAttempts">
                <p class="error-message">
                    {{ 'You\'ve entered an invalid OTP' | translate }} {{ creditAppData.totalAttempts }} {{ 'times.' | translate }}
                    {{ 'For you security, please request a new OTP and try again.' | translate }}
                </p>
            </template>

            <p class="success-message" v-if="otpData.refreshed">
                {{ 'A new OTP has been sent to' | translate }} {{ formattedPhone }}
            </p>

            <div class="buttons">
                <button type="button" @click="skip()"
                        name="skip"
                        class="button button-narrow button-grey">
                    {{ 'Skip' | translate }}
                </button>

                <button type="button" @click="submitOtp()"
                        :disabled="otpData.retryAttempts === 0 || countdown === 0"
                        name="otp_submit"
                        class="button button-narrow popup-continue">
                    {{ 'Continue' | translate }}
                </button>
            </div>

            <p class="timer">
                {{ 'Your OTP will expire in ' | translate }} {{ showCountdown }}
            </p>
        </div>


    </div>
</template>

<script>
    export default Vue.extend({
        props: {
            creditAppData: {
                required: true,
                type: Object
            },

            otpData: {
                required: true,
                type: [Boolean, Object]
            }
        },

        data() {
            return {
                codeChars: [],
                countdown: this.creditAppData.countdownExpires,
                timer: null
            };
        },

        computed: {
            formattedPhone() {
                if (this.otpData.response === false) {
                    return '';
                }

                return this.otpData.response.authentication.cellNoForOTP;
            },

            otpLength() {
                if (this.otpData.response === false) {
                    return 5;
                }

                return this.otpData.response.authentication.otpLength;
            },

            otpCode() {
                return this.codeChars.join('');
            },

            showCountdown() {
                const minutes = parseInt(this.countdown / 60);
                let seconds = this.countdown % 60;

                if (seconds < 10) {
                    seconds = `0${seconds}`;
                }

                return `${minutes}:${seconds}`;
            }
        },

        methods: {
            requestNewOtp() {
                this.initCodeChars();
                this.countdown = this.creditAppData.countdownExpires;
                this.$dispatch('CheckoutCreditAppAccordionGroup::RefreshOTP');
            },

            skip() {
                this.closeModal();
                this.$dispatch('CheckoutCreditAppAccordionGroup::Cancel');
            },

            submitOtp() {
                this.$dispatch('CheckoutCreditAppAccordionGroup::Submit', this.otpCode);
            },

            closeModal() {
                this.$parent.closePopup();
            },

            jumpToNextChar(e) {
                const value = parseInt(e.key);
                const target = e.target;

                if (Number.isInteger(value)) {
                    target.value = value;
                    if (target.value.length > 0) {
                        if (target.nextElementSibling) {
                            target.nextElementSibling.focus();
                        }
                    }
                }
            },

            initCodeChars() {
                this.codeChars.splice(0, this.codeChars.length);
                while (this.codeChars.length < this.otpLength) {
                    this.codeChars.push('');
                }
            }
        },

        ready() {
            this.initCodeChars();
        },

        watch: {
            'otpLength'() {
                this.initCodeChars();
            },

            countdown: {
                handler(value) {
                    clearTimeout(this.timer);

                    if (value > 0) {
                        this.timer = setTimeout(() => {
                            this.countdown--;
                        }, 1000);
                    }
                },
                immediate: true
            },

            '$parent.show'(result) {
                clearTimeout(this.timer);

                if (result) {
                    this.countdown = this.creditAppData.countdownExpires;
                }
            }
        },
    });
</script>

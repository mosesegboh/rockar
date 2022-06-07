<template>
    <div class="debit_details">
        <h2>Direct Debit Details</h2>
        <p>
            Please enter the details for the account that you wish the finance repayments to be made from.
        </p>

        <table>
            <tr>
                <td>Name of bank account:</td>
                <td>
                    <input type="text" name="bank_account" v-model="formData.bank_account">
                </td>
            </tr>

            <tr>
                <td>Sort code:</td>
                <td>
                    <input type="text" name="sort_code" v-model="formData.sort_code">
                </td>
            </tr>

            <tr>
                <td>Account number:</td>
                <td>
                    <input type="text" name="account_number" v-model="formData.account_number">
                </td>
            </tr>

            <tr>
                <td>Time with bank*</td>
                <td>
                    <select name="with_bank_year" v-model="formData.time_with_bank.year">
                        <option v-for="(index, year) in dates.years" :value="year">
                            {{ year }}
                        </option>
                    </select>

                    <select name="with_bank_month" v-model="formData.time_with_bank.month">
                        <option v-for="(index, month) in dates.months" :value="month">
                            {{ month }}
                        </option>
                    </select>
                </td>
            </tr>
        </table>
    </div>
</template>

<script>
    import VueValidator from 'vue-validator';
    Vue.use(VueValidator);

    export default Vue.extend({
        props: {
            dates: {
                required: true,
                type: Object
            }
        },

        data() {
            return {
                formData: {
                    bank_account: 'bank account',
                    sort_code: 'sort_code',
                    time_with_bank: {
                        year: 2015,
                        month: 1
                    }
                }
            }
        }
    });
</script>

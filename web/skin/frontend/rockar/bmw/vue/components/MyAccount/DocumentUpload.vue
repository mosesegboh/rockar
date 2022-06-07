<template>
    <div class="general-preloader" v-show="ajaxLoading">
        <div class="show-loading"></div>
    </div>
    <div class="documents-upload">
        <div class="row my-account-header">
            {{ "The required documents are based on the following selection criteria:" | translate }}
        </div>
        <div class="row">
            <div class="col-6 my-account-header">
                <div class="my-account-header">{{ "I am buying as:" | translate }}</div>
                <div class="row" v-for="cust in customerGroups">
                    <input
                        type="radio"
                        class="radio-checkbox small-business-check"
                        :id="`radio-cust-group-${cust.value}`"
                        :value="cust.value"
                        v-model="formData.customerGroup"
                        name="customer-group"
                    >
                    <label :for="`radio-cust-group-${cust.value}`"><span></span>{{ cust.title }}</label>
                </div>
                <input
                    type="hidden"
                    name="customer-group"
                    value="{{ formData.customerGroup }}"
                />
            </div>
            <div class="col-6">
                <div class="my-account-header">{{ "Please confirm your finance type:" | translate }}</div>
                <div class="row" v-for="finGroup in financeGroups">
                    <input
                        type="radio"
                        class="radio-checkbox small-business-check"
                        :id="`radio-fin-group-${finGroup.value}`"
                        :value="finGroup.value"
                        v-model="formData.financeGroup"
                        name="finance-group"
                    >
                    <label :for="`radio-fin-group-${finGroup.value}`"><span></span>{{ finGroup.title }}</label>
                </div>
                <input
                    type="hidden"
                    name="finance-group"
                    value="{{ formData.financeGroup }}"
                />
            </div>
        </div>
        <table class="table-responsive">
            <thead>
            <tr>
                <th colspan="3">{{ "Document name" | translate }}</th>
                <th>{{ "Date uploaded" | translate }}</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <template v-for="item in formData.documentFinanceType">
                <tr v-if="item.documentId">
                    <td colspan="3">
                        <a class="a-center"
                           :href="this.generateDownLoadLink(item.documentId)">
                            <div class="download-icon"></div>
                            {{ item.documentType | translate }}
                        </a>
                    </td>
                    <td aria-label="Date uploaded">{{ item.dateUploaded }}</td>
                    <td>
                        <a class="a-center"
                           @click="this.deleteDocument(item.documentId)">{{ 'Remove' | translate }}</a>
                    </td>
                    <td>
                        <div class="document-upload">
              <span class="button button-empty wide">
                <template v-if="item.allowMultipleUploads === '1'">{{ 'Upload' | translate }}</template>
                <template v-else>{{ 'Change' | translate }}</template>
                <span class="i-choose-file"></span>
                <input
                    type="file"
                    class="document"
                    @change="doDocumentUpload($event, item)"
                />
              </span>
                        </div>
                    </td>
                </tr>
                <tr v-else>
                    <td colspan="5">
                        {{ item.documentType | translate }}
                    </td>
                    <td>
                        <div class="document-upload">
              <span class="button button-empty wide">
                {{ "Upload" | translate }}
                <span class="i-choose-file"></span>
                <input
                    :id=`document-new-${item.documentTypeId}`
                    type="file"
                    class="document"
                    @change="doDocumentUpload($event, item)"
                />
              </span>
                        </div>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
    </div>
</template>

<script>
import appSelect from 'core/components/Elements/Select';
import yourDetailsHelpers from 'core/components/Checkout/YourDetails/helpers';

export default Vue.extend({
    name: 'app-document-upload',
    props: {
        customerGroups: {
            required: true,
            type: Array
        },
        financeGroups: {
            required: true,
            type: Array
        },
        editUrl: {
            required: true,
            type: String
        },
        saveUrl: {
            required: true,
            type: String
        },
        formKey: {
            required: true,
            type: String
        },
        documentLoadUrl: {
            required: true,
            type: String
        },
        documentDownloadUrl: {
            required: true,
            type: String
        },
        documentDeleteUrl: {
            required: true,
            type: String
        },
        confirmationMessage: {
            required: true,
            type: String
        },
        defaultSelection: {
            required: false,
            type: Object
        }
    },

    data() {
        return {
            formData: {
                documentFinanceType: null,
                selectedDocumentType: null,
                customerGroup: null,
                financeGroup: null,
                documentName: this.documentName,
                documentTitle: this.documentTitle,
                documentValue: null
            },
            ajaxLoading: true
        }
    },

    methods: {
        /**
        * Retrieve a list of documents, uploaded or not.
        */
        getDocuments() {
            if (this.formData.financeGroup && this.formData.customerGroup) {
                this.ajaxLoading = true;
                this.$http({
                    url: this.documentLoadUrl,
                    method: 'GET',
                    emulateJSON: true,
                    data: { financeGroup: this.formData.financeGroup, customerGroup: this.formData.customerGroup },
                }).then(this.submitSuccess, this.submitFail);
            }
        },

        /**
         * Load selected documents to the front end.
         * @param response, ajax response as returned by the server.
         */
        submitSuccess(response) {
            const toProcess = (response.data || response);

            if (Array.isArray(toProcess)) {
                Vue.set(this.formData, 'documentFinanceType', toProcess);
                this.ajaxLoading = false;
            } else {
                this.submitFail(toProcess);
            }
        },

        /**
         * Load selected documents to the front end.
         * @param response, ajax response as returned by the server.
         */
        submitFail(data) {
            console.error(data);
            this.ajaxLoading = false;
        },

        /**
         * remove the uploaded document that the user selected to delete.
         * @param identity, integer to be removed.
         */
        deleteDocument(identity) {
            if (identity && window.confirm(this.confirmationMessage)) {
                this.ajaxLoading = true;
                this.$http({
                    url: this.documentDeleteUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: { entity_id: identity, financeGroup: this.formData.financeGroup, customerGroup: this.formData.customerGroup }
                }
                ).then(this.submitSuccess, this.submitFail);
            }
        },

        /**
         * upload document to the server
         * @param eve - event information contains details about the files uploaded.
         * @param documentMetadata additional meta data about this document.
         */
        doDocumentUpload(eve, documentMetadata) {
            this.ajaxLoading = true;
            const formData = new FormData();
            for (let i = 0; i < eve.target.files.length; i++) {
                formData.append(`fileToUpload${i}`, eve.target.files[i]);
            }
            formData.append('form_key', this.formKey);
            formData.append('title', documentMetadata.documentType);
            formData.append('documentFinanceType', documentMetadata.financeType);
            formData.append('documentType', documentMetadata.documentTypeId);
            formData.append('financeGroup', this.formData.financeGroup);
            formData.append('customerGroup', this.formData.customerGroup);

            if (documentMetadata.allowMultipleUploads === '0' && documentMetadata.documentId) {
                formData.append('entity_id', documentMetadata.documentId);
            }

            this.$http.post(this.saveUrl,
                formData,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(this.submitSuccess, this.submitFail);
        },

        /**
         * generate the download link for the client to download and view said document.
         * @param link - integer that identifies this document.
         * @returns {string} - download link for the user to click.
         */
        generateDownLoadLink(link) {
            return this.documentDownloadUrl.replace('@entity_id@', link);
        },

        selectionChangedUpdateUI() {
            if (!this.defaultSelection) {
                this.getDocuments();
            }
        }
    },
    ready() {
        if (
            this.defaultSelection
            && this.defaultSelection.financeGroup
            && this.defaultSelection.customerType
            && this.defaultSelection.documents) {
            this.formData.financeGroup = this.defaultSelection.financeGroup.value;
            this.formData.customerGroup = this.defaultSelection.customerType.value;
            this.submitSuccess(this.defaultSelection.documents);
            this.$nextTick(
                () => {
                    this.defaultSelection = false;
                }
            );
        }
    },
    watch: {
        'formData.financeGroup'() {
            this.selectionChangedUpdateUI();
        },
        'formData.customerGroup'() {
            this.selectionChangedUpdateUI();
        }
    },
    mixins: [
        yourDetailsHelpers
    ],
    components: {
        appSelect
    }
});
</script>

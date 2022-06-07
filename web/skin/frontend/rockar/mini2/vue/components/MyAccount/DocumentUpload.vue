<script>
    import appDocumentUpload from 'dsp2/components/MyAccount/DocumentUpload.vue';

    export default appDocumentUpload.extend({
        computed: {
            requiredDocumentsText() {
                return 'The required documents to complete your MINI purchase are based on the following criteria:';
            }
        }
    });
</script>

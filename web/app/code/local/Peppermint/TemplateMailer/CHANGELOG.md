# Peppermint_TemplateMailer Change Log

0.1.0:
- Extended TemplateMailer module
- Overwrite send method to accept new parameters $filePath = null, $fileName = null, \$fileType = null
- Added attachment to the email

0.1.1
- added Observer for event checkout_submit_all_after to trigger new mail when oder is placed
- added new Sales Emails configuration option in backend (Order Offer to Purchase)
- added helper to generate OTP document and send mail to customer

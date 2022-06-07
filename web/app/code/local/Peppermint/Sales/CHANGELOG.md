# Peppermint_Sales Change Log

0.1.0:
- Extended Sales module
- Overwrite queueNewOrderEmail method to accept new parameter $mFest = false, $filePath = null, $fileName = null, $fileType = null

0.1.1
- Add migration to add new column into specif table rockar_order_additional
- trigger at tracking order in transit / arrived

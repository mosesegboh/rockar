Peppermint_FinancingOptions Change Log
=======================

0.1.0:
- api that calls DLM
- add sql changes

0.1.1:
- add new fields into DSP pages and hide not needed fields
- add Finance Products templates

0.1.2:
- Added new migration to add new required finance varibles for monthly price calcuation
- Added rewrite for rockar_financingoptions_calcuation_carfinder
- Added rewrite for adminhtml rockar_financingoptions observer
- Edited CalculationAbstract to add additional finance variables

0.1.3:
- Added rewrite for Model Calculation for Order, Pdp, Quote, Reorder, Wishlist

0.1.4:
- Moved migration file from sql to data folder for finance variables

0.1.5:
- updated payInFull, Leasing and finance variables calculation updates
- Replace "Part Exchange" labels with "Trade In" in `rockar_financing_options_variables` and `rockar_financing_options_pdp_variables` tables

0.1.6
- new slider added to system > finance products
- new data seeder for balloon slider in config (default values)
- new slider group added to finance group filters (Finance > Finance Group > Filters)
- config helper extended
- extended table `rockar_financing_options_group` with 2 new fields for balloon slider

0.1.7
- added payment options for finance products

0.1.8:
- Added max_balloon_percent column in finance data table
- Added max_balloon_percent column in rockar_orderamend
- Added instalment sale option for calculations

0.1.9:
- add unique index for rockar_financingoptions/variables variable field

0.2.0:
- add shortfall_applied and shortfall_support finance variables

0.2.1:
- Added interest_rate_calc column in finance options
- Updated interest_rate so now is taking in consideration the subvention amount
- add rate_subvention_amount finance variable

0.2.2:
- added 2 new fields balloon_slider_steps and balloon_default_step to fix an older migration 

0.2.3:
- add balloonPercentage to be transmited from FE to DSP finance formulas calculations

0.2.4:
- updated Initiation Fee and Monthly Service Fee variable name
- added logic in each fees calculation to display individual or corporate value depending on what customer selected in the checkout

# Peppermint_Localstores Change Log

0.1.0:
- Added new columns to rockar_localstores/address (legal_entity, registration_number, vat_number, email_address)
0.1.1
- Added new columns to rockar_localstores/stores (dealer_code, associated_brand, associated_vehicle_types, vehicle_types, brand_code, financial_services_provider_number, registered_company_name, branch, branch_name, branch_type, brand)
- Added new columns to rockar_localstores/address (postal_address_line_1, postal_address_line_2, postal_address_line_3, postal_address_city, postal_address_postal_code, postal_address_province, province_code, province_name)
0.1.3
- added table peppermint_localstores/distances and CRUD functionality when import dealers
0.1.4
- drop table peppermint_localstores/distances and recreated with a new ForeignKey for to_store_id

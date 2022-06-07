# Peppermint_Importer Change Log

0.1.0:
- Created Importer module

0.1.1:
- Implemented Queue functionality with RabbitMq

0.1.2:
- Implemented create/update lead time per product

0.1.3:
- Implemented vin_number as an option value
- Created another consumer importer model which reads from dealer's queue and performs CRUD operations

0.1.4:
- Refactor attribute import to work continously and support update on options

0.1.5:
- Created orders consumer which reads from orders's queue and performs operations

0.1.6:
- Created products delete worker that reads list from separate queue

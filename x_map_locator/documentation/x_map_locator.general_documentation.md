**X Map Locator**

Provides custom functionality, that integrated Google Maps and search places on map.
Provides two custom entities for Marker Types (`x_map_locator_marker_type`) and Doctors(`x_map_locator_doctor`).

**Steps for set up module:**
1. Enable module “x_map_locator“, submodules: “x_map_locator_paragraph“, “x_map_locator_sales_rep“.
2. Go to “en-us/admin/structure/types/manage/page/fields/node.page.field_sections”, select “Map locator“ and submit form.
3. Go to “en-us/admin/x/x-map-locator/setting“ and check default settings for Map Locator.
4. Go to “en-us/admin/x/x-map-locator/doctor/import” and import from csv all doctors (***)
5. Go to “en-us/admin/x/language_settings/en-us“ and in vertical tabs select “Sales rep“. Add to Request settings this data:
   - Url = ***
   - Default country code = US
   - BU Codes = 301

6. Go to some node’s edit page and add to “field_sections“ new paragraph with type “Map Locator“.
7. Go to node’s page and check how it looks on page.

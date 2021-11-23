## Technical documentation
* **src/Form/XMapLocatorDoctorDefaultForm.php** - Form handler for the Doctor type validate, add and edit forms.
* **src/Form/XMapLocatorAdvancedSearchForm.php** - Provides XMapLocatorAdvancedSearchForm form.
    * *getFormId()* - Provides get form id method.
    * *buildForm()* - Build XMapLocatorAdvancedSearchForm form.
    * *submitForm()* - Default submit XMapLocatorAdvancedSearchForm form method.
    * *ajaxSearchBuildForm()* - Ajax submit for default search on map.
* **src/Form/XMapLocatorDoctorDeleteForm.php** - Builds the form to delete an X Map Locator Doctor.
    * *getQuestion()* - Provides the getting delete question message.
    * *getCancelUrl()* - Provides the cancel url.
    * *getConfirmText()* - Provides the confirm delete text.
    * *submitForm()* - Provides the submit method to delete the entity.
* **src/Form/XMapLocatorMarkerTypeForm.php** - Form handler for the Marker Type add and edit forms.
    * *form()* - Marker types form build.
    * *save()* - Set marker type values to entity.
    * *exist()* - Helper function to check whether an Marker Type configuration entity exists.
    * *iconSavePermanent()* - Helper function to save permanent icons.
* **src/Form/XMapLocatorSettingForm.php** - Provides XMapLocatorSetting form.
    * *buildForm()* - Provides build form with all needed data for Google map.
    * *submitForm()* - Save values to settings.
    * *getMarkerTypesList()* - Provides Marker types list.
* **src/Form/XLocatorSearchForm.php** - Provides XLocatorSearchForm form.
    * *getFormId()* - Provides get form id method.
    * *buildForm()* - Build XLocatorSearchForm form.
    * *submitForm()* - Default submit XLocatorSearchForm form method.
    * *ajaxSearchBuildForm()* - Ajax submit for default search on map.
    * *getProximityDoctors()* - Get proximity doctors.
* **src/Form/XMapLocatorDoctorImportForm.php** - Provides an form for import doctor entyties from csv file
    * *getFormId()* - Provides get form id method.
    * *buildForm()* - Form constructor.
    * *getCancelUrl()* - Cancel form action.
    * *deleteHandler()* - Delete all XMapLocatorDoctor form handler.
    * *submitForm()* - Form import from CSV submission handler.
    * *processItems()* - Processor for batch operations.
    * *processCreateItem()* - Process create XMapLocatorDoctor single item.
    * *processDeleteItem()* - Process delete XMapLocatorDoctor single item.
    * *finished()* - Finished callback for the batch.
* **src/Form/XMapLocatorMarkerTypeDeleteForm.php** - Builds the form to delete an MarkerType.
    * *getQuestion()* - Provides the getting delete question message.
    * *getCancelUrl()* - Provides the cancel url.
    * *getConfirmText()* - Provides the confirm delete text.
    * *validateForm()* - Provides the validate form method.
    * *submitForm()* - Provides the submit method to delete the entity.
* **src/XMapLocatorDoctorAccessControlHandler.php** - Access controller for the XMapLocatorDoctor entity.
    * *checkAccess()* - Provides access check for doctors managing.
    * *checkCreateAccess()* - Provides access check for doctors creating.
* **src/XMapLocatorListBuilder.php** - Defines a class to build a listing of doctor entity.
    * *$languageManager* - Common interface for the language manager service.
    * *__construct()* - Constructs a new XMapLocatorListBuilder object.
    * *getDefaultOperations()* - Set current language in operation url.
* **src/Plugin/Block/XMapLocatorSearchBlock.php** - Provides a block with a X map Locator search.
    * *build()* - Provides the block build.
* **src/Entity/XMapLocatorDoctor.php** - Defines the XMapLocatorDoctor entity.
    * *baseFieldDefinitions()* - Define the field properties.
* **src/Entity/XMapLocatorMarkerType.php** - Defines the XMapLocatorMarkerType entity.
* **src/XMapLocatorMarkerTypeInterface.php** - Provides an interface defining the XMapLocatorMarkerType
  entity.
    * *getLabel()* - Get the label.
    * *getDescription()* - Get the description.
    * *getIconRealUrl()* - Get the real icon url.
    * *getInternalIconRealUrl()* - Get the internal real icon url.
    * *getIcon()* - Get the icon file id.
    * *getInternalIcon()* - Get the internal icon file id.
    * *getIconName()* - Get the icon name.
    * *getIconImage()* - Get the icon image tag.
    * *getInternalIconImage()* - Get the internal icon image tag.
    * *getIconForForm()* - Get the icon of the marker type for the form.
    * *getInternalIconForForm()* - Get the internal icon of the marker type for the form.
    * *getWeight()* - Get the weight of the marker type.
    * *getIconFileUrl()* - Get icon file url.
* **src/LocatorManagerInterface.php** - Provides an interface for LocatorManager service
    * *getLocationByAddress()* - Get doctor location by doctor entity address values
    * *getFullAddress()* - Get doctor full address by the addresses fields values
    * *getInternalPinImageUrl()* - Get internal pin icon image for different types of qualification
    * *getPinImageUrl()* - Get pin icon image for different types of qualification
    * *getRenderedMarkers()* - Provides rendered markers and infoWindows
* **src/LocatorManager.php** - Provides LocatorManager service.
    * *$configFactory* - The config factory service.
    * *$httpClient* - The http client service.
    * *$markerStorage* - The entity storage.
    * *$jsonSerialization* - The Json serializer.
    * *$renderer* - The Renderer.
    * *$loggerFactory* - The Loger factory.
    * *__construct()* - Constructs a new LocatorManager object.
* **src/Controller/XMapLocatorMarkerTypeListBuilder.php** - Provides a listing of XMapLocatorMarkerType.
    * *buildHeader()* - Provides build for header table of Markers.
    * *buildRow()* - Provides build for Marker types.
* **src/XMapLocatorDoctorInterface.php** - Provides an interface defining an X Map Locator Doctor
  entity.
    * *firstName()* - Get the doctor first name of the doctor.
    * *lastName()* - Get the doctor last name of the doctor.
    * *scheduleAppointment()* - Get the Schedule Appointment of the doctor.
    * *qualification()* - Get the qualification of the doctor.
    * *practice()* - Get the practice name of the doctor.
    * *website()* - Get the website of the doctor.
    * *address1()* - Get the first address of the doctor.
    * *address2()* - Get the second address of the doctor.
    * *city()* - Get the city of the doctor.
    * *area()* - Get the area of the doctor.
    * *country()* - Get the country of the doctor.
    * *phone()* - Get the phone of the doctor.
    * *email()* - Get the email of the doctor.
    * *getZipcode()* - Get the zipcode of the doctor.
    * *customerId()* - Get the customer id of the doctor.
    * *isFullData()* - Get the provider of the doctor.
    * *XMapLocatorAllowedValues()* - Get the allowed doctor qualification values.
* **x_map_locator.module**
    * *x_map_locator_theme()* - Define theme for map elements
    * *x_map_locator_library_info_build()* - Provides dinamic library for Google API.

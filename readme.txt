##1.4.6
Removed 'show more' for description.
It is included in the description now.

##1.4.5
Mobile vie added show more button

##1.4.4
Adjusted image in list 2 for mobile

##1.4.2
Added description to List 2
Added years to list 2

##1.4.1
Added new parameter to shortcode jack-weekly-list-views: hide_categories

[jack-weekly-list-views orgid=508809  filters="location-room,age,class-type" filters_names="Location,Age,Class Type" view="week" btn_list="LIST" btn_week="CALENDAR" hide_class_type_first_load="Option 1"
hide_categories="Brambleton Summer Camp,Summer Camp Themed"]

Added new shortcode to display 2 lists
[jack-2-list-views orgid=508809  filters="date,age,location-room" filters_names="Date,Age,Location" view="list2" btn_list="LIST 1" btn_list_2="LIST 2" show_categories="summer camp"]

Parameters show_categories="...,..." y hide_categories="...,..."
can be applied to both shortcodes:
[jack-weekly-list-views]
[jack-2-list-views]

Added setting images for categories (shortcode jack-2-list-views):
https://placehold.co/600x400?text=camp [camp]
https://placehold.co/600x400?text=Ashburn [Ashburn]
https://placehold.co/600x400?text=default


##1.3.8
It is done. Version 1.3.8.
You just add a new parameter: [ ... hide_class_type_first_load="Summer Camps"]

Be careful to put exactly the same option name that you have used in the 'Custom filter class-type'

That class-type will be hidden from the first load in the week view only.

<?php

function jackca_get_message_weekends()
{
	$message = JACKCA()->settings->get('jacka_weekends_message');
	$link = JACKCA()->settings->get('jackca_weekends_link');

	if (preg_match('#.+\{(.+)\}#', $message, $matches)) {
		$message = str_replace('{'.$matches[1].'}', '<a style="color:black; text-decoration: underline;" href="'.$link.'">'.$matches[1].'</a>', $message);
	}

	return '<div class="weekend-bdy-parties">'.$message.'</div>';
}

function jacka_get_classtype_filter_options()
{
	$message = JACKCA()->settings->get('jackca_filter_class_type');
	$lines = explode("\r\n", $message);

	$filter_options = [];

	$current_option = false;

	foreach($lines as $line) {
		$line = trim($line);
		if (preg_match('#\[(.+)\]#', $line, $matches))
		{
			// Save previous current option
			if ($current_option) {
				$filter_options[$current_option['label']] = $current_option;
			}
			// New current option
			$current_option = [
				'label' => $matches[1],
				'options' => []
			];
		} else {
			if ($current_option) {
				if (preg_match('#(.+):(.+)#', $line, $matches)) {
					$current_option['options'][] = [
						'field' => trim($matches[1]),
						'value' => trim($matches[2])
					];
				}

			}
		}
	}

	if ($current_option && count($current_option['options']) > 0) {
		$filter_options[$current_option['label']] = $current_option;
	}


	//ray($filter_options);
	/*
	[
	  [
	    "label" => "Option 1"
	    "options" => array:2 [
	      [
	        "field" => "category1"
	        "value" => "Summer Camp Apollo Beach"
	      ]
	      1[
	        "field" => "category1"
	        "value" => "Creative Junk Therapy Camps"
	      ]
	    ]
	  ],
	  [
	    "label" => "Option 2"
	    "options" => [
			[
		        "field" => "category2"
		        "value" => "Summer Camp Themed"
		    ]
		]
	  ]
	]
	*/
	return $filter_options;
}

function jacka_get_location_colors()
{
	$message = JACKCA()->settings->get('jackca_location_colors');
	$lines = explode("\r\n", $message);

	$list = [];

	foreach($lines as $line) {
		if (preg_match('#(.+):(.+)#', $line, $matches))
		{
			$location = trim($matches[1]);
			$color = trim($matches[2]);

			$list[$location] = [
				'location' => $location,
				'color' => $color
			];
		}
	}

	return $list;
}

function jacka_get_categories_at_the_bottom_of_the_list_view()
{
	$file = JACKCA_PLUGIN_DIR.'/bottom_list_categories.txt';

	$list = JACKCA()->settings->get('jacka_list_last_categories');

	if (empty($list) || strlen($list) < 10) {
		// Load text file content
		if (file_exists($file) && is_readable($file)) {
			$list = file_get_contents($file);
		} else {
			// Handle the case where the file doesn't exist or is not readable
			//error_log("Unable to read file: $file");
			//return [];
		}
	}
	//ray($list);

	// Process the list
	$result = [];

	// Dividir el contenido en líneas
	$lines = explode("\n", $list);

	// Procesar cada línea
	foreach ($lines as $line) {
		// Eliminar espacios en blanco al inicio y al final
		$line = trim($line);

		// Ignorar líneas vacías
		if (empty($line)) {
			continue;
		}

		// Verificar si la línea cumple con el patrón category:texto
		if (preg_match('/^(category\d+):(.+)$/', $line, $matches)) {
			$category = $matches[1];
			$value = trim($matches[2]);

			// Agregar el valor al array correspondiente
			if (!isset($result[$category])) {
				$result[$category] = [];
			}
			$result[$category][] = $value;
		}
	}

	return $result;
}

function jacka_get_list_categories_images()
{
	$images = JACKCA()->settings->get('jacka_images');

	$images = preg_split('/\r\n|[\r\n]/', $images);

	$list = [];
	foreach($images as $color_text)
	{
		if (preg_match('#(.+)\[(.+)\]#', $color_text, $matches))
		{
			$list[] = [
				'url' => trim($matches[1]),
				'words' => explode(',', $matches[2])
			];
		}
		else if (strlen($color_text) > 10)
		{
			$list[] = [
				'url' => $color_text,
				'words' => []
			];
		}
	}

	if (empty($list)) {
		return [
			['url' => 'https://placehold.co/600x400?text=-', 'words' => []]
		];
	}

	return $list;

	/*return [
		['url' => 'https://cod181795.test/wp-content/uploads/2023/07/kids-1.jpg', 'words' => ['world']],
		['url' => 'https://cod181795.test/wp-content/uploads/2023/07/kids-2.jpeg', 'words' => ['pie']],
		['url' => 'https://cod181795.test/wp-content/uploads/2023/07/kids-3.jpg', 'words' => ['cheese']],
	];*/
}

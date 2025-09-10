export default {

    data(){
        return {
            jackrabbit_url: window.JACKCA_CALENDAR.endpoint,
            orgid: window.JACKCA_CALENDAR.orgid,
            //images: window.JACKCA_CALENDAR.images,
            //colors: window.JACKCA_CALENDAR.colors,

            jackDataLoaded: false,
            rows: [],

            // Se admiten hasta 3 filtros
            filters: [],
            filter0_selected: null,
            filter1_selected: null,
            filter2_selected: null,

            classTypeViewMap: {},

            hideCategories: window.JACKCA_CALENDAR.hide_categories ?
                window.JACKCA_CALENDAR.hide_categories.split(',').map(cat => cat.trim()) : [],

            showCategories: window.JACKCA_CALENDAR.show_categories ?
                window.JACKCA_CALENDAR.show_categories.split(',').map(cat => cat.trim()) : [],
        }
    },

    methods: {
        getjacktabbitData(){
            let url = this.jackrabbit_url + '?orgid=' + this.orgid

            jQuery.getJSON(url, (response) => {
                if (response.success) {
                    //console.log(response.rows)
                    // Asignacion directa
                    //this.rows = response.rows
                    // Test a range of dates
                    //this.rows[0].start_date = '2023-09-13'
                    //this.rows[0].end_date = '2023-09-23'

                    // Filtrar las rows basándonos en hide_categories
                    let filteredRows = response.rows;

                    //console.log('Hide categories', this.hideCategories)
                    //console.log('showCategories', this.showCategories)
                    if (this.showCategories.length > 0) {
                        filteredRows = response.rows.filter(row => {
                            // Verificar si alguna de las categorías de la row coincide con las categorías vistas
                            return this.showCategories.some(showCat =>
                                (row.category1 && row.category1.toLowerCase().includes(showCat.toLowerCase())) ||
                                (row.category2 && row.category2.toLowerCase().includes(showCat.toLowerCase())) ||
                                (row.category3 && row.category3.toLowerCase().includes(showCat.toLowerCase()))
                            );
                        });
                    }

                    //console.log('hideCategories', this.hideCategories)
                    if (this.hideCategories.length > 0) {
                        filteredRows = response.rows.filter(row => {
                            // Verificar si alguna de las categorías de la row coincide con las categorías ocultas
                            return !this.hideCategories.some(hiddenCat =>
                                (row.category1 && row.category1.toLowerCase().includes(hiddenCat.toLowerCase())) ||
                                (row.category2 && row.category2.toLowerCase().includes(hiddenCat.toLowerCase())) ||
                                (row.category3 && row.category3.toLowerCase().includes(hiddenCat.toLowerCase()))
                            );
                        });
                    }

                    // Asignar classtype a cada row antes de guardar
                    this.rows = filteredRows.map(row => {
                        row.classtype = this.determineClassType(row);
                        return row;
                    });

                    this.jackDataLoaded = true
                    this.prepareFilters()
                    //this.extractMonths()
                }
            })
        },

        // Nueva función para determinar el classtype de una row
        determineClassType(row) {
            const filterClassType = window.JACKCA_CALENDAR.filter_classtype;

            for (const [classType, config] of Object.entries(filterClassType)) {
                // Extraer el nombre del classtype sin el viewtype
                const displayName = this.extractViewType(classType)[0];

                // Verificar si la row cumple con alguna de las opciones del classtype
                const matches = config.options.some(option =>
                    row[option.field] === option.value
                );

                if (matches) {
                    return displayName;
                }
            }
            return null;
        },


        isClassAvailableForDate(row, date) {

            let weekdays = {
                1: 'mon',
                2: 'tue',
                3: 'wed',
                4: 'thu',
                5: 'fri',
                6: 'sat',
                7: 'sun'
            }

            //console.log(row.name)
            /*if (row.name == 'Creative Drama- Menifee') {
                //console.log(row)
                console.log(date.dateFormat)
                console.log(date.weekday + ' => ' + weekdays[date.weekday])
                console.log(row.meeting_days[weekdays[date.weekday]])
            }*/

            let dateFormat = date.dateFormat

            // Start date
            if (row.start_date && row.start_date == dateFormat && row.meeting_days[weekdays[date.weekday]])
            {
                return true
            }

            // Entre las dos fechas
            if (row.start_date && row.end_date)
            {
                // Tiene que coincidir tb el weekday
                if (dateFormat >= row.start_date && dateFormat <= row.end_date && row.meeting_days[weekdays[date.weekday]])
                {
                   return true
                }
            }

            return false
        },

        isClassRecurring(row) {
            // Parseamos las fechas de inicio y fin
            const startDate = new Date(row.start_date);
            const endDate = new Date(row.end_date);

            // Calculamos la diferencia en milisegundos
            const diffTime = Math.abs(endDate - startDate);

            // Convertimos la diferencia a días
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            // Consideramos la clase recurrente si hay 98 días (14 semanas) o más entre las fechas
            return diffDays >= 98;
        },

        tConvert (time) {
            // Check correct time format and split into components
            time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

            if (time.length > 1) { // If time format correct
                time = time.slice (1);  // Remove full string match value
                time[5] = +time[0] < 12 ? 'AM' : 'PM'; // Set AM/PM
                time[0] = +time[0] % 12 || 12; // Adjust hours
            }
            return time.join (''); // return adjusted time or original string
        },

        convertDate(regdate) {
            var m = regdate.match(/(\d+)-(\d+)-(\d+)\s+(\d+):(\d+):(\d+)/);
            var date = new Date(m[1], m[2] - 1, m[3], m[4], m[5], m[6]);
            return date.toLocaleDateString("en-US");
        },

        formatTime(time) {
            return this.tConvert(time)
        },

        formatDate(date) {
            return this.convertDate(date + ' 00:00:00')
        },

        // MAYO 2024 - añadido para poder poner 3 filtros en vez de usar la categoria 1
        //----------------------------------------------------------------------------------
        prepareFilters()
        {
            this.filters = []

            const list_fields = window.JACKCA_CALENDAR.filters

            // Buscar cada termino y extraer los valores únicos
            for (let index = 0; index < list_fields.length; index++)
            {

                const term = list_fields[index]
                const name = window.JACKCA_CALENDAR.filters_names[index] || term.toUpperCase()

                const filter = this.getFilterData(term)

                if (filter) {
                    filter.name = name
                    this.filters.push(filter)
                }
            }

            console.log('Filters are ready', this.filters)
        },

        // Prepara el filtro para el dropdown
        getFilterData(term)
        {
            // Filtro especial para fecha
            if (term === 'date') {
                return {
                    name: 'Date',
                    date_filter: true,
                    field: 'date',
                    values: [],
                    values_display: [],
                    values_allowed: []
                };
            }

            const {list_values, list_values_display} = this.getListOfValuesForField(term)

            if (list_values.length > 0 || list_values_display.length > 0)
            {
                let field = term
                let location_filter = false
                let age_filter = false
                let classtype_filter = false

                const result = term.match(/(.+)-(.+)/)

                // FILTRO: age

                if ( field == 'age')
                {
                    age_filter = true
                }

                // FILTRO class-type
                else if (field == 'class-type')
                {
                    classtype_filter = true
                }

                // FILTRO location-(field)
                else if (result)
                {
                    field = result[2].trim()
                    location_filter = true
                }


                return {
                    name: term.toUpperCase(),

                    // Es un filtro especial ?
                    location_filter,
                    age_filter,
                    classtype_filter,

                    // Campo a buscar
                    field: field,

                    // Todos los valores posibles
                    values: list_values,
                    values_display: list_values_display,

                    // Listado de valores que se ajustan a los otros filtros seleccionados
                    values_allowed: JSON.parse(JSON.stringify(list_values_display))
                }
            }

            return null
        },

        // Lista de valores encontrados
        getListOfValuesForField(term, rows = null) {
            if (rows == null) {
                rows = this.rows
            }

            let field = term

            const list_values = []

            // Es del tipo: location-name
            // Name es el campo que tengo que extraer y el valor a tener en cuenta es location
            const result = term.match(/(.+)-(.+)/)

            // FILTRO: age
            if ( field == 'age')
            {
                /*for (let i = 0; i < rows.length; i++)
                {
                    if (rows[i]['min_age'] && rows[i]['max_age']) {
                        const value1 = rows[i]['min_age']
                        const value2 = rows[i]['max_age']

                        if (value1 && !list_values.includes(value1)) {
                            list_values.push(value1)
                        }
                        if (value2 && !list_values.includes(value2)) {
                            list_values.push(value2)
                        }
                    }
                }

                const list_values_sorted =  list_values.sort()
                const list_values_sorted_display = list_values.sort().map(value => {
                    return value.replace('P0','').replace('Y00M',' years').replace('P','')
                })

                console.log({
                    list_values: list_values_sorted,
                    list_values_display: list_values_sorted_display
                })

                return {
                    list_values: list_values_sorted,
                    list_values_display: list_values_sorted_display
                }*/

                let minAge = 18;  // Inicializamos con el máximo permitido
                let maxAge = 4;   // Inicializamos con el mínimo permitido

                // Encontrar la edad mínima y máxima en las filas
                for (let i = 0; i < rows.length; i++) {
                    if (rows[i]['min_age'] && rows[i]['max_age']) {
                        const currentMinAge = parseInt(rows[i]['min_age'].replace('P', '').replace('Y00M', ''));
                        const currentMaxAge = parseInt(rows[i]['max_age'].replace('P', '').replace('Y00M', ''));

                        minAge = Math.min(minAge, currentMinAge);
                        maxAge = Math.max(maxAge, currentMaxAge);
                    }
                }

                // Ajustar al rango de 4 a 18 años
                minAge = Math.max(4, minAge);
                maxAge = Math.min(18, maxAge);

                // Generar listas de edades
                for (let age = minAge; age <= maxAge; age++) {
                    // Formato P__Y00M para list_values
                    list_values.push(`P${age.toString().padStart(2, '0')}Y00M`);
                }


                const result = {
                    list_values: list_values,
                    list_values_display: list_values.map(value => `${parseInt(value.replace('P', '').replace('Y00M', ''))} years`),
                }

                //console.log(result)
                return result

            }

            // FILTRO class-type
            /*else if (field == 'class-type') {
                return {
                    list_values: window.JACKCA_CALENDAR.filter_classtype,
                    list_values_display: Object.keys(window.JACKCA_CALENDAR.filter_classtype)
                }
            }*/

            // FILTRO class-type
            else if (field == 'class-type') {
                const filterClassType = window.JACKCA_CALENDAR.filter_classtype;
                const list_values = [];
                const list_values_display = [];
                this.classTypeViewMap = {};

                for (const [key, value] of Object.entries(filterClassType)) {
                    const [displayName, viewType] = this.extractViewType(key);
                    list_values.push(key);
                    list_values_display.push(displayName);
                    if (viewType) {
                        this.classTypeViewMap[displayName] = viewType;
                    }
                }

                return {
                    list_values: list_values,
                    list_values_display: list_values_display
                }
            }

            else if (result)
            {
                field = result[2].trim() // Cojo el campo name
                for (let i = 0; i < rows.length; i++)
                {
                    const value = rows[i][field]
                    // Extraer la location como el primer termino
                    let extract_value = value.match(/(.+)-(.+)/)
                    if (extract_value) {
                        const real_value = extract_value[1].trim()
                        if (real_value && !list_values.includes(real_value)) {
                            list_values.push(real_value)
                        }
                    }
                }
            }

            // Es un campo normal: category1 ...
            else {
                for (let i = 0; i < rows.length; i++)
                {
                    if (rows[i][field]) {
                        const value = rows[i][field]
                        if (value && !list_values.includes(value)) {
                            list_values.push(value)
                        }
                    }
                }
            }


            list_values.sort()
            //console.log(list_values)
            return {
                list_values: list_values,
                list_values_display: list_values
            }
        },

        // extraer option 1:week , option 2:list
        extractViewType(key) {
            const parts = key.split(':');
            if (parts.length > 1) {
                return [parts[0].trim(), parts[1].trim()];
            }
            return [key, null];
        },

        // Filtrar que rows cumplen los 3 filtros
        getClassesFor(date) {
            console.log('>>>> getClassesFor ', date.dateFormat)
            let dateFormat = date.dateFormat

            let list = []

            for(let i = 0; i < this.rows.length; i++)
            {
                let row = this.rows[i]

                // Coincidir todos los filtros y la fecha
                if (this.meetsAllFilters(row) && this.isClassAvailableForDate(row, date))
                // Coincidir con todos los filtros
                //if (this.meetsAllFilters(row))
                {
                    list.push(row)
                }

            }

            // DEBUG
            /*for (let i = 0; i < list.length; i++){
                console.log(list[i].name)
            }*/

            list.sort((a, b) => {
                const timeA = a.start_time.split(':').map(Number);
                const timeB = b.start_time.split(':').map(Number);

                // Comparar horas
                if (timeA[0] !== timeB[0]) {
                    return timeA[0] - timeB[0];
                }
                // Si las horas son iguales, comparar minutos
                return timeA[1] - timeB[1];
            });

            // Hay que reordernar por start_time
            //console.log(list)

            return list
        },


        // Determina si una clase está activa en una fecha específica
        isClassActiveOnDate(row, selectedDate) {

            if (!selectedDate || !row.start_date || !row.end_date) return true;



            // Convertir las fechas a objetos Date para comparación
            const classStart = new Date(row.start_date);
            const classEnd = new Date(row.end_date);
            const filterDate = new Date(selectedDate);

            // Resetear las horas para comparar solo fechas
            classStart.setHours(0,0,0,0);
            classEnd.setHours(0,0,0,0);
            filterDate.setHours(0,0,0,0);

            // La clase está activa si la fecha seleccionada está entre start_date y end_date
            const result = filterDate >= classStart && filterDate <= classEnd;

            //console.log('isClassActiveOnDate: ' + selectedDate + ' : ' + row.start_date + ' - ' + row.end_date + ' = ' + result)
            return result
        },

        cumpleElFiltro(row, value_selected, filter)
        {
            if (filter.date_filter) {
                return this.isClassActiveOnDate(row, value_selected);
            }

            // Location filter
            if (filter.location_filter) {
                //console.log('Checking ' + filter.location_filter + ' = ' + value_selected + ' with ' + row[filter.field])

                if (row[filter.field].indexOf(value_selected) == -1 || row[filter.field].indexOf(value_selected) > 0) {
                    return false
                }
            }

            // Age filter
            else if (filter.age_filter) {
                // La edad tiene que estar entre dos valores usando min_age y max_age
                //console.log(value_selected)

                // Transformar el value -> numero
                const years = parseInt(value_selected.replace(' years', ''))

                // Transformar min_age y max_age
                let min_age = parseInt(row.min_age.replace('P0','').replace('Y00M','').replace('P',''))
                let max_age = parseInt(row.max_age.replace('P0','').replace('Y00M','').replace('P',''))

                //console.log(years + ' entre ' + min_age + '-' + max_age)
                if (years < min_age || years > max_age) return false
            }

            // Class-type filter
            /*else if (filter.classtype_filter) {

                //console.log('CLASSTYPE FILTER: ' + value_selected)
                //console.log(filter)

                // Tiene que coincidir con alguno de los campo:valor
                let found = false

                if (filter.values[value_selected])
                {
                    const options = filter.values[value_selected].options
                    // {field:category1, value:Summer camp}, {..}

                    //console.log(options)
                    for (let i = 0; i < options.length; i++)
                    {
                        const opt_key = options[i].field
                        const opt_value = options[i].value

                        if (row[opt_key] && row[opt_key] == opt_value) {
                            found = true
                        }
                    }
                }

                return found
            }*/

            // Class-type filter
            else if (filter.classtype_filter) {
                let found = false;
                const filterClassType = window.JACKCA_CALENDAR.filter_classtype;

                // Find the original key (with view type) that matches the selected value
                const originalKey = Object.keys(filterClassType).find(key => this.extractViewType(key)[0] === value_selected);

                if (originalKey && filterClassType[originalKey]) {
                    const options = filterClassType[originalKey].options;
                    for (let i = 0; i < options.length; i++) {
                        const opt_key = options[i].field;
                        const opt_value = options[i].value;
                        if (row[opt_key] && row[opt_key] == opt_value) {
                            found = true;
                            break;
                        }
                    }
                }
                return found;
            }

            // Cualquier otro campo
            else {
                //console.log(filter.field + ': ' + value_selected + '==' + row[filter.field])
                if (row[filter.field] != value_selected) return false
            }

            return true
        },

        // New method to handle view change
        handleClassTypeViewChange(selectedValue) {
            const viewType = this.classTypeViewMap[selectedValue];
            if (viewType) {
                this.view = viewType;
            }
        },

        // Cumple esta fila los tres filtros?
        meetsAllFilters(row)
        {
            if (this.filter0_selected && !this.cumpleElFiltro(row, this.filter0_selected, this.filters[0]))
            {
                return false;
            }

            if (this.filter1_selected && !this.cumpleElFiltro(row, this.filter1_selected, this.filters[1]))
            {
                return false;
            }

            if (this.filter2_selected && !this.cumpleElFiltro(row, this.filter2_selected, this.filters[2]))
            {
                return false;
            }

            return true
        },


        // Obtener todas las filas que cumplen con estos fields
        getRowsWitchTheseFields(fields = {}) {
            // fields = {category1: 'pepe', category2: ''}
            let rows = []
            for (let i = 0; i < this.rows.length; i++) {
                let found = true
                Object.keys(fields).forEach( key => {
                    if (fields[key] != null && this.rows[i][key] != fields[key]) {
                        found = false
                    }
                })
                if (found) {
                    rows.push(this.rows[i])
                }
            }
            return rows
        },


        // Register link

        hasRegisterLink(item) {
            return this.getRegisterLink(item) != false
        },

        getRegisterLink(item) {

            // Check if item has online_reg_link property
            if (item.online_reg_link) {
                return item.online_reg_link.replaceAll('&amp;', '&');
            }

            // If not, search for a link in the description
            if (item.description) {
                // Create a temporary DOM element to parse the HTML
                const tempElement = document.createElement('div');
                tempElement.innerHTML = item.description;

                // Find the first anchor tag with class "register-link"
                const registerLink = tempElement.querySelector('a.register-link');

                // If found, return its href attribute
                if (registerLink) {
                    return registerLink.getAttribute('href');
                }
            }

            // If no link is found, return false
            return false;
        },

    },

}

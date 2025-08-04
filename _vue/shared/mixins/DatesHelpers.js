export default {
    data(){
        return {
            currentYear: 2020,
            currentMonth: 1,
            //firstDayOfWeek: 1 // Could be a prop
        }
    },
    methods: {

        weekLabels(format = 'short', year = null) {
            if (year == null) {
                year = this.currentYear
            }
            let weekNumbers = this.firstDayOfWeek == 1 ? [1,2,3,4,5,6,7] : [7,1,2,3,4,5,6]

            let labels = []
            weekNumbers.forEach( n => {
                labels.push(DateTime.fromISO(year + '-W10-' + n).setLocale(this.locale).toLocaleString({weekday: format}))
            })
            return labels;
        },

        weekLabelsWithKeys(format = 'short', year = null) {
            if (year == null) {
                year = this.currentYear
            }
            let weekNumbers = this.firstDayOfWeek == 1 ? [1,2,3,4,5,6,7] : [7,1,2,3,4,5,6]

            let labels = {}
            weekNumbers.forEach( n => {
                let label = DateTime.fromISO(year + '-W10-' + n).setLocale(this.locale).toLocaleString({weekday: format})
                labels[n] = label
            })
            return labels;
        },

        weekLabel(day, format = 'long') {
            return  DateTime.fromISO(this.currentYear + '-W10-' + day.weekday).setLocale(this.locale).toLocaleString({weekday: format})
        },

        monthLabel(index, format = 'short') {
            index = this.pad(index, 2)
            return DateTime.fromISO('2020-' + index + '-01').setLocale(this.locale).toLocaleString({month: format})
        },


        //-------------------------------------------
        // Manage calendar days
        //-------------------------------------------

        getDaysMonth(year, month) {

            if (month < 1) {
                month = 1
            }
            if (month > 12) {
                month = 12
            }

            let count = this.daysInMonth(year, month);

            var days = [];

            var firstWeekNumber = 0
            var lastWeekNumber = 0

            for (let i = 1; i <= count; i++)
            {
                const dayObject = this.getDayObject(year, month, i)
                days.push(dayObject)

                if (i == 1) {
                    firstWeekNumber = dayObject.weekNumber
                }
                lastWeekNumber = dayObject.weekNumber
            }

            let weekNumbers = this.firstDayOfWeek == 1 ? [1,2,3,4,5,6,7] : [7,1,2,3,4,5,6]

            // Complete first week with empty days
            let firstIndex = days[0].weekday
            let foundIndex = false

            let firstDay = DateTime.fromObject({year: days[0].year, month: days[0].month, day: days[0].day})
            let minusDays = -1;

            for (let i = 0; i < weekNumbers.length; i++) {
                if (weekNumbers[i] == firstIndex) {
                    foundIndex = true
                }
                if (!foundIndex) {
                    let theDay = firstDay.plus({days: minusDays})
                    minusDays--

                    days.unshift({
                        dateTime: theDay,
                        inMonth: false,
                        year: theDay.year,
                        month: theDay.month,
                        day: theDay.day,
                        weekday: theDay.weekday,
                        weekNumber: theDay.weekNumber,
                        dateFormat: theDay.toFormat('yyyy-MM-dd')
                    });
                }
            }

            // Complete until multiple of seven days
            const lastIndex = days.length-1
            let lastDay = DateTime.fromObject({year: days[lastIndex].year, month: days[lastIndex].month, day: days[lastIndex].day})
            let plusDays = 1;

            const number_rest = days.length % 7
            if (number_rest !== 0) {
                for (let i = number_rest; i < 7; i++){
                    let theDay = lastDay.plus({days: plusDays})
                    plusDays++
                    days.push({
                        dateTime: theDay,
                        inMonth: false,
                        year: theDay.year,
                        month: theDay.month,
                        day: theDay.day,
                        weekday: theDay.weekday,
                        weekNumber: theDay.weekNumber,
                        dateFormat: theDay.toFormat('yyyy-MM-dd')
                    });
                }
            }

            return days

        },

        pad(num, size = 2) {
            var s = num + "";
            while (s.length < size) s = "0" + s;
            return s;
        },

        daysInMonth(year, month){
            return DateTime.fromObject({year: year, month: month}).daysInMonth
        },

        getDayObject(year, month, day){
            //let date = year + '-' + this.pad(month) + '-' + this.pad(day);
            let date = DateTime.fromObject({year: year, month: month, day: day})
            let weekday = date.weekday // 1(monday) to 7(sunday)
            let weekNumber = date.weekNumber
            //console.log(date.toFormat('yyyy-MM-dd') + ' -> ' + weekday)

            return {
                dateTime: date,
                inMonth: true,
                year: year,
                month: month,
                day: day,
                weekday: weekday,
                weekNumber: weekNumber,
                dateFormat: date.toFormat('yyyy-MM-dd')
            };
        },

        monthCalendarFormatted(year = null, month = null) {
            if (!year) {
                year = this.currentYear
            }
            if (!month) {
                month = this.currentMonth
            }
            return DateTime.fromObject({year: year, month: month}).setLocale(this.locale).toFormat('LLL yyyy')
        },

        prevMonth() {
            let month = this.currentMonth - 1
            if (month < 1) {
                this.currentYear = this.currentYear - 1
                this.currentMonth = 12
            } else {
                this.currentMonth = month
            }
        },

        nextMonth() {
            let month = this.currentMonth + 1
            if (month > 12) {
                this.currentYear = this.currentYear + 1
                this.currentMonth = 1
            } else {
                this.currentMonth = month
            }
        },

        dayFormattedMed( day ) {
            return day.setLocale(this.locale).toLocaleString(DateTime.DATE_MED)
        },

        // date is a luxon DateTime
        calculateWeekDays(date_received, firstDayOfWeek) {
            let weekday = date_received.weekday
            let days = []

            let addToCorrect = firstDayOfWeek == 7 ? -1 : 0
            if (weekday == 7 && firstDayOfWeek == 7) { addToCorrect = 6 }

            for (let i = 1; i <= 7; i++){
                days.push(date_received.plus({days: i - weekday + addToCorrect}))
            }

            return days
        },
    }
}

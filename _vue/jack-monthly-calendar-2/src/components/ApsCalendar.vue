<template>

  <!-- Mobile view -->
  <div class="calendar-mobile-view md:hidden">
    <div v-for="(name, key) in weekLabelsWithKeys('long')" class="week-day text-left text-sm py-0 text-slate-400" :key="key">

      <div class="calendar-mobile-week-label cursor-pointer p-4 text-center"
        @click="weekdays_open[key] = !weekdays_open[key]"
      >
        <div class="flex flex-row items-center justify-between">
          <div class="text-xl">{{name}}</div>
          <svg v-if="weekdays_open[key]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
          </svg>
          <svg v-if="!weekdays_open[key]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
          </svg>
        </div>

      </div>

      <!-- DAYS -->
      <div v-for="(day, index) in getDaysMonth(currentYear, currentMonth)">

        <div v-if="day.weekday == key && weekdays_open[key]"
            class="month-day relative flex flex-col">

          <div class="flex flex-row items-center justify-between">
            <div class="text-left text-sm p-2"
                 :class="{'text-slate-800': day.inMonth, 'text-slate-400': !day.inMonth}"
            >{{ day.day }}</div>
            <div>{{ formatAmericanDate(day.dateFormat) }}</div>
          </div>

          <slot :day="day"></slot>

        </div>

      </div>

    </div>
  </div>

  <!-- Montly view -->
  <div class="calendar-desktop-view">
    <div class="month-days-wrap hidden  md:grid "
         style="grid-template-columns: repeat(7, minmax(120px, 300px));"
    >
      <!-- Week labels -->
      <div v-for="name in weekLabels('long')" class="week-day text-left text-sm py-2 text-slate-400 ">
        <span>{{name}}</span>
      </div>

      <!-- DAYS -->
      <div v-for="(day, index) in getDaysMonth(currentYear, currentMonth)"
           class="month-day relative flex flex-col"
           :class="{
            'month-day-last-column': (index+1) % 7 == 0,
            'not-current-month bg-slate-100': !day.inMonth
          }">
        <div class="month-day-label text-left text-sm p-2"
             :class="{'text-slate-800': day.inMonth, 'text-slate-400': !day.inMonth}"
        >{{ day.day }}
        </div>

        <div style="max-height: 300px; overflow-y: scroll;">
          <slot :day="day"></slot>
        </div>
      </div>

    </div>
  </div>

</template>

<script>
import DatesHelpers from "@shared/mixins/DatesHelpers";
export default {

  mixins: [DatesHelpers],

  props: {
    year: {
      required: true
    },
    month: {
      required: true
    },
    timezone: {
      default: 'America/New_York'
    },
    firstDayOfWeek: {
      default: 1,
    },
    locale: {
      default: 'en'
    },
  },

  watch:{
    year(){
      this.loadYearMonth()
    },
    month(){
      this.loadYearMonth()
    },
  },

  data(){
    return {
      weekdays_open: {
        1: false,
        2: false,
        3: false,
        4: false,
        5: false,
        6: false,
        7: false
      }
    }
  },

  created(){
   this.loadYearMonth()
  },

  methods:{
    loadYearMonth(){
      this.currentYear = parseInt(this.year)
      this.currentMonth = parseInt(this.month)
    },

    convertDate(regdate) {
      var m = regdate.match(/(\d+)-(\d+)-(\d+)\s+(\d+):(\d+):(\d+)/);
      var date = new Date(m[1], m[2] - 1, m[3], m[4], m[5], m[6]);
      return date.toLocaleDateString("en-US");
    },

    formatAmericanDate(date) {
      return this.convertDate(date + ' 00:00:00')
    },
  }

}
</script>

<style>
:root {
  --calendar-border-color: #319ac5;
}

.calendar-desktop-view .week-day {
  border-bottom: 3px solid var(--calendar-border-color);

  background: #FF9800;
  color: white;
  font-size: 20px;
  padding: 10px;
}
.calendar-desktop-view .month-day {
  border-left: 3px solid var(--calendar-border-color);
  border-bottom: 3px solid var(--calendar-border-color);
  min-height: 50px;
  height: auto;
  background: #c9efff;
}
.calendar-desktop-view .month-day.month-day-last-column {
  border-right: 3px solid var(--calendar-border-color);
}

.calendar-desktop-view .month-day-label {
  background: #319ac5;
  color: white;
}

.calendar-mobile-week-label {
  background: #319ac5;
  color: white;
}
.calendar-mobile-view img {
  display: none;
}
.calendar-mobile-view .week-day {
  border-bottom: 1px solid white;
}
.calendar-mobile-view .month-day {
  border: 1px solid #acacac;
  border-top: none;
  padding: 10px;
  background: #c9efff;
  color: black;
}
</style>

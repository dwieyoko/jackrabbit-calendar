<template>
  <div id="jack-weekly-calendar" class="text-3xl">
    <div v-if="!jackDataLoaded" class="text-center">Loading...</div>

    <template v-if="jackDataLoaded">


      <div class="flex flex-row items-center justify-center mb-6">
        <button @click="view = 'list'" class="cursor-pointer px-4 py-2"
          :class="view == 'list' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-800'"
        >{{ btn_list }}</button>
        <button @click="view = 'week'" class="cursor-pointer px-4 py-2"
          :class="view == 'week' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-800'"
        >{{ btn_week }}</button>
      </div>

      <!-- FILTERS -->
      <template v-if="filters.length >= 1">
        <select v-model="filter0_selected"
                @change="filters[0].classtype_filter ? handleClassTypeChange($event, 0) : null"
                class="select-cat1 filter-1 round p-4">
          <option :value="null">{{ filters[0].name}}</option>
          <option v-for="val in filters[0].values_allowed" :value="val">{{ val }}</option>
        </select>
      </template>

      <template v-if="filters.length >= 2">
        <select v-model="filter1_selected"
                @change="filters[1].classtype_filter ? handleClassTypeChange($event, 1) : null"
                class="select-cat1 filter-2 round p-4">
          <option :value="null">{{ filters[1].name}}</option>
          <option v-for="val in filters[1].values_allowed" :value="val">{{ val }}</option>
        </select>
      </template>

      <template v-if="filters.length >= 3">
        <select v-model="filter2_selected"
                @change="filters[2].classtype_filter ? handleClassTypeChange($event, 2) : null"
                class="select-cat1 filter-3 round p-4">
          <option :value="null">{{ filters[2].name}}</option>
          <option v-for="val in filters[2].values_allowed" :value="val">{{ val }}</option>
        </select>
      </template>

      <!-- LIST VIEW -->
      <ApsList v-if="view == 'list'" :items="getClassesForFilters"/>


      <!-- WEEKLY VIEW -->
      <ApsCalendar v-else>

        <template v-slot:week-label="{ day }">
          <div class="relative flex flex-row items-center justify-center cursor-pointer md:cursor-auto" @click="toggleDay(day.weekday)">
            <!-- Centered content for both mobile and desktop -->
            <div class="flex flex-col items-center justify-center">
              <div>{{ weekLabel(day) }}</div>
              <div class="md:hidden">({{ getClassesForWeekDay(day.weekday)?.length }} {{getClassesForWeekDay(day.weekday)?.length == 1 ? 'class' : 'classes'}})</div>
            </div>

            <!-- Absolutely positioned elements for mobile -->
            <div class="absolute left-0 top-1/2 transform -translate-y-1/2 md:hidden"></div>
            <div class="absolute right-0 top-1/2 transform -translate-y-1/2 md:hidden cursor-pointer p-1 rounded-full text-2xl">
              {{ expandedDays[day.weekday] ? '-' : '+' }}
            </div>
          </div>
        </template>

        <!-- Desktop/Mobile view -->
        <template v-slot:default="slotProps">
          <div :class="{'hidden md:block': !expandedDays[slotProps.day.weekday]}"
               class="jack-day-classes text-sm px-1">

            <div v-for="item in getClassesForWeekDay(slotProps.day.weekday)"
                 @click="selectClass(item)"
                 :style="{border: '3px solid black', background: getBackgroundColor(item)}"
                 class="jack-day-class my-1 cursor-pointer hover:color-black hover:bg-yellow-200 rounded-md p-2"
            >
              <div>{{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}</div>
              <div class="font-bold text-black">{{ item.name }}</div>
              <div class="jack-class-details _hidden">
                <div class="text-indigo-500 text-xs">{{item.min_age}}-{{item.max_age}}</div>
                <div class="text-indigo-500 text-xs">{{item.start_time}}</div>
                <div class="text-red-500 text-xs">{{item.category1}}</div>
                <div class="text-red-500 text-xs">{{item.category2}}</div>
                <div class="text-red-500 text-xs">{{item.category3}}</div>
                <div class="text-red-500 text-xs">ROOM: {{item.room}}</div>
              </div>

            </div>
          </div>
        </template>

      </ApsCalendar>

    </template>
  </div>

  <!-- MODAL VIEW -->
  <Modal v-if="showModal" @close="showModal = false">

    <div id="jackcalendar-modal-content-inner" class="bg-white relative rounded-xl" :style="{border: '8px solid black'}">

      <div class="absolute top-2 right-2 cursor-pointer hover:opacity-80" @click="showModal = false">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>

      <div class="p-4 pt-12">

        <div class="font-bold text-black text-xl mt-2">{{ classSelected.name }}</div>

        <!-- img class="w-full h-auto rounded-md max-w-[640px] mt-2" :src="getImageForTitle(classSelected.name)" -->
        <!-- img class="w-full h-auto rounded-md max-w-[640px] mt-2" :src="getImageForClassItem(classSelected)" -->

        <!-- Description -->
        <div v-if="classSelected.description" class="mt-4">
          <div class="text-black">
            <div v-if="!descriptionExpanded" class="description-preview">
              <div v-html="getFirstLineOfDescription(classSelected.description)"></div>
              <button
                @click="toggleDescription"
                class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-3 py-1.5 rounded-full border border-blue-200 hover:border-blue-300 transition-all duration-200 inline-flex items-center gap-1.5"
              >
                <span>Read more</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>
            </div>
            <div v-else class="description-full">
              <div v-html="classSelected.description"></div>
              <button
                @click="toggleDescription"
                class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-3 py-1.5 rounded-full border border-blue-200 hover:border-blue-300 transition-all duration-200 inline-flex items-center gap-1.5"
              >
                <span>Read less</span>
                <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Meeting Day -->
        <div v-if="formatMeetingDays(classSelected.meeting_days)" class="mt-4">
          <div class="font-semibold text-black">Meeting Day:</div>
          <div class="text-black">{{ formatMeetingDays(classSelected.meeting_days) }}</div>
        </div>

        <!-- Ages -->
        <div v-if="formatAgeRange(classSelected.min_age, classSelected.max_age)" class="mt-2">
          <div class="font-semibold text-black">Ages:</div>
          <div class="text-black">{{ formatAgeRange(classSelected.min_age, classSelected.max_age) }}</div>
        </div>

        <!-- Address -->
        <div v-if="formatAddress(classSelected.room)" class="mt-2">
          <div class="font-semibold text-black">Location:</div>
          <div class="text-black">{{ formatAddress(classSelected.room) }}</div>
        </div>

        <div class="flex flex-row items-start justify-between mt-4">
          <div class="">
            <div class="flex flex-row items-center gap-2">
              <div v-if="classSelected.start_date">Date: {{ formatDate(classSelected.start_date)}}</div>
              <div v-if="classSelected.end_date && classSelected.start_date != classSelected.end_date">- {{ formatDate(classSelected.end_date)}}</div>
            </div>
            <div class="flex flex-row items-center gap-2">
              <div v-if="classSelected.start_time">Time: {{ formatTime(classSelected.start_time)}}</div>
              <div v-if="classSelected.end_time">- {{ formatTime(classSelected.end_time)}}</div>
            </div>

          </div>
          <div v-if="classSelected.tuition && classSelected.tuition.fee" class="font-bold text-black">
            {{ isClassRecurring(classSelected) ? label_monthly_tuition : label_tutition}}: ${{ classSelected.tuition.fee }}
          </div>
        </div>


        <div v-if="hasRegisterLink(classSelected)" class="text-center mt-4">
          <a :href="getRegisterLink(classSelected)" target="_blank" class="jack-register-button">
            {{ registerButton }}
          </a>
        </div>

      </div>

    </div>
  </Modal>
</template>

<script>
import { DateTime } from "luxon";
import JackrabbitData from "@shared/mixins/JackrabbitData";
import Modal from "./components/Modal";
import ApsCalendar from "./components/ApsCalendar";
import ApsList from "./components/ApsList";

export default {
  mixins: [JackrabbitData],
  components: {ApsCalendar, ApsList, Modal },

  data() {
    return {
      view: 'week', // week, list
      locale: 'en',
      showModal: false,
      classSelected: null,
      descriptionExpanded: false,

      message_weekends: JACKCA_CALENDAR.message_weekends,
      colors: JACKCA_CALENDAR.location_colors,
      weekdayNames: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
      filterSelections: [null, null, null],
      expandedDays: {1: false, 2: false, 3: false, 4: false, 5: false, 6: false}, // Inicialmente todos expandidos
      btn_list: JACKCA_CALENDAR.btn_list,
      btn_week: JACKCA_CALENDAR.btn_week,
      // Nueva propiedad para controlar si es la primera carga
      isFirstLoad: true
    };
  },

  created() {
    this.getjacktabbitData();
  },

  mounted() {
    if (window.JACKCA_CALENDAR.view == 'list') {
      this.view = 'list'
    }
  },

  computed: {
    registerButton() {
      return this.classSelected?.openings?.calculated_openings > 0 ? 'Register' : 'Join Waitlist';
    },
    label_tutition() {
      return window.JACKCA_CALENDAR.fee_labels[0];
    },
    label_monthly_tuition() {
      return window.JACKCA_CALENDAR.fee_labels[1];
    },

    // Sin filtrar la primera carga
    getClassesForFilters_old() {
      const today = DateTime.now().startOf('day');

      return this.rows.filter(item => {
        const endDate = DateTime.fromISO(item.end_date);

        return endDate >= today && this.meetsAllFilters(item);
      }).sort((a, b) => this.compareTime(a.start_time, b.start_time));
    },

    // Ocultando un primer filtro
    getClassesForFilters() {
      const today = DateTime.now().startOf('day');

      return this.rows.filter(item => {
        const endDate = DateTime.fromISO(item.end_date);

        // Verificar si debemos ocultar este tipo de clase en la primera carga
        if (this.isFirstLoad &&
            this.view === 'week' &&
            window.JACKCA_CALENDAR.hide_class_type_first_load &&
            item.classtype === window.JACKCA_CALENDAR.hide_class_type_first_load) {
          return false;
        }

        return endDate >= today && this.meetsAllFilters(item);
      }).sort((a, b) => this.compareTime(a.start_time, b.start_time));
    },
  },

  methods: {

    toggleView(){
      this.isFirstLoad = false; // Reset el estado de primera carga
      if (this.view == 'list') {
        this.view = 'week'
      } else {
        this.view = 'list'
      }
    },

    toggleDay(weekday) {
      this.expandedDays[weekday] = !this.expandedDays[weekday];
    },

    weekLabel(day, format = 'long') {

      return DateTime.fromObject({ weekday: day.weekday })
          .setLocale(this.locale)
          .toLocaleString({ weekday: format });
    },

    /*getClassesForWeekDay_old(day) {
      return this.rows.filter(item => {
        const weekdays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        return item.meeting_days[weekdays[day - 1]] && this.meetsAllFilters(item);
      }).sort((a, b) => this.compareTime(a.start_time, b.start_time));
    },

    getClassesForWeekDay_old2(day) {
      const today = DateTime.now().startOf('day');

      return this.rows.filter(item => {
        const weekdays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        const endDate = DateTime.fromISO(item.end_date);

        return item.meeting_days[weekdays[day - 1]] &&
            endDate >= today &&
            this.meetsAllFilters(item);
      }).sort((a, b) => this.compareTime(a.start_time, b.start_time));
    },*/

    getClassesForWeekDay(day) {
      const today = DateTime.now().startOf('day');
      const weekdays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
      const bottomCategories = window.JACKCA_CALENDAR.list_view_bottom_categories || {};

      // Filter classes for the given day
      const filteredClasses = this.rows.filter(item => {
        const endDate = DateTime.fromISO(item.end_date);

        // Verificar si debemos ocultar este tipo de clase en la primera carga
        if (this.isFirstLoad &&
            this.view === 'week' &&
            window.JACKCA_CALENDAR.hide_class_type_first_load &&
            item.classtype === window.JACKCA_CALENDAR.hide_class_type_first_load) {
          return false;
        }

        return item.meeting_days[weekdays[day - 1]] &&
            endDate >= today &&
            this.meetsAllFilters(item);
      });

      // Separate items into top and bottom lists
      const bottomItems = [];
      const topItems = [];

      filteredClasses.forEach(item => {
        if (this.isItemInBottomCategories(item, bottomCategories)) {
          bottomItems.push(item);
        } else {
          topItems.push(item);
        }
      });

      // Sort each list alphabetically by item.name
      //const sortAlphabetically = (a, b) => a.name.localeCompare(b.name);
      //const sortedTopItems = topItems.sort(sortAlphabetically);
      //const sortedBottomItems = bottomItems.sort(sortAlphabetically);

      // Sort function by start_time
      const sortByStartTime = (a, b) => this.compareTime(a.start_time, b.start_time);
      const sortedTopItems = topItems.sort(sortByStartTime);
      const sortedBottomItems = bottomItems.sort(sortByStartTime);

      // Combine the sorted lists
      return [...sortedTopItems, ...sortedBottomItems];
    },

    isItemInBottomCategories(item, bottomCategories) {
      for (const [category, values] of Object.entries(bottomCategories)) {
        if (values.includes(item[category])) {
          return true;
        }
      }
      return false;
    },

    compareTime(time1, time2) {
      const [h1, m1] = time1.split(':').map(Number);
      const [h2, m2] = time2.split(':').map(Number);
      return h1 * 60 + m1 - (h2 * 60 + m2);
    },

    selectClass(item) {
      this.classSelected = item;
      this.descriptionExpanded = false; // Reset description state
      this.showModal = true;
    },

    registerLink(link) {
      return link.replaceAll('&amp;', '&');
    },

    getBackgroundColor(item) {
      let extract_value = item.name.match(/(.+)-(.+)/);
      if (extract_value) {
        const location = extract_value[1].trim();
        if (this.colors[location]) {
          return this.colors[location].color;
        }
      }
      extract_value = item.room.match(/(.+)-(.+)/);
      if (extract_value) {
        const location = extract_value[1].trim();
        if (this.colors[location]) {
          return this.colors[location].color;
        }
      }
      return 'white';
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

    handleClassTypeChange(event, index) {
      this.isFirstLoad = false; // Reset el estado de primera carga
      console.log('handleClassTypeChange', event, index)
      const selectedValue = event.target.value;
      console.log(selectedValue)
      this.handleClassTypeViewChange(selectedValue);
    },

    toggleDescription() {
      this.descriptionExpanded = !this.descriptionExpanded;
    },

    getFirstLineOfDescription(description) {
      if (!description) return '';

      // Remove HTML tags and get plain text
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = description;
      const plainText = tempDiv.textContent || tempDiv.innerText || '';

      // Split by sentences and take the first one, or limit by characters
      const sentences = plainText.split(/[.!?]+/);
      const firstSentence = sentences[0];

      // If first sentence is too long, truncate at around 100 characters
      if (firstSentence.length > 100) {
        return firstSentence.substring(0, 100) + '...';
      }

      // If we have a complete sentence, add the punctuation back
      if (sentences.length > 1) {
        return firstSentence + '.';
      }

      // If it's just one sentence or fragment, return as is
      return firstSentence;
    },



    formatMeetingDays(meetingDays) {
      if (!meetingDays) return '';

      const dayNames = {
        mon: 'Monday',
        tue: 'Tuesday',
        wed: 'Wednesday',
        thu: 'Thursday',
        fri: 'Friday',
        sat: 'Saturday',
        sun: 'Sunday'
      };

      const activeDays = Object.keys(meetingDays)
        .filter(day => meetingDays[day])
        .map(day => dayNames[day]);

      return activeDays.join(', ');
    },

    formatAgeRange(minAge, maxAge) {
      if (!minAge && !maxAge) return '';

      const parseAge = (ageString) => {
        if (!ageString) return null;
        const match = ageString.match(/P(\d+)Y/);
        return match ? parseInt(match[1]) : null;
      };

      const min = parseAge(minAge);
      const max = parseAge(maxAge);

      if (min && max) {
        return `Ages ${min}-${max}`;
      } else if (min) {
        return `Ages ${min}+`;
      } else if (max) {
        return `Ages up to ${max}`;
      }
      return '';
    },

    formatAddress(room) {
      if (!room) return '';

      // Extract address after the first dash
      const match = room.match(/^[^-]+-\s*(.+)$/);
      return match ? match[1].trim() : room;
    },
  }
};
</script>

<style>

.select-cat1 {
  position: relative;
  font-size: 24px;
  line-height: 40px;
  background: #ffd100;
  text-align: center;
}
.select-cat1 {
  margin: 0;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  -webkit-appearance: none;
  -moz-appearance: none;
}

.select-cat1{
  appearance: none;
  background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
  background-repeat: no-repeat;
  background-position: right 2rem top 50%;
  background-size: 0.65rem auto;
}

.select-cat1.round_ {
  background-image:
      linear-gradient(45deg, transparent 50%, gray 50%),
      linear-gradient(135deg, gray 50%, transparent 50%),
      radial-gradient(#ddd 70%, transparent 72%);
  background-position:
      calc(100% - 20px) calc(1em + 2px),
      calc(100% - 15px) calc(1em + 2px),
      calc(100% - .5em) .5em;
  background-size:
      5px 5px,
      5px 5px,
      1.5em 1.5em;
  background-repeat: no-repeat;
}

.calendar-change-month {
  padding: 20px;
  background: #02b9d6;
  color: white;
}

/*.jack-day-class {
  border: 1px solid #b8b85d;
  background: #f8f8ca;
}*/
.jack-register-button {
  font-size: 30px;
  font-weight: bold;
  background-color: #00B9D6FF;
  padding: 10px;
  color: white;
  text-decoration: none;
  border-bottom-left-radius: 49% 54%;
  border-bottom-right-radius: 51% 54%;
  border-top-left-radius: 71% 46%;
  border-top-right-radius: 29% 46%;
  padding-bottom: 15px;
  padding-left: 45px;
  padding-right: 45px;
  padding-top: 15px;
}

</style>

<template>
  <div class="jack-list-classes">
    <div class="jack-list-header p-6 text-base">
      <div class="jack-list-count">{{ items.length }} CLASSES</div>
    </div>

    <!-- Headers - Visible only on desktop -->
    <div class="jack-list-desktop-headers hidden md:grid md:grid-cols-11 text-sm gap-4 p-4 font-bold" style="border-bottom: 2px solid grey">
      <div class="jack-header-class-name col-span-6">NAME</div>
      <div class="jack-header-days col-span-1">DAYS</div>
      <div class="jack-header-time-date col-span-2">TIME & DATE</div>
      <div class="jack-header-tuition col-span-1">TUITION</div>
      <div class="jack-header-registration col-span-1"></div>
    </div>

    <!-- Class Items -->
    <div class="jack-list-items">
      <div v-for="item in sortedItems" :key="item.id" class="jack-list-item grid md:grid-cols-11 text-sm gap-4 p-4" style="border-bottom: 1px solid #cdcdcd">
        <div class="jack-item-name-description col-span-11 md:col-span-6 space-y-2">
          <div class="jack-item-name class-name font-bold">{{ item.name }}</div>
          <div class="jack-item-description class-description" v-html="item.description"></div>
          <div class="jack-item-categories hidden">
            <div class="text-red-500">{{ item.category1 }}</div>
            <div class="text-red-500">{{ item.category2 }}</div>
            <div class="text-red-500">{{ item.category3 }}</div>
          </div>
        </div>

        <div class="jack-item-days col-span-11 md:col-span-1 mt-2 md:mt-0">
          <div class="jack-mobile-label md:hidden font-bold">DAYS:</div>
          <div v-if="item.meeting_days" class="jack-item-meeting-days">
            {{ formatMeetingDays(item.meeting_days) }}
          </div>
          <div v-else class="jack-item-no-meeting-days">
            -
          </div>
        </div>

        <div class="jack-item-time-date col-span-11 md:col-span-2 mt-2 md:mt-0">
          <div class="jack-mobile-label md:hidden font-bold">TIME & DATE:</div>
          <div class="jack-item-time class-time">{{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}</div>
          <div class="jack-item-date class-date">{{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}</div>
        </div>

        <div class="jack-item-tuition col-span-11 md:col-span-1 mt-2 md:mt-0">
          <div class="jack-mobile-label md:hidden font-bold">TUITION:</div>
          <div v-if="item.tuition && item.tuition.fee" class="jack-item-fee flex flex-row items-center gap-2">
            <div class="jack-item-fee-label">{{ isClassRecurring(item) ? label_monthly_tuition : label_tutition}}</div>
            <div class="jack-item-fee-amount font-bold">${{ item.tuition.fee }}</div>
          </div>
        </div>

        <div class="jack-item-registration col-span-11 md:col-span-1 mt-4 md:mt-0">
          <div v-if="hasRegisterLink(item)" class="text-center md:mt-0">
            <a :href="getRegisterLink(item)" target="_blank" class="jack-list-register-button inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
              {{ registerButton(item) }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import JackrabbitData from "@shared/mixins/JackrabbitData";
export default {
  mixins: [JackrabbitData],
  props: {
    items: { default: [] },
  },

  computed: {

    label_tutition() {
      return window.JACKCA_CALENDAR.fee_labels[0];
    },
    label_monthly_tuition() {
      return window.JACKCA_CALENDAR.fee_labels[1];
    },
    sortedItems() {
      const bottomCategories = window.JACKCA_CALENDAR.list_view_bottom_categories || {};

      // Separate items into top and bottom lists
      const bottomItems = [];
      const topItems = [];

      this.items.forEach(item => {
        if (this.isItemInBottomCategories(item, bottomCategories)) {
          bottomItems.push(item);
        } else {
          topItems.push(item);
        }
      });

      // Sort each list alphabetically by item.name
      const sortAlphabetically = (a, b) => a.name.localeCompare(b.name);

      const sortedTopItems = topItems.sort(sortAlphabetically);
      const sortedBottomItems = bottomItems.sort(sortAlphabetically);

      // Combine the sorted lists
      return [...sortedTopItems, ...sortedBottomItems];
    },
  },

  methods: {
    isItemInBottomCategories(item, bottomCategories) {
      for (const [category, values] of Object.entries(bottomCategories)) {
        if (values.includes(item[category])) {
          return true;
        }
      }
      return false;
    },

    registerButton(row) {
      return row.openings?.calculated_openings > 0 ? 'Register' : 'Join Waitlist';
    },
    registerLink(link) {
      return link.replaceAll('&amp;', '&');
    },
    formatMeetingDays(meetingDays) {
      const dayMap = {
        mon: 'Mon',
        tue: 'Tue',
        wed: 'Wed',
        thu: 'Thu',
        fri: 'Fri',
        sat: 'Sat',
        sun: 'Sun'
      };
      return Object.entries(meetingDays)
          .filter(([_, value]) => value)
          .map(([day, _]) => dayMap[day])
          .join(', ');
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const [year, month, day] = dateString.split('-');
      return `${month}/${day}/${year}`;
    },
  }
}
</script>

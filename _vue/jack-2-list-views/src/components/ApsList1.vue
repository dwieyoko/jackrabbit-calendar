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
      <div v-for="item in sortedItems" :key="item.id"
           @click="showPopup(item)"
           class="jack-list-item grid md:grid-cols-11 text-sm gap-4 p-4 relative cursor-pointer md:cursor-auto"
           style="border-bottom: 1px solid #cdcdcd">
        <div class="jack-item-name-description col-span-11 md:col-span-6 space-y-2 md:space-y-2 space-y-1">
          <div class="jack-item-name class-name font-bold">{{ item.name }}</div>

          <!-- div class="jack-item-description class-description md:block hidden" v-html="item.description"></div -->

          <!-- Description with expand/collapse -->
          <!-- div class="jack-item-description md:block hidden relative">
            <div :class="{'line-clamp-1': !expandedDescriptions[item.id]}"
                 class="transition-all duration-300"
                 v-html="item.description">
            </div>
            <div v-if="hasLongDescription(item)"
                 @click.stop="toggleDescription(item.id)"
                 class="text-blue-600 hover:text-blue-800 cursor-pointer text-sm mt-1 flex items-center gap-1">
              <span>{{ expandedDescriptions[item.id] ? 'Show less' : 'Show more' }}</span>
              <svg v-if="!expandedDescriptions[item.id]" xmlns="http://www.w3.org/2000/svg"
                   class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg"
                   class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                      d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                      clip-rule="evenodd" />
              </svg>
            </div>
          </div -->


          <div class="jack-item-categories hidden">
            <div class="text-red-500">{{ item.category1 }}</div>
            <div class="text-red-500">{{ item.category2 }}</div>
            <div class="text-red-500">{{ item.category3 }}</div>
          </div>
        </div>

        <div class="jack-item-days col-span-11 md:col-span-1 mt-2 md:mt-0 hidden md:block">
          <div v-if="item.meeting_days" class="jack-item-meeting-days">
            {{ formatMeetingDays(item.meeting_days) }}
          </div>
          <div v-else class="jack-item-no-meeting-days">
            -
          </div>
        </div>

        <div class="jack-item-time-date col-span-11 md:col-span-2 mt-0 md:mt-0">
          <div class="jack-item-time class-time text-sm md:text-base">{{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}</div>
          <div class="jack-item-date class-date text-sm md:text-base">{{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}</div>
        </div>

        <div class="jack-item-tuition col-span-11 md:col-span-1 mt-2 md:mt-0 hidden md:block">
          <div v-if="item.tuition && item.tuition.fee" class="jack-item-fee flex flex-row items-center gap-2">
            <div class="jack-item-fee-label">{{ isClassRecurring(item) ? label_monthly_tuition : label_tutition}}</div>
            <div class="jack-item-fee-amount font-bold">${{ item.tuition.fee }}</div>
          </div>
        </div>

        <div class="jack-item-registration col-span-11 md:col-span-1 mt-4 md:mt-0 hidden md:block">
          <div v-if="hasRegisterLink(item)" class="text-center md:mt-0">
            <a :href="getRegisterLink(item)" target="_blank" class="jack-list-register-button inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
              {{ registerButton(item) }}
            </a>
          </div>
        </div>

        <!-- Info Icon - Visible only on mobile -->
        <div class="md:hidden absolute right-4 top-1/2 transform -translate-y-1/2">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-500">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
        </div>
      </div>
    </div>

    <!-- Popup for mobile view -->
    <div v-if="selectedItem" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 md:hidden" @click="closePopup">
      <div class="bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto relative" @click.stop>
        <!-- Close (X) button -->
        <div class="sticky top-0 bg-white z-10 flex justify-end p-4">
          <svg @click="closePopup" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>

        <div class="p-6 pt-0">
          <h2 class="text-lg font-bold mb-3">{{ selectedItem.name }}</h2>
          <div class="mb-3 text-sm" v-html="selectedItem.description"></div>
          <div class="mb-2 text-sm"><strong>Days:</strong> {{ formatMeetingDays(selectedItem.meeting_days) }}</div>
          <div class="mb-2 text-sm"><strong>Time:</strong> {{ formatTime(selectedItem.start_time) }} - {{ formatTime(selectedItem.end_time) }}</div>
          <div class="mb-2 text-sm"><strong>Date:</strong> {{ formatDate(selectedItem.start_date) }} - {{ formatDate(selectedItem.end_date) }}</div>
          <div class="mb-3 text-sm" v-if="selectedItem.tuition && selectedItem.tuition.fee">
            <strong>{{ isClassRecurring(selectedItem) ? label_monthly_tuition : label_tutition }}:</strong> ${{ selectedItem.tuition.fee }}
          </div>
          <div v-if="hasRegisterLink(selectedItem)" class="text-center">
            <a :href="getRegisterLink(selectedItem)" target="_blank" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors text-sm">
              {{ registerButton(selectedItem) }}
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
  data() {
    return {
      selectedItem: null,
      expandedDescriptions: {}, // Simple object to track expanded state
      descriptionLineHeight: 20, // Default line height in pixels
      maxLines: 1
    }
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

      // Sort each list by start_time
      //const sortByStartTime = (a, b) => this.compareTime(a.start_time, b.start_time);
      //const sortedTopItems = topItems.sort(sortByStartTime);
      //const sortedBottomItems = bottomItems.sort(sortByStartTime);

      // Sort each list by start_date
      const sortByStartDate = (a, b) => a.start_date.localeCompare(b.start_date);
      const sortedTopItems = topItems.sort(sortByStartDate);
      const sortedBottomItems = bottomItems.sort(sortByStartDate);

      // Combine the sorted lists
      return [...sortedTopItems, ...sortedBottomItems];
    },
  },
  methods: {
    hasLongDescription(item) {

      // Create a temporary div to measure text height
      if (!item.description) return false;

      return item.description.length > 100;

      /*const temp = document.createElement('div');
      temp.style.visibility = 'hidden';
      temp.style.position = 'absolute';
      temp.style.width = '100%';
      temp.style.lineHeight = `${this.descriptionLineHeight}px`;
      temp.innerHTML = item.description;
      document.body.appendChild(temp);

      const isLong = temp.offsetHeight > (this.descriptionLineHeight * this.maxLines);
      document.body.removeChild(temp);

      return isLong;*/
    },

    toggleDescription(itemId) {
      // Use Vue 3's reactivity by directly modifying the object
      this.expandedDescriptions[itemId] = !this.expandedDescriptions[itemId];
    },

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
    showPopup(item) {
      this.selectedItem = item;
    },
    closePopup() {
      this.selectedItem = null;
    },
    compareTime(time1, time2) {
      const [h1, m1] = time1.split(':').map(Number);
      const [h2, m2] = time2.split(':').map(Number);
      return h1 * 60 + m1 - (h2 * 60 + m2);
    },
  }
}
</script>

<template>
  <div class="jack-list-classes text-base">
    <!-- Header con contador de clases -->
    <div class="jack-list-header p-6 text-base">
      <div class="jack-list-count">{{ items.length }} CLASSES</div>
    </div>

    <!-- Lista de clases -->
    <div class="jack-list-items space-y-6">
      <div v-for="item in sortedItems"
           :key="item.id"
           class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex md:flex-row flex-col">
          <!-- Imagen izquierda -->
          <div class="w-full md:w-1/4 h-auto relative">
            <img
                :src="getImageForClassItem(item)"
                :alt="item.name"
                class="w-full h-auto object-fill"
            />
          </div>

          <!-- Contenido derecha -->
          <div class="flex-1 p-6">
            <h2 class="text-lg font-bold text-orange-500 mb-2">
              {{ item.name }}
            </h2>

            <div class="space-y-3 text-gray-600">

              <!-- Description -->
              <div v-if="item.description?.includes('<summary>')" class="jack-item-description block relative" v-html="item.description">
              </div>

              <!-- Description with expand/collapse -->
              <div v-else class="jack-item-description block relative">
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
              </div>

              <!-- Ubicación -->
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <!--span>{{ item.room }}</span-->
                <span>{{ formatRoom(item.room) }}</span>
              </div>

              <!-- Age Range -->
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>{{ formatAge(item.min_age, item.max_age) }}</span>
              </div>

              <!-- Fecha -->
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>
                  {{ formatDate(item.start_date) }}
                  <template v-if="item.start_date !== item.end_date">
                    - {{ formatDate(item.end_date) }}
                  </template>
                  <template v-if="item.session">
                    ({{ item.session }})
                  </template>
                </span>
              </div>

              <!-- Hora -->
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>
                  {{ formatTime(item.start_time) }} - {{ formatTime(item.end_time) }}
                </span>
              </div>

              <!-- Días y Precio -->
              <div class="block md:flex md:items-center gap-4 mt-4">
                <div class="flex gap-1">
                  <template v-for="day in orderedDays" :key="day">
                    <span
                        class="w-6 h-6 flex items-center justify-center rounded"
                        :class="item.meeting_days[day] ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500'"
                    >
                      {{ day.charAt(0).toUpperCase() }}
                    </span>
                  </template>
                </div>
                <span class="text-xl font-bold">
                  ${{ item.tuition?.fee || 0 }}
                </span>
              </div>

              <!-- Botón de registro -->
              <div class="mt-6">
                <a
                    v-if="hasRegisterLink(item)"
                    :href="getRegisterLink(item)"
                    target="_blank"
                    class="inline-block bg-blue-500 text-white px-8 py-3 rounded-lg text-base font-bold hover:bg-blue-600 transition-colors"
                >
                  {{ registerButton(item) }}
                </a>
              </div>

            </div>
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
    items: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      expandedDescriptions: {}, // Simple object to track expanded state
      descriptionLineHeight: 20, // Default line height in pixels
      maxLines: 1
    }
  },
  computed: {
    orderedDays() {
      return ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    },
    sortedItems() {
      const bottomCategories = window.JACKCA_CALENDAR.list_view_bottom_categories || {};

      // Separar items en listas top y bottom
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
    formatRoom(room) {
      if (!room) return '';
      const parts = room.split('-');
      if (parts.length > 1) {
        return parts[1].trim();
      }
      return room.trim();
    },
    formatAge(minAge, maxAge) {
      if (!minAge || !maxAge) return '';

      const getYears = (age) => parseInt(age.replace('P', '').replace('Y00M', ''));
      const minYears = getYears(minAge);
      const maxYears = getYears(maxAge);

      if (minYears === maxYears) {
        return `${minYears} years`;
      }
      return `${minYears}-${maxYears} years`;
    },

    getImageForClassItem(item) {
      const categoryImages = window.JACKCA_CALENDAR.category_images || [];
      const itemName = item.name.toLowerCase();

      // Buscar coincidencia con cada imagen en orden
      for (const imageConfig of categoryImages) {
        if (!imageConfig.words || imageConfig.words.length === 0) continue;

        // Comprobar si alguna palabra coincide con el nombre
        const hasMatch = imageConfig.words.some(word =>
            itemName.includes(word.toLowerCase())
        );

        if (hasMatch) {
          return imageConfig.url;
        }
      }

      // Si no hay coincidencias, usar la última imagen como default
      const defaultImage = categoryImages.length > 0 ?
          categoryImages[categoryImages.length - 1].url :
          'https://placehold.co/600x400?text=-';

      return defaultImage;
    },

    getImageForClassItem_for_categories(item) {
      const categoryImages = window.JACKCA_CALENDAR.category_images || [];
      const categories = [item.category1, item.category2, item.category3].filter(Boolean);

      // Buscar coincidencia con cada imagen en orden
      for (const imageConfig of categoryImages) {
        if (!imageConfig.words || imageConfig.words.length === 0) continue;

        // Comprobar si alguna palabra coincide con alguna categoría
        const hasMatch = imageConfig.words.some(word =>
            categories.some(category =>
                category.toLowerCase().includes(word.toLowerCase())
            )
        );

        if (hasMatch) {
          return imageConfig.url;
        }
      }

      // Si no hay coincidencias, usar la última imagen como default
      const defaultImage = categoryImages.length > 0 ?
          categoryImages[categoryImages.length - 1].url :
          'https://placehold.co/600x400?text=-';

      return defaultImage;
    },

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


  }
};
</script>

<style scoped>
.jack-list-classes a {
  text-decoration: none;
}
</style>

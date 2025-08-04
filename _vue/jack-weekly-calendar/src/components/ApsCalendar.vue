<template>
  <div class="aps-calendar">
    <!-- Desktop view -->
    <div class="calendar-desktop-view hidden md:block">

      <div class="week-days-wrap grid grid-cols-6">

        <!-- Week labels -->
        <div v-for="day in [1, 2, 3, 4, 5, 6]" :key="day"
             class="week-day text-left text-sm py-2 text-slate-400">

          <slot :day="{ weekday: day }" name="week-label">
            <span>{{ weekLabel(day) }}</span>
          </slot>

        </div>

        <!-- div v-for="name in weekLabels('long').slice(0, 6)" :key="name" class="week-day text-left text-sm py-2 text-slate-400">
          <span>{{ name }}</span>
        </div -->
      </div>

      <div class="days-wrap grid grid-cols-6">
        <!-- Days -->
        <div v-for="day in [1, 2, 3, 4, 5, 6]" :key="day" class="day-column">

          <slot :day="{ weekday: day }" name="default">
            <!-- Default content if no slot is provided -->
            <div class="text-center py-2">No classes</div>
          </slot>

        </div>
      </div>
    </div>

    <!-- Mobile view -->
    <div class="calendar-mobile-view md:hidden">

      <div v-for="day in [1, 2, 3, 4, 5, 6]" :key="day" class="mb-0">

        <div class="week-day">
          <slot :day="{ weekday: day }" name="week-label">
            <span>{{ weekLabel(day) }}</span>
          </slot>
        </div>

        <div class="day-column">
          <slot :day="{ weekday: day }">
            <!-- Default content if no slot is provided -->
            <div class="text-center py-2">No classes</div>
          </slot>
        </div>


      </div>
    </div>
  </div>
</template>

<script>
import { DateTime } from "luxon";

export default {
  props: {
    locale: {
      type: String,
      default: 'en'
    },
    firstDayOfWeek: {
      type: Number,
      default: 1
    }
  },

  methods: {
    weekLabels(format = 'short') {
      const weekNumbers = [1, 2, 3, 4, 5, 6, 7];
      const labels = weekNumbers.map(n =>
          DateTime.fromObject({ weekday: n })
              .setLocale(this.locale)
              .toLocaleString({ weekday: format })
      );

      // Adjust the array based on firstDayOfWeek
      const adjustedLabels = [
        ...labels.slice(this.firstDayOfWeek - 1),
        ...labels.slice(0, this.firstDayOfWeek - 1)
      ];

      return adjustedLabels;
    }
  }
};
</script>

<style>
:root {
  --calendar-border-color: #319ac5;
}

.calendar-desktop-view .week-day,
.calendar-mobile-view .week-day{
  border-bottom: 3px solid var(--calendar-border-color);

  background: #FF9800;
  color: white;
  font-size: 20px;
  padding: 10px;
}

.calendar-desktop-view .day-column {
  border-left: 3px solid var(--calendar-border-color);
  border-bottom: 3px solid var(--calendar-border-color);
  min-height: 50px;
  height: auto;
  background: #c9efff;
}

.calendar-mobile-view .day-column {
  border: 3px solid var(--calendar-border-color);
  min-height: 10px;
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

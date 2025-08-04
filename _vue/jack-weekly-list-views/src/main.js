import { createApp } from 'vue'
import App from './App.vue'

import { DateTime } from "luxon";
window.DateTime = DateTime

import Swal from "sweetalert2";
window.Swal = Swal

createApp(App).mount('#app-weekly-list-views')

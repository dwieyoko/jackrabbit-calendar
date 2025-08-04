<template>
  <div style="margin-top: 20px;">

    <div style="display: flex; flex-direction: row; align-items: center; gap: 10px;">
      <div>OrgID</div>
      <input type="text" v-model="orgid">
      <div>Field</div>
      <select v-model="field_name">
        <option value="name">name</option>
        <option value="room">room</option>
      </select>
    </div>

    <div style="display: flex; flex-direction: row; align-items: center; gap: 10px;">
      <div>Colors</div>
      <input type="text" class="large-text" v-model="colorsString" placeholder="#FF0000,#00FF00,#0000FF">
    </div>

    <div style="margin-top: 15px; color: blue; cursor: pointer;" @click.stop="extract">Extract locations from class: location - name|room</div>

  </div>
</template>

<script setup>
import {ref} from 'vue'

const orgid = ref(0)
const field_name = ref('name')
const colorsString = ref('')

const locations = ref([])

function extract()
{
  //alert('Extract')

  const url = window.JACKCA_CALENDAR.endpoint + '?orgid=' + orgid.value

  jQuery.getJSON(url, (response) => {
    if (response.success)
    {
      //console.log(response.rows)
      getListFormField(response.rows, field_name.value)

      //console.log(locations.value.sort())
      pasteToTextarea()
    } else {
      if (response.message) {
        alert(response.message)
      } else {
        alert('ERROR API')
      }
    }
  })
}

function getListFormField( rows, field_name = 'name') {
  let list = []

  locations.value = []

  // Probar con name
  for (let i = 0; i < rows.length; i++)
  {
    const term = rows[i][field_name]
    const result = term.match(/(.+)-(.+)/)
    if (result){
      const location = result[1].trim()
      if (!locations.value.includes(location)) {
        locations.value.push(location)
      }
    }
  }
}

function pasteToTextarea() {
  let text = ''
  const list = locations.value.sort()
  const colors = getColorsArray()

  for (let i = 0; i < list.length; i++)
  {
    let indexColor = i % colors.length

    text += list[i] + ':' + colors[indexColor] + "\n";
  }

  //alert(text);
  document.getElementById('jacka-colors').value += text
}

function getColorsArray(){
  let colors = colorsString.value.split(',')
      .map( color => color.trim())
      .filter(color => color.length > 6)

  if (colors.length > 0) return colors

  return ['#FFFFFF']
}
</script>

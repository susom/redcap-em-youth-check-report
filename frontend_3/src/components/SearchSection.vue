<template>
  <div>
    <vSelect placeholder="Please Choose a record" class="mb-3 nopadding" value="id" :options="records_list" v-model="record_id"
             :reduce="(option) => option.id"
             @update:modelValue="setRecordId"></vSelect>
  </div>
</template>
<script>

import vSelect from 'vue-select'
import eventBus from '../eventBus';


import 'vue-select/dist/vue-select.css';

export default {
  components: {
    vSelect
  },
  name: "SearchSection",
  methods: {
    setRecordId: function (record_id) {
      eventBus.emit('update_record_id', record_id);
      this.addParameterToUrl('record_id', record_id)
    },
    addParameterToUrl: function (parameterName, parameterValue) {
      const url = new URL(window.location.href);
      const searchParams = url.searchParams;
        // Check if the parameter exists
      if (searchParams.has(parameterName)) {
        // Update the existing parameter
        searchParams.set(parameterName, parameterValue);
      } else {
        // Add the new parameter
        searchParams.append(parameterName, parameterValue);
      }
      const newUrl = url.toString();
      history.pushState({}, "", newUrl);
    }
  },
  data() {
    return {
      record_id: null,
      records_list: window.records_list
    }
  },
  mounted() {
    let urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('record_id')) {
      this.record_id = parseInt(urlParams.get('record_id'));
    }
  }
}
</script>
<style scoped>

</style>
<template>
    <div>
      <div class="col-xs-12">
        <h1>DPS Symptom Scale Reconstruction</h1>

        <div v-for="(item, index) in this.record.symptom_scale_reconstruction" :key="index">
          <h2>{{ index }}</h2>
          <table id="{{index}}" class="table table-striped table-condensed">
            <thead v-if="item.header !== undefined">
            <tr>
              <th v-for="item in item.header" :key="item">{{ item }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in item.rows" :key="row">
              <td v-for="col in row" :key="col" v-html="col"/>
            </tr>
            </tbody>
          </table>
          <br>
        </div>
      </div>
    </div>
</template>
<script>
export default {
  name: "DPSSymptomScaleSection",
  props: {
    record: Object,
  },
}
</script>
<style scoped>

</style>
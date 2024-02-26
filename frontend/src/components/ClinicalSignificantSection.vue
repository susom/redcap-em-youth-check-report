<script>
export default {
  name: "ClinicalSignificantSection",
  props: {
    record: Object,
  },
}
</script>

<template>
  <div>
    <div v-if="this.record.clinical_significant.length !== 0" class="row mt-5">
        <div class="row col-xs-10">
            <h2>Clinically Significant Information</h2>
          </div>
        <div class="col-sm-4">
          <table id="clinical_significant" class="table table-striped table-condensed" cellspacing="0" width="95%">
            <thead v-if="this.record.clinical_significant.header !== undefined">
            <tr v-for="(index, item) in this.record.clinical_significant.header" :key="item.record_id">
              <th>{{ item }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in this.record.clinical_significant.rows" :key="row.record_id">
              <td v-for="col in row" :key="col" v-html="col"/>
            </tr>
            </tbody>
          </table>
        </div>
        <div class="col-sm-8">
          <table id="check_list" class="table table-striped table-condensed" cellspacing="0" width="95%">
              <thead v-if="this.record.check_list.header !== undefined">
              <tr>
                <th v-for="item in this.record.check_list.header" :key="item">{{ item }}</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="row in this.record.check_list.rows" :key="row">
                <td v-if="typeof row === 'string'">{{ row }}</td>
                <div v-else>
                  <td v-for="col in row" :key="col" v-html="col"/>
                </div>
              </tr>
              </tbody>
            </table>
        </div>
      </div>
  </div>
</template>

<style scoped>

</style>
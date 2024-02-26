<script>
export default {
  name: "ScalesSection",
  props: {
    record: Object,
  },
}
</script>

<template>
  <div>
    <div class="row m-t-3">
        <div class="print-expand col-sm-6">
          <h3 id="scale">Symptom Scale</h3>
          <table id="score_table" class="table table-striped table-condensed" cellspacing="0" width="95%">
            <thead v-if="this.record.score_table.header !== undefined">
            <tr>
              <th v-for="item in this.record.score_table.header" :key="item">{{ item }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in this.record.score_table.rows" :key="row">
              <td v-for="col in row" :key="col" v-html="col"/>
            </tr>
            </tbody>
          </table>
        </div>
        <div class="print-expand col-sm-6">
          <h3 id="scale">Impairment Scale</h3>
          <table id="impair_status" class="table table-striped table-condensed" cellspacing="0" width="95%">
            <thead v-if="this.record.impair_status.header !== undefined">
            <tr>
              <th v-for="item in this.record.impair_status.header" :key="item">{{ item }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in this.record.impair_status.rows" :key="row">
              <td v-if="typeof row === 'string'">{{ row }}</td>
              <div v-else>
                <td v-for="col in row" :key="col">{{ col }}</td>
              </div>
            </tr>
            </tbody>
          </table>
          <hr>
          <table id="impair_symptom" class="table table-striped table-condensed" cellspacing="0" width="95%">
            <thead v-if="this.record.impair_symptom.header !== undefined">
            <tr>
              <th v-for="item in this.record.impair_symptom.header" :key="item">{{ item }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in this.record.impair_symptom.rows" :key="row">
              <td v-if="typeof row === 'string'">{{ row }}</td>
              <div v-else>
                <td v-for="col in row" :key="col">{{ col }}</td>
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
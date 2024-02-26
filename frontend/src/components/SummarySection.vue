<script>
export default {
  name: "NewSection",
  props: {
    record: Object,
  },
}
</script>

<template>
  <div>
    <div class="row m-t-3 print-expand">
      <div class="print-expand col-sm-4">
        <h2>Report for {{ this.record.summary.test_id }}</h2>
        <br/>
        <table class="table table-striped">
          <tbody>
          <tr>
            <td><strong class="text-capitalize">Total
              Symptom Score</strong></td>
            <td>{{ this.record.summary.score_total_dps }}</td>
          </tr>
          <tr>
            <td><strong class="text-capitalize">Total
              Impairment Score</strong></td>
            <td>{{ this.record.summary.score_impairment }}</td>
          </tr>
          <tr>
            <td><strong class="text-capitalize">Interview
              Date</strong></td>
            <td>{{ this.record.summary.interview_date }}</td>
          </tr>
          </tbody>
        </table>
      </div>
      <div class="print-expand col-sm-8">
        <h2 v-if="this.record.further_eval.impair_list == null">Further Evaluation Not Recommended</h2>
        <h2 v-else>Further Evaluation Recommended</h2>
        <ul v-for="item in this.record.further_eval.impair_list" :key="item.record_id">
          <li v-html="item"/>
        </ul>
        <strong><em>Additional reasons for evaluation:</em></strong>
        <ul>
          <li>Open-ended response: {{ this.record.further_eval.open_ended_response }}</li>
          <li>Did youth or program staff recommend clinical interview?
            {{ this.record.further_eval.recommend_clinical_interview }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>
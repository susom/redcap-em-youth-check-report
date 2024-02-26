<template>
  <div>
    <div class="row">
      <div v-if="showAlert === true" class="col-12">
        <p :class="alertVariant" class="alert">{{ alertMessage }}</p>
      </div>
    </div>
    <div v-if="showLoader === true" class="row">
      <div class="col-12">
        <div class="d-flex justify-content-center">
          <div class="spinner-border" role="status">

          </div>
        </div>
      </div>
    </div>
    <div v-else-if="this.record_id != null">

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
          <ul v-for="(index, item) in this.record.further_eval.impair_list" :key="item.record_id">
            <li>{{ item }}</li>
          </ul>
          <ul>
            <li>Open-ended response: {{ this.record.further_eval.open_ended_response }}</li>
            <li>Did youth or program staff recommend clinical interview?
              {{ this.record.further_eval.recommend_clinical_interview }}
            </li>
          </ul>
        </div>
      </div>
      <hr>
      <div class="row m-t-3">
        <div v-if="this.record.clinical_significant.length !== 0">
          <div class="col-xs-10">
            <h2>Clinically Significant Information</h2>
          </div>
          <div class="print-expand col-xs-4">
            <table id="clinical_significant" class="table table-striped table-condensed" cellspacing="0" width="95%">
              <thead v-if="this.record.clinical_significant.header !== undefined">
              <tr v-for="(index, item) in this.record.clinical_significant.header" :key="item.record_id">
                <th>{{ item }}</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(index, item) in this.record.clinical_significant.rows" :key="item.record_id">
                <th>{{ item }}</th>
              </tr>
              </tbody>
            </table>
          </div>
          <div class="print-expand col-xs-8">
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
                  <td v-for="col in row" :key="col">{{ col }}</td>
                </div>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
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
              <td v-for="col in row" :key="col">{{ col }}</td>
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
    <div v-else>
        <p>No record is selected.</p>
    </div>
  </div>
</template>
<script>
export default {
  name: "SummarySection",
  methods: {
    getRecord: function () {
      this.showLoader = true;
      var obj = this
      window.module.ajax('get_record', {'record_id': this.record_id}).then(function (response) {
        console.log(response)
        obj.record = response
        obj.showLoader = false;
      }).catch(function (err) {
        obj.showAlert = true
        obj.alertMessage = err
        console.log(err)
      });
    }
  },
  data() {
    return {
      record: {},
      showLoader: false,
      record_id: null,
      showAlert: '',
      alertMessage: '',
      alertVariant: '',
    }
  },
  mounted() {
    this.$root.$on('update_record_id', record_id => {
      this.record_id = record_id
      this.getRecord()
    });

    let urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('record_id')) {
      this.record_id = parseInt(urlParams.get('record_id'));
      this.getRecord()
    }

  }
}
</script>
<style scoped>

</style>
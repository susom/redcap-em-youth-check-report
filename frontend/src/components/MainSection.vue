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
      <SummarySection :record="this.record"></SummarySection>
      <hr>
      <ClinicalSignificantSection :record="this.record"></ClinicalSignificantSection>
      <hr>
      <ScalesSection :record="this.record"></ScalesSection>
      <hr>
      <DPSSymptomScaleSection :record="this.record"></DPSSymptomScaleSection>
    </div>
    <div v-else>
      <p>No record is selected.</p>
    </div>
  </div>
</template>
<script>
import SummarySection from "@/components/SummarySection.vue";
import ClinicalSignificantSection from "@/components/ClinicalSignificantSection.vue";
import DPSSymptomScaleSection from "@/components/DPSSymptomScaleSection.vue";
import ScalesSection from "@/components/ScalesSection.vue";

export default {
  name: "MainSection",
  components: {ScalesSection, DPSSymptomScaleSection, ClinicalSignificantSection, SummarySection},
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
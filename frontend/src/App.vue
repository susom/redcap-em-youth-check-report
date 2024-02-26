<template>
  <div id="app" class="container">
    <div class="row hidden-print">
      <HeaderSection/>
      <SearchSection/>
      <SummarySection/>
    </div>

  </div>
</template>

<script>
import HeaderSection from "./components/HeaderSection.vue";
import SearchSection from "./components/SearchSection.vue";
import SummarySection from "./components/SummarySection.vue";

export default {
  name: 'App',
  components: {
    SummarySection,
    SearchSection,
    HeaderSection
  }
}
</script>

<style>
</style>
